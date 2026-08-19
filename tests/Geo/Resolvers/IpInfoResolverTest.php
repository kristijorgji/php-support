<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Geo\Resolvers;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use kristijorgji\Geo\Exceptions\BogonCannotBeResolvedException;
use kristijorgji\Geo\Exceptions\GeoLocationResolverException;
use kristijorgji\Geo\Resolvers\IpInfoResolver;
use kristijorgji\Iso\Countries;
use PHPUnit\Framework\TestCase;

final class IpInfoResolverTest extends TestCase
{
    public function test_maps_successful_response(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('request')->willReturn(
            new Response(200, [], '{"ip":"8.8.8.8","country":"AL","city":"Tirana"}'),
        );

        $details = new IpInfoResolver($client, 'token')->detailsFromIp('8.8.8.8');
        $this->assertSame(Countries::ALBANIA, $details->getCountryCode());
        $this->assertSame('Tirana', $details->getCity());
        $this->assertNull($details->getCountryName());
    }

    public function test_bogon_flag_throws_service_level_exception(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('request')->willReturn(new Response(200, [], '{"ip":"127.0.0.1","bogon":true}'));

        $this->expectException(BogonCannotBeResolvedException::class);
        new IpInfoResolver($client, 'token')->detailsFromIp('127.0.0.1');
    }

    public function test_pseudo_country_code_is_resolver_exception_not_value_error(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('request')->willReturn(new Response(200, [], '{"ip":"1.2.3.4","country":"EU"}'));

        $this->expectException(GeoLocationResolverException::class);
        new IpInfoResolver($client, 'token')->detailsFromIp('1.2.3.4');
    }
}
