<?php

namespace App\Services\Location\Import;

/**
 * Immutable value object for one record in a GeoNames `allCountries.txt` file.
 *
 * The source is tab-separated with 19 columns:
 * 0 geonameid, 1 name, 2 asciiname, 3 alternatenames, 4 latitude, 5 longitude,
 * 6 feature class, 7 feature code, 8 country code, 9 cc2, 10 admin1, 11 admin2,
 * 12 admin3, 13 admin4, 14 population, 15 elevation, 16 dem, 17 timezone,
 * 18 modification date.
 */
final readonly class GeoNamesRow
{
    private const COLUMN_COUNT = 19;

    public function __construct(
        public string $geonameId,
        public string $name,
        public string $asciiName,
        public string $alternateNames,
        public ?float $latitude,
        public ?float $longitude,
        public string $featureClass,
        public string $featureCode,
        public string $countryCode,
        public string $admin1,
        public string $admin2,
        public int $population,
        public string $timezone,
    ) {}

    /**
     * Parse a raw line, returning null for blank or malformed records.
     */
    public static function fromLine(string $line): ?self
    {
        $columns = str_getcsv(rtrim($line, "\r\n"), "\t", '"', '');

        if (count($columns) < self::COLUMN_COUNT) {
            return null;
        }

        $name = $columns[1] !== '' ? $columns[1] : $columns[2];

        if ($name === '') {
            return null;
        }

        return new self(
            geonameId: $columns[0],
            name: $name,
            asciiName: $columns[2],
            alternateNames: $columns[3],
            latitude: self::toFloat($columns[4]),
            longitude: self::toFloat($columns[5]),
            featureClass: strtoupper($columns[6]),
            featureCode: strtoupper($columns[7]),
            countryCode: strtoupper($columns[8]),
            admin1: $columns[10],
            admin2: $columns[11],
            population: self::toInt($columns[14]),
            timezone: $columns[17],
        );
    }

    private static function toFloat(string $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private static function toInt(string $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
