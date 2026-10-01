<?php

namespace Database\Factories;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => LocationType::City,
            'name' => fake()->unique()->city(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'is_active' => true,
        ];
    }

    public function country(): static
    {
        return $this->state(fn (): array => [
            'type' => LocationType::Country,
            'name' => fake()->unique()->country(),
            'country_code' => strtoupper(fake()->countryCode()),
        ]);
    }

    public function city(): static
    {
        return $this->state(fn (): array => [
            'type' => LocationType::City,
            'name' => fake()->unique()->city(),
        ]);
    }

    public function district(): static
    {
        return $this->state(fn (): array => [
            'type' => LocationType::District,
            'name' => fake()->unique()->citySuffix().' '.fake()->unique()->word(),
        ]);
    }

    public function type(LocationType $type): static
    {
        return $this->state(fn (): array => ['type' => $type]);
    }

    public function childOf(Location $parent): static
    {
        return $this->state(fn (): array => [
            'parent_id' => $parent->id,
            'country_code' => $parent->country_code,
        ]);
    }
}
