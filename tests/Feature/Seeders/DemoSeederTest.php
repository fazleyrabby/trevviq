<?php

use App\Models\Location;
use App\Models\Review;
use App\Models\User;
use App\Models\Video;
use Database\Seeders\DemoSeeder;

it('seeds every module and is idempotent when re-run', function () {
    $this->seed(DemoSeeder::class);

    $counts = [
        'users' => User::count(),
        'locations' => Location::count(),
        'reviews' => Review::count(),
        'videos' => Video::count(),
    ];

    $this->seed(DemoSeeder::class);

    expect(User::count())->toBe($counts['users'])
        ->and(Location::count())->toBe($counts['locations'])
        ->and(Review::count())->toBe($counts['reviews'])
        ->and(Video::count())->toBe($counts['videos']);
});
