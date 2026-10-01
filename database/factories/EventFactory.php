<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('now', '+3 months');

        return [
            'location_id' => Location::factory(),
            'organizer_id' => User::factory(),
            'name' => fake()->unique()->sentence(3),
            'description' => fake()->paragraph(),
            'starts_at' => $startsAt,
            'ends_at' => null,
            'address' => fake()->streetAddress(),
            'website_url' => null,
            'status' => EventStatus::Published,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => EventStatus::Pending]);
    }

    public function past(): static
    {
        return $this->state(fn (): array => [
            'starts_at' => fake()->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }
}
