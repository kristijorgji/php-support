<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Geo\Factories;

use kristijorgji\Geo\GeoLocationDetails;
use kristijorgji\Iso\Countries;

final class GeoLocationDetailsFactory
{
    /**
     * @param array<string, mixed> $custom
     */
    public static function make(array $custom = []): GeoLocationDetails
    {
        return new GeoLocationDetails(
            countryCode: $custom['countryCode'] ?? Countries::ALBANIA,
            countryName: $custom['countryName'] ?? 'Albania',
            city: $custom['city'] ?? 'Tirana',
            countryFlag: $custom['countryFlag'] ?? null,
            countryFlagEmoji: $custom['countryFlagEmoji'] ?? '🇦🇱',
            rawResponse: $custom['rawResponse'] ?? null,
        );
    }
}
