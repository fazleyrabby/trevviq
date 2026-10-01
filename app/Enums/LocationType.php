<?php

namespace App\Enums;

enum LocationType: string
{
    case Country = 'country';
    case Region = 'region';
    case City = 'city';
    case District = 'district';
    case Neighborhood = 'neighborhood';
    case Attraction = 'attraction';
    case Hotel = 'hotel';
    case Restaurant = 'restaurant';
    case Cafe = 'cafe';
    case Museum = 'museum';
    case Beach = 'beach';
    case Park = 'park';
    case Landmark = 'landmark';
    case Venue = 'venue';
    case Activity = 'activity';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Country => 'Country',
            self::Region => 'Region',
            self::City => 'City',
            self::District => 'District',
            self::Neighborhood => 'Neighborhood',
            self::Attraction => 'Attraction',
            self::Hotel => 'Hotel',
            self::Restaurant => 'Restaurant',
            self::Cafe => 'Cafe',
            self::Museum => 'Museum',
            self::Beach => 'Beach',
            self::Park => 'Park',
            self::Landmark => 'Landmark',
            self::Venue => 'Venue',
            self::Activity => 'Activity',
            self::Other => 'Place',
        };
    }

    /**
     * Administrative levels make up the navigable hierarchy.
     *
     * @return array<int, self>
     */
    public static function administrative(): array
    {
        return [self::Country, self::Region, self::City, self::District, self::Neighborhood];
    }

    /**
     * Point-of-interest types that hang off the administrative tree.
     *
     * @return array<int, self>
     */
    public static function places(): array
    {
        return [
            self::Attraction, self::Hotel, self::Restaurant, self::Cafe,
            self::Museum, self::Beach, self::Park, self::Landmark,
            self::Venue, self::Activity, self::Other,
        ];
    }

    public function isAdministrative(): bool
    {
        return in_array($this, self::administrative(), true);
    }

    public function isPlace(): bool
    {
        return in_array($this, self::places(), true);
    }

    /**
     * Relative weight used to rank search results; countries and cities first.
     */
    public function searchWeight(): int
    {
        return match ($this) {
            self::Country => 90,
            self::Region => 70,
            self::City => 80,
            self::District => 60,
            self::Neighborhood => 50,
            default => 40,
        };
    }
}
