<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Geo;

use kristijorgji\Geo\GeoLocationDetailsBuilder;
use kristijorgji\Tests\Geo\Factories\GeoLocationDetailsFactory;
use PHPUnit\Framework\TestCase;

final class GeoLocationDetailsBuilderTest extends TestCase
{
    public function test_can_overwrite_name_flag_and_emoji(): void
    {
        $built = new GeoLocationDetailsBuilder(GeoLocationDetailsFactory::make(['countryName' => null]))
            ->setCountryName('Shqipëri')
            ->setCountryFlag('https://flagcdn.com/al.svg')
            ->setCountryFlagEmoji('🇦🇱')
            ->build();

        $this->assertSame('Shqipëri', $built->getCountryName());
        $this->assertSame('https://flagcdn.com/al.svg', $built->getCountryFlag());
        $this->assertSame('🇦🇱', $built->getCountryFlagEmoji());
    }
}
