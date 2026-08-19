<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Geo\Resolvers;

use GuzzleHttp\ClientInterface;
use kristijorgji\Geo\Contracts\RequestContextInterface;
use kristijorgji\Geo\Exceptions\GeoConfigurationException;
use kristijorgji\Geo\Resolvers\HeaderCountryResolver;
use kristijorgji\Geo\Resolvers\IpInfoResolver;
use kristijorgji\Geo\Resolvers\IpStackResolver;
use kristijorgji\Geo\Resolvers\MaxMindDbResolver;
use kristijorgji\Geo\Resolvers\ResolverFactory;
use PHPUnit\Framework\TestCase;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

final class ResolverFactoryTest extends TestCase
{
    public function test_make_chain_throws_on_empty_list(): void
    {
        $factory = new ResolverFactory;

        $this->expectException(GeoConfigurationException::class);
        $this->expectExceptionMessage('Geo resolver list is empty');
        $factory->makeChain([], []);
    }

    public function test_unknown_resolver_name_throws(): void
    {
        $factory = new ResolverFactory;

        $this->expectException(GeoConfigurationException::class);
        $this->expectExceptionMessage('Unknown geo resolver "typo"');
        $factory->make('typo', []);
    }

    public function test_header_requires_request_context(): void
    {
        $factory = new ResolverFactory;

        $this->expectException(GeoConfigurationException::class);
        $this->expectExceptionMessage('request context');
        $factory->make('header', ['trustCountryHeader' => true]);
    }

    public function test_header_requires_trust_flag(): void
    {
        $factory = new ResolverFactory(null, $this->createStub(RequestContextInterface::class));

        $this->expectException(GeoConfigurationException::class);
        $this->expectExceptionMessage('trustCountryHeader');
        $factory->make('header', []);
    }

    public function test_header_accepts_string_bool(): void
    {
        $factory = new ResolverFactory(null, $this->createStub(RequestContextInterface::class));

        $resolver = $factory->make('header', ['trustCountryHeader' => 'true']);
        $this->assertInstanceOf(HeaderCountryResolver::class, $resolver);
        $this->assertSame('header', $resolver->name());
    }

    public function test_maxmind_requires_existing_file(): void
    {
        $factory = new ResolverFactory;

        $this->expectException(GeoConfigurationException::class);
        $this->expectExceptionMessage('maxMind.databasePath');
        $factory->make('maxmind', ['maxMind' => ['databasePath' => '/tmp/does-not-exist.mmdb']]);
    }

    public function test_maxmind_with_existing_file(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'mmdb');
        $this->assertIsString($path);
        $factory = new ResolverFactory;

        $resolver = $factory->make('maxmind', ['maxMind' => ['databasePath' => $path]]);
        $this->assertInstanceOf(MaxMindDbResolver::class, $resolver);
        unlink($path);
    }

    public function test_ip_info_requires_token_and_http(): void
    {
        $factory = new ResolverFactory;

        $this->expectException(GeoConfigurationException::class);
        $this->expectExceptionMessage('Guzzle');
        $factory->make('ipInfo', ['ipInfo' => ['token' => 'abc']]);
    }

    public function test_ip_info_requires_token(): void
    {
        $factory = new ResolverFactory($this->createStub(ClientInterface::class));

        $this->expectException(GeoConfigurationException::class);
        $this->expectExceptionMessage('ipInfo.token');
        $factory->make('ipInfo', ['ipInfo' => ['token' => '']]);
    }

    public function test_ip_stack_requires_access_key(): void
    {
        $factory = new ResolverFactory($this->createStub(ClientInterface::class));

        $this->expectException(GeoConfigurationException::class);
        $this->expectExceptionMessage('ipStack.accessKey');
        $factory->make('ipStack', []);
    }

    public function test_make_chain_preserves_order(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'mmdb');
        $this->assertIsString($path);

        $factory = new ResolverFactory(
            $this->createStub(ClientInterface::class),
            $this->createStub(RequestContextInterface::class),
        );

        $resolvers = $factory->makeChain(
            ['header', 'maxmind', 'ipInfo', 'ipStack'],
            [
                'trustCountryHeader' => false,
                'maxMind' => ['databasePath' => $path],
                'ipInfo' => ['token' => 'info-token'],
                'ipStack' => ['accessKey' => 'stack-key'],
            ],
        );

        $this->assertCount(4, $resolvers);
        $this->assertInstanceOf(HeaderCountryResolver::class, $resolvers[0]);
        $this->assertInstanceOf(MaxMindDbResolver::class, $resolvers[1]);
        $this->assertInstanceOf(IpInfoResolver::class, $resolvers[2]);
        $this->assertInstanceOf(IpStackResolver::class, $resolvers[3]);
        unlink($path);
    }
}
