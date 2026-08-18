<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Net;

use kristijorgji\Net\IpUtils;
use PHPUnit\Framework\TestCase;

class IpUtilsTest extends TestCase
{
    /**
     * @dataProvider bogonExamples
     */
    public function test_is_bogon(string $ip, bool $expected): void
    {
        $this->assertSame($expected, IpUtils::isBogon($ip), $ip);
    }

    /**
     * @return list<array{0: string, 1: bool}>
     */
    public function bogonExamples(): array
    {
        return [
            ['8.8.8.8', false],
            ['1.1.1.1', false],
            ['100.63.255.255', false],
            ['100.64.0.0', true],
            ['100.64.3.9', true],
            ['100.127.255.255', true],
            ['100.128.0.0', false],
            ['10.0.0.1', true],
            ['127.0.0.1', true],
            ['169.254.1.1', true],
            ['172.16.0.1', true],
            ['192.168.1.1', true],
            ['192.0.2.1', true],
            ['198.51.100.1', true],
            ['203.0.113.1', true],
            ['255.255.255.255', true],
            ['fd12:3456::1', true],
            ['2001:db8::1', true],
            ['::1', true],
            ['2001:4860:4860::8888', false],
        ];
    }

    /**
     * @dataProvider privateExamples
     */
    public function test_is_private_ip(string $ip, bool $expected): void
    {
        $this->assertSame($expected, IpUtils::isPrivateIp($ip), $ip);
    }

    /**
     * @return list<array{0: string, 1: bool}>
     */
    public function privateExamples(): array
    {
        return [
            ['10.1.2.3', true],
            ['172.16.0.1', true],
            ['192.168.0.1', true],
            ['8.8.8.8', false],
            ['100.64.0.1', false],
            ['127.0.0.1', false],
            ['fd12:3456::1', true],
            ['fc00::1', true],
            ['fe80::1', false],
            ['2001:db8::1', false],
        ];
    }

    public function test_get_ip_uses_last_xff_hop_verbatim(): void
    {
        $this->assertSame('10.0.0.9', IpUtils::getIp([
            'HTTP_X_FORWARDED_FOR' => '1.2.3.4, 10.0.0.9',
            'REMOTE_ADDR' => '127.0.0.1',
        ]));
        $this->assertSame('1.2.3.4', IpUtils::getIp([
            'HTTP_X_FORWARDED_FOR' => '1.2.3.4',
            'REMOTE_ADDR' => '127.0.0.1',
        ]));
        $this->assertSame('9.9.9.9', IpUtils::getIp([
            'REMOTE_ADDR' => '9.9.9.9',
        ]));
    }
}
