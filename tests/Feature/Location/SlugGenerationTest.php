<?php

use App\Models\Location;

it('generates a kebab-case slug and a matching full slug for a root location', function () {
    $location = Location::factory()->create(['name' => 'New York City']);

    expect($location->slug)->toBe('new-york-city')
        ->and($location->full_slug)->toBe('new-york-city');
});

it('prefixes a child full slug with the parent full slug', function () {
    $parent = Location::factory()->country()->create(['name' => 'France']);
    $child = Location::factory()->city()->childOf($parent)->create(['name' => 'Paris']);

    expect($child->full_slug)->toBe($parent->full_slug.'/'.$child->slug)
        ->and($child->full_slug)->toBe('france/paris');
});

it('suffixes duplicate root names to keep full slugs distinct', function () {
    $first = Location::factory()->create(['name' => 'Springfield']);
    $second = Location::factory()->create(['name' => 'Springfield']);

    expect($second->full_slug)->not->toBe($first->full_slug)
        ->and($second->full_slug)->toBe('springfield-2');
});

it('avoids reserved first segments for root locations', function () {
    $location = Location::factory()->create(['name' => 'Search']);

    expect($location->slug)->toBe('search-place')
        ->and($location->full_slug)->not->toBe('search')
        ->and($location->full_slug)->toBe('search-place');
});

it('keeps the full slug immutable when the name changes', function () {
    $location = Location::factory()->create(['name' => 'Old Name']);
    $originalFullSlug = $location->full_slug;

    $location->update(['name' => 'Renamed Place']);

    expect($location->fresh()->full_slug)->toBe($originalFullSlug)
        ->and($location->fresh()->full_slug)->toBe('old-name');
});
