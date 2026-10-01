<?php

namespace Database\Factories;

use App\Enums\VideoStatus;
use App\Enums\VideoVisibility;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    protected $model = Video::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'location_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'duration' => fake()->numberBetween(5, 60),
            'width' => 1080,
            'height' => 1920,
            'file_size' => fake()->numberBetween(1_000_000, 40_000_000),
            'mime_type' => 'video/mp4',
            'status' => VideoStatus::Published,
            'visibility' => VideoVisibility::Public,
            'published_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => VideoStatus::Pending,
            'published_at' => null,
        ]);
    }

    public function unlisted(): static
    {
        return $this->state(fn (): array => ['visibility' => VideoVisibility::Unlisted]);
    }
}
