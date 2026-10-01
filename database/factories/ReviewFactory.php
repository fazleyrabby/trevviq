<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Location;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'location_id' => Location::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'visit_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'status' => ReviewStatus::Approved,
            'helpful_count' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => ReviewStatus::Pending]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => ['status' => ReviewStatus::Rejected]);
    }
}
