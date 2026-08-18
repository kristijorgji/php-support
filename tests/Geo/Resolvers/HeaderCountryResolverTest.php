<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Geo\Resolvers;

use kristijorgji\Geo\Contracts\RequestContextInterface;
use kristijorgji\Geo\Exceptions\GeoLocationResolverException;
use kristijorgji\Geo\Resolvers\HeaderCountryResolver;
use kristijorgji\Iso\Countries;
use PHPUnit\Framework\TestCase;

class HeaderCountryResolverTest extends TestCase
{
    public function test_resolves_when_trusted_and_ip_matches(): void
    {
        $context = $this->createMock(RequestContextInterface::class);
        $context->method('clientIp')->willReturn('8.8.8.8');
        $context->method('countryHeader')->willReturn('al');

        $details = (new HeaderCountryResolver($context, true))->detailsFromIp('8.8.8.8');
        $this->assertSame(Countries::ALBANIA, $details->getCountryCode());
        $this->assertNull($details->getCountryName());
    }

    public function test_refuses_when_untrusted(): void
    {
        $context = $this->createMock(RequestContextInterface::class);
        $this->expectException(GeoLocationResolverException::class);
        (new HeaderCountryResolver($context, false))->detailsFromIp('8.8.8.8');
    }

    public function test_refuses_when_ip_does_not_match_current_client(): void
    {
        $context = $this->createMock(RequestContextInterface::class);
        $context->method('clientIp')->willReturn('8.8.8.8');
        $context->method('countryHeader')->willReturn('AL');

        $this->expectException(GeoLocationResolverException::class);
        (new HeaderCountryResolver($context, true))->detailsFromIp('1.2.3.4');
    }

    public function test_refuses_empty_or_unknown_header(): void
    {
        $context = $this->createMock(RequestContextInterface::class);
        $context->method('clientIp')->willReturn('8.8.8.8');
        $context->method('countryHeader')->willReturn('EU');

        $this->expectException(GeoLocationResolverException::class);
        (new HeaderCountryResolver($context, true))->detailsFromIp('8.8.8.8');
    }
}
