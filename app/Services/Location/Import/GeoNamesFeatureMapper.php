<?php

namespace App\Services\Location\Import;

use App\Enums\LocationType;

/**
 * Maps GeoNames feature class + feature code pairs onto the location model.
 *
 * Anything outside the administrative tree (POIs, natural features, ...) is
 * intentionally unsupported and maps to null.
 */
final class GeoNamesFeatureMapper
{
    public static function map(string $featureClass, string $featureCode): ?LocationType
    {
        $class = strtoupper($featureClass);
        $code = strtoupper($featureCode);

        return match (true) {
            $class === 'P' && $code === 'PCLI' => LocationType::Country,
            $class === 'A' && $code === 'ADM1' => LocationType::Region,
            $class === 'A' && $code === 'ADM2' => LocationType::District,
            $class === 'P' && str_starts_with($code, 'PPL') => LocationType::City,
            default => null,
        };
    }
}
