<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Geo;

use kristijorgji\Geo\ChainGeoLocationService;
use kristijorgji\Geo\Exceptions\BogonCannotBeResolvedException;
use kristijorgji\Geo\Exceptions\GeoLocationResolverException;
use kristijorgji\Geo\Exceptions\GeoLocationServiceException;
use kristijorgji\Geo\Exceptions\PrivateIpCannotBeResolvedException;
use kristijorgji\Geo\GeoLocationDetails;
use kristijorgji\Geo\Resolvers\GeoLocationResolverInterface;
use kristijorgji\Iso\Countries;
use kristijorgji\Iso\CountryInfo;
use kristijorgji\Iso\CountryInfoRepositoryInterface;
use kristijorgji\Support\LocaleProviderInterface;
use kristijorgji\Tests\Geo\Factories\GeoLocationDetailsFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ChainGeoLocationServiceTest extends TestCase
{
    public function test_bogon_throws_before_any_resolver_is_invoked(): void
    {
        $resolver = $this->createMock(GeoLocationResolverInterface::class);
        $resolver->expects($this->never())->method('detailsFromIp');

        $service = new ChainGeoLocationService([$resolver], new NullLogger());

        $this->expectException(BogonCannotBeResolvedException::class);
        $service->detailsFromIp('100.64.3.9');
    }

    public function test_private_ip_throws_private_subclass(): void
    {
        $resolver = $this->createMock(GeoLocationResolverInterface::class);
        $resolver->expects($this->never())->method('detailsFromIp');

        $service = new ChainGeoLocationService([$resolver], new NullLogger());

        $this->expectException(PrivateIpCannotBeResolvedException::class);
        $service->detailsFromIp('10.0.0.1');
    }

    public function test_resolver_thrown_bogon_aborts_the_chain(): void
    {
        $first = $this->createMock(GeoLocationResolverInterface::class);
        $first->method('name')->willReturn('ipInfo');
        $first->method('detailsFromIp')->willThrowException(
            new BogonCannotBeResolvedException('bogon from provider'),
        );

        $second = $this->createMock(GeoLocationResolverInterface::class);
        $second->expects($this->never())->method('detailsFromIp');

        $service = new ChainGeoLocationService([$first, $second], new NullLogger());

        $this->expectException(BogonCannotBeResolvedException::class);
        $service->detailsFromIp('8.8.8.8');
    }

    public function test_falls_through_resolver_exceptions_then_succeeds(): void
    {
        $first = $this->createMock(GeoLocationResolverInterface::class);
        $first->method('name')->willReturn('ipInfo');
        $first->method('detailsFromIp')->willThrowException(
            new GeoLocationResolverException('timeout'),
        );

        $expected = GeoLocationDetailsFactory::make();
        $second = $this->createMock(GeoLocationResolverInterface::class);
        $second->method('name')->willReturn('ipStack');
        $second->method('detailsFromIp')->willReturn($expected);

        $service = new ChainGeoLocationService([$first, $second], new NullLogger());
        $this->assertSame($expected, $service->detailsFromIp('8.8.8.8'));
    }

    public function test_all_resolvers_failing_throws_service_exception(): void
    {
        $resolver = $this->createMock(GeoLocationResolverInterface::class);
        $resolver->method('name')->willReturn('ipInfo');
        $resolver->method('detailsFromIp')->willThrowException(
            new GeoLocationResolverException('fail'),
        );

        $service = new ChainGeoLocationService([$resolver], new NullLogger());

        $this->expectException(GeoLocationServiceException::class);
        $service->detailsFromIp('8.8.8.8');
    }

    public function test_enriches_missing_country_name(): void
    {
        $resolver = $this->createMock(GeoLocationResolverInterface::class);
        $resolver->method('detailsFromIp')->willReturn(new GeoLocationDetails(
            Countries::ALBANIA,
            null,
            'Tirana',
            null,
            null,
            null,
        ));

        $repo = $this->createMock(CountryInfoRepositoryInterface::class);
        $repo->method('find')->willReturn(new CountryInfo(Countries::ALBANIA, 'Albania', '🇦🇱'));

        $locale = $this->createMock(LocaleProviderInterface::class);
        $locale->method('locale')->willReturn('en');

        $service = new ChainGeoLocationService(
            [$resolver],
            new NullLogger(),
            $repo,
            $locale,
        );

        $result = $service->detailsFromIp('8.8.8.8');
        $this->assertSame('Albania', $result->getCountryName());
        $this->assertSame('🇦🇱', $result->getCountryFlagEmoji());
        $this->assertSame('Tirana', $result->getCity());
    }
}
