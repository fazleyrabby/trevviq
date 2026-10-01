<?php

use App\Models\User;
use App\Models\Video;

it('shows the public video feed', function () {
    Video::factory()->create(['title' => 'Roam public story']);

    $this->get(route('videos.index'))
        ->assertOk()
        ->assertSee('Roam public story');
});

it('excludes pending and unlisted videos from the feed', function () {
    Video::factory()->create(['title' => 'Visible journey']);
    Video::factory()->pending()->create(['title' => 'Pending journey']);
    Video::factory()->unlisted()->create(['title' => 'Unlisted journey']);

    $this->get(route('videos.index'))
        ->assertOk()
        ->assertSee('Visible journey')
        ->assertDontSee('Pending journey')
        ->assertDontSee('Unlisted journey');
});

it('shows a published video detail page', function () {
    $video = Video::factory()->create(['title' => 'Detail journey']);

    $this->get(route('videos.show', $video))
        ->assertOk()
        ->assertSee('Detail journey');
});

it('lets the owner view their pending video but hides it from others', function () {
    $owner = User::factory()->create();
    $video = Video::factory()->for($owner)->pending()->create(['title' => 'Owner pending']);

    $this->actingAs(User::factory()->create())
        ->get(route('videos.show', $video))
        ->assertNotFound();

    $this->actingAs($owner)
        ->get(route('videos.show', $video))
        ->assertOk()
        ->assertSee('Owner pending');
});

it('increments the view count of a published video once', function () {
    $video = Video::factory()->create();

    $this->get(route('videos.show', $video))->assertOk();

    expect($video->fresh()->view_count)->toBe(1);
});

it('lets a guest view a published video detail page', function () {
    $video = Video::factory()->create(['title' => 'Guest journey']);

    $this->get(route('videos.show', $video))
        ->assertOk()
        ->assertSee('Guest journey');
});
