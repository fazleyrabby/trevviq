<?php

use App\Enums\ReportStatus;
use App\Enums\VideoStatus;
use App\Models\Report;
use App\Models\User;
use App\Models\Video;

it('lets a user like and unlike a video', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $this->actingAs($user)->post(route('videos.like', $video))->assertRedirect();

    $this->assertDatabaseHas('video_likes', [
        'video_id' => $video->id,
        'user_id' => $user->id,
    ]);
    expect($video->fresh()->like_count)->toBe(1);

    $this->actingAs($user)->post(route('videos.like', $video))->assertRedirect();

    $this->assertDatabaseMissing('video_likes', [
        'video_id' => $video->id,
        'user_id' => $user->id,
    ]);
    expect($video->fresh()->like_count)->toBe(0);
});

it('lets a user save and unsave a video', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $this->actingAs($user)->post(route('videos.save', $video))->assertRedirect();

    $this->assertDatabaseHas('saved_videos', [
        'video_id' => $video->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)->post(route('videos.save', $video))->assertRedirect();

    $this->assertDatabaseMissing('saved_videos', [
        'video_id' => $video->id,
        'user_id' => $user->id,
    ]);
});

it('increments the share count when a video is shared', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $this->actingAs($user)->post(route('videos.share', $video))->assertRedirect();

    expect($video->fresh()->share_count)->toBe(1);
});

it('lets a user report a video', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $this->actingAs($user)->post(route('videos.report', $video), [
        'reason' => 'spam',
    ])->assertRedirect();

    $this->assertDatabaseHas('reports', [
        'reporter_id' => $user->id,
        'reportable_type' => Video::class,
        'reportable_id' => $video->id,
        'reason' => 'spam',
        'status' => ReportStatus::Open->value,
    ]);
});

it('does not create a second report while one is still open', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $this->actingAs($user)->post(route('videos.report', $video), [
        'reason' => 'spam',
    ])->assertRedirect();

    $this->actingAs($user)->post(route('videos.report', $video), [
        'reason' => 'offensive',
    ])->assertRedirect();

    expect(Report::query()->count())->toBe(1);
});

it('redirects guests to login for video interactions', function () {
    $video = Video::factory()->create();

    $this->post(route('videos.like', $video))->assertRedirect(route('login'));
    $this->post(route('videos.save', $video))->assertRedirect(route('login'));
    $this->post(route('videos.share', $video))->assertRedirect(route('login'));
    $this->post(route('videos.report', $video), ['reason' => 'spam'])->assertRedirect(route('login'));

    $this->assertDatabaseCount('video_likes', 0);
    $this->assertDatabaseCount('saved_videos', 0);
    $this->assertDatabaseCount('reports', 0);
});

it('lets the owner delete their video', function () {
    $owner = User::factory()->create();
    $video = Video::factory()->for($owner)->create(['title' => 'Deletable journey']);

    $this->actingAs($owner)
        ->delete(route('videos.destroy', $video))
        ->assertRedirect(route('videos.index'));

    expect($video->fresh()->status)->toBe(VideoStatus::Deleted);
    $this->assertDatabaseHas('videos', ['id' => $video->id]);

    $this->get(route('videos.index'))->assertDontSee('Deletable journey');
});

it('forbids a non-owner from deleting a video', function () {
    $video = Video::factory()->for(User::factory())->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('videos.destroy', $video))
        ->assertForbidden();

    expect($video->fresh()->status)->toBe(VideoStatus::Published);
});
