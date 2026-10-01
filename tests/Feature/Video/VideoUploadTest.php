<?php

use App\Enums\VideoStatus;
use App\Enums\VideoVisibility;
use App\Jobs\ProcessVideo;
use App\Models\Location;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config(['trevviq.video.disk' => 'public']);
});

it('redirects a guest to login for the upload form', function () {
    $this->get(route('videos.create'))->assertRedirect(route('login'));
});

it('redirects a guest to login when uploading a video', function () {
    $location = Location::factory()->create();

    $this->post(route('videos.store'), [
        'video' => UploadedFile::fake()->create('clip.mp4', 5000, 'video/mp4'),
        'location_id' => $location->id,
    ])->assertRedirect(route('login'));

    $this->assertDatabaseCount('videos', 0);
});

it('lets an authenticated user upload a video', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $location = Location::factory()->create();

    Queue::fake();

    $this->actingAs($user)->post(route('videos.store'), [
        'video' => UploadedFile::fake()->create('clip.mp4', 5000, 'video/mp4'),
        'location_id' => $location->id,
        'title' => 'Sunset over the ridge',
        'description' => 'A short clip from the trail.',
        'visibility' => VideoVisibility::Public->value,
    ])->assertRedirect(route('videos.show', Video::query()->sole()));

    $video = Video::query()->sole();

    $this->assertModelExists($video);

    expect($video->status)->toBe(VideoStatus::Pending)
        ->and($video->user_id)->toBe($user->id)
        ->and($video->location_id)->toBe($location->id)
        ->and($video->visibility)->toBe(VideoVisibility::Public);

    $this->assertDatabaseHas('videos', [
        'id' => $video->id,
        'user_id' => $user->id,
        'location_id' => $location->id,
        'status' => VideoStatus::Pending->value,
    ]);

    Queue::assertPushed(ProcessVideo::class);

    expect(Storage::disk('public')->exists($video->original_path))->toBeTrue();
});

it('requires a video file when uploading', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();

    $this->actingAs($user)->post(route('videos.store'), [
        'location_id' => $location->id,
    ])->assertSessionHasErrors('video');

    $this->assertDatabaseCount('videos', 0);
});

it('rejects a file that is not a video', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();

    $this->actingAs($user)->post(route('videos.store'), [
        'video' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        'location_id' => $location->id,
    ])->assertSessionHasErrors('video');

    $this->assertDatabaseCount('videos', 0);
});

it('requires a location when uploading', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('videos.store'), [
        'video' => UploadedFile::fake()->create('clip.mp4', 5000, 'video/mp4'),
    ])->assertSessionHasErrors('location_id');

    $this->assertDatabaseCount('videos', 0);
});

it('rejects a location that does not exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('videos.store'), [
        'video' => UploadedFile::fake()->create('clip.mp4', 5000, 'video/mp4'),
        'location_id' => 999999,
    ])->assertSessionHasErrors('location_id');

    $this->assertDatabaseCount('videos', 0);
});

it('defaults the visibility to public when omitted', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $location = Location::factory()->create();

    Queue::fake();

    $this->actingAs($user)->post(route('videos.store'), [
        'video' => UploadedFile::fake()->create('clip.mp4', 5000, 'video/mp4'),
        'location_id' => $location->id,
    ])->assertRedirect(route('videos.show', Video::query()->sole()));

    $video = Video::query()->sole();

    expect($video->visibility)->toBe(VideoVisibility::Public);

    $this->assertDatabaseHas('videos', [
        'id' => $video->id,
        'visibility' => VideoVisibility::Public->value,
    ]);
});

it('publishes an upload synchronously when the queue runs inline', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $location = Location::factory()->create();

    $this->actingAs($user)->post(route('videos.store'), [
        'video' => UploadedFile::fake()->create('clip.mp4', 5000, 'video/mp4'),
        'location_id' => $location->id,
    ])->assertRedirect(route('videos.show', Video::query()->sole()));

    $video = Video::query()->sole()->refresh();

    expect($video->status)->toBe(VideoStatus::Published)
        ->and($video->processed_path)->not->toBeNull()
        ->and($video->published_at)->not->toBeNull();
});
