<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Geo;

use kristijorgji\Geo\GeoLocationDetails;
use kristijorgji\Iso\Countries;
use kristijorgji\Tests\Geo\Factories\GeoLocationDetailsFactory;
use PHPUnit\Framework\TestCase;

class GeoLocationDetailsTest extends TestCase
{
    public function test_from_json_tolerates_missing_country_flag(): void
    {
        $details = GeoLocationDetails::fromJson('{"countryCode":"AL","countryName":"Albania","city":"Tirana","countryFlagEmoji":"🇦🇱"}');
        $this->assertSame(Countries::ALBANIA, $details->getCountryCode());
        $this->assertNull($details->getCountryFlag());
        $this->assertSame('Tirana', $details->getCity());
    }

    public function test_json_roundtrip_includes_country_flag(): void
    {
        $original = GeoLocationDetailsFactory::make(['countryFlag' => 'https://flagcdn.com/al.svg']);
        $decoded = GeoLocationDetails::fromJson(json_encode($original));
        $this->assertSame($original->getCountryFlag(), $decoded->getCountryFlag());
        $this->assertSame($original->getCountryCode(), $decoded->getCountryCode());
    }
}
