<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Geo\Resolvers;

use kristijorgji\Geo\Exceptions\GeoLocationResolverException;
use kristijorgji\Geo\Resolvers\MaxMindDbResolver;
use PHPUnit\Framework\TestCase;

final class MaxMindDbResolverTest extends TestCase
{
    public function test_missing_database_throws_resolver_exception(): void
    {
        $resolver = new MaxMindDbResolver('/tmp/does-not-exist-GeoLite2-City.mmdb');
        $this->assertSame('maxmind', $resolver->name());

        $this->expectException(GeoLocationResolverException::class);
        $resolver->detailsFromIp('8.8.8.8');
    }
}
