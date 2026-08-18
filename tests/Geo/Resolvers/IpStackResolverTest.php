<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Geo\Resolvers;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use kristijorgji\Geo\Exceptions\GeoLocationResolverException;
use kristijorgji\Geo\Resolvers\IpStackResolver;
use kristijorgji\Iso\Countries;
use PHPUnit\Framework\TestCase;

class IpStackResolverTest extends TestCase
{
    public function test_maps_successful_response_including_flag(): void
    {
        $body = json_encode([
            'success' => true,
            'country_code' => 'AL',
            'country_name' => 'Albania',
            'city' => 'Tirana',
            'location' => [
                'country_flag' => 'https://flagcdn.com/al.svg',
                'country_flag_emoji' => '🇦🇱',
            ],
        ]);
        $client = $this->createMock(ClientInterface::class);
        $client->method('request')->willReturn(new Response(200, [], $body));

        $details = (new IpStackResolver($client, 'key'))->detailsFromIp('8.8.8.8');
        $this->assertSame(Countries::ALBANIA, $details->getCountryCode());
        $this->assertSame('Albania', $details->getCountryName());
        $this->assertSame('https://flagcdn.com/al.svg', $details->getCountryFlag());
        $this->assertSame('🇦🇱', $details->getCountryFlagEmoji());
    }

    public function test_failed_payload_throws(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('request')->willReturn(new Response(200, [], '{"success":false}'));

        $this->expectException(GeoLocationResolverException::class);
        (new IpStackResolver($client, 'key'))->detailsFromIp('8.8.8.8');
    }

    public function test_pseudo_country_code_is_resolver_exception(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('request')->willReturn(new Response(200, [], '{"country_code":"EU"}'));

        $this->expectException(GeoLocationResolverException::class);
        (new IpStackResolver($client, 'key'))->detailsFromIp('8.8.8.8');
    }
}
