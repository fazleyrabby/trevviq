<?php

use App\Enums\VideoStatus;
use App\Jobs\ProcessVideo;
use App\Models\Location;
use App\Models\User;
use App\Models\Video;
use App\Services\Location\LocationContentService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config(['roam.video.disk' => 'public']);
});

it('publishes a pending video once processing succeeds', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $video = Video::factory()->pending()->create([
        'user_id' => $user->id,
        'duration' => null,
    ]);

    ProcessVideo::dispatchSync($video->id);

    $video->refresh();

    expect($video->status)->toBe(VideoStatus::Published)
        ->and($video->duration)->toBe(30)
        ->and($video->thumbnail_path)->not->toBeNull()
        ->and($video->published_at)->not->toBeNull();
});

it('rejects a video longer than the maximum duration', function () {
    Storage::fake('public');
    config(['roam.video.max_duration' => 60]);

    $video = Video::factory()->pending()->create(['duration' => 90]);

    ProcessVideo::dispatchSync($video->id);

    $video->refresh();

    expect($video->status)->toBe(VideoStatus::Rejected)
        ->and($video->published_at)->toBeNull();
});

it('leaves a valid video pending when auto publish is disabled', function () {
    Storage::fake('public');
    config(['roam.video.auto_publish' => false]);

    $video = Video::factory()->pending()->create(['duration' => null]);

    ProcessVideo::dispatchSync($video->id);

    $video->refresh();

    expect($video->status)->toBe(VideoStatus::Pending)
        ->and($video->processed_path)->not->toBeNull()
        ->and($video->published_at)->toBeNull();
});

it('counts published videos toward the location content total', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $location = Location::factory()->create();

    $videos = Video::factory()->count(3)->pending()->create([
        'user_id' => $user->id,
        'location_id' => $location->id,
        'duration' => null,
    ]);

    $videos->each(fn (Video $video) => ProcessVideo::dispatchSync($video->id));

    app(LocationContentService::class)->recompute($location);

    $location->refresh();

    expect($location->content_count)->toBe(3)
        ->and($location->indexable)->toBeTrue();
});

it('leaves a deleted video untouched', function () {
    Storage::fake('public');

    $video = Video::factory()->create(['status' => VideoStatus::Deleted]);

    ProcessVideo::dispatchSync($video->id);

    $video->refresh();

    expect($video->status)->toBe(VideoStatus::Deleted)
        ->and($video->processed_path)->toBeNull();
});
