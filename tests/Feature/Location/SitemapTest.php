<?php

use App\Models\Location;

it('serves an XML sitemap containing indexable locations', function () {
    $location = Location::factory()->create(['name' => 'Indexable Isle', 'indexable' => true]);

    $this->get(route('sitemap.index'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('<urlset', false)
        ->assertSee(route('travel.show', $location->full_slug), false);
});

it('excludes non-indexable locations from the sitemap', function () {
    $indexable = Location::factory()->create(['name' => 'Visible Vista', 'indexable' => true]);
    $hidden = Location::factory()->create(['name' => 'Hidden Hollow', 'indexable' => false]);

    $this->get(route('sitemap.index'))
        ->assertOk()
        ->assertSee(route('travel.show', $indexable->full_slug), false)
        ->assertDontSee(route('travel.show', $hidden->full_slug), false);
});

it('splits a large sitemap into an index with paginated chunks', function () {
    config(['roam.sitemap.chunk_size' => 1]);

    Location::factory()->count(2)->create(['indexable' => true]);

    $this->get(route('sitemap.index'))
        ->assertOk()
        ->assertSee('<sitemapindex', false)
        ->assertSee(route('sitemap.page', ['page' => 1]), false);

    $this->get(route('sitemap.page', ['page' => 1]))
        ->assertOk()
        ->assertSee('<urlset', false);

    $this->get(route('sitemap.page', ['page' => 3]))
        ->assertNotFound();
});

it('serves a robots file that disallows admin and points at the sitemap', function () {
    $this->get(route('robots'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /admin', false)
        ->assertSee(route('sitemap.index'), false);
});
