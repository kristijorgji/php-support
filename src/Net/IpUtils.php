<?php declare(strict_types = 1);

namespace kristijorgji\Net;

use function array_any;
use function count;
use function explode;
use function filter_var;
use function inet_pton;
use function intdiv;
use function is_array;
use function str_contains;
use function strlen;
use function substr;
use function unpack;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_IP;

final class IpUtils
{
    private const array PRIVATE_V4 = [
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
    ];
    private const array PRIVATE_V6 = [
        'fc00::/7',
    ];
    private const array BOGON_V4 = [
        '0.0.0.0/8',
        '10.0.0.0/8',
        '100.64.0.0/10',
        '127.0.0.0/8',
        '169.254.0.0/16',
        '172.16.0.0/12',
        '192.0.0.0/24',
        '192.0.2.0/24',
        '192.88.99.0/24',
        '192.168.0.0/16',
        '198.18.0.0/15',
        '198.51.100.0/24',
        '203.0.113.0/24',
        '224.0.0.0/4',
        '240.0.0.0/4',
        '255.255.255.255/32',
    ];
    private const array BOGON_V6 = [
        '::/128',
        '::1/128',
        '::ffff:0:0/96',
        '100::/64',
        '2001:10::/28',
        '2001:db8::/32',
        'fc00::/7',
        'fe80::/10',
        'ff00::/8',
    ];

    /**
     * If your server is behind a proxy, additional checks need to be done
     * before fetching the correct client IP.
     *
     * Ported verbatim from the apps so this extraction stays behavior-neutral.
     * The last X-Forwarded-For hop and the `', '` split are known defects;
     * do not "fix" them here.
     *
     * @param array<string, string|array<string>|null> $server
     */
    public static function getIp(array $server): string
    {
        if (isset($server['HTTP_X_FORWARDED_FOR'])) {
            $proxy = $server['HTTP_X_FORWARDED_FOR'];
            if (is_array($proxy)) {
                $proxy = $proxy[0] ?? '';
            }
            if (str_contains($proxy, ', ')) {
                $proxies = explode(', ', $proxy);
                return $proxies[count($proxies) - 1];
            }

            return $proxy;
        }

        $remote = $server['REMOTE_ADDR'] ?? '';
        return is_array($remote) ? ($remote[0] ?? '') : $remote;
    }

    public static function isPrivateIp(string $ip): bool
    {
        if (self::isIpV4($ip)) {
            return self::matchesAnyCidr($ip, self::PRIVATE_V4);
        }

        if (self::isIpV6($ip)) {
            return self::matchesAnyCidr($ip, self::PRIVATE_V6);
        }

        return false;
    }

    public static function isBogon(string $ip): bool
    {
        if (self::isIpV4($ip)) {
            return self::matchesAnyCidr($ip, self::BOGON_V4);
        }

        if (self::isIpV6($ip)) {
            return self::matchesAnyCidr($ip, self::BOGON_V6);
        }

        return false;
    }

    public static function isIpV4(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    public static function isIpV6(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * @param list<string> $cidrs
     */
    private static function matchesAnyCidr(string $ip, array $cidrs): bool
    {
        return array_any($cidrs, fn($cidr) => self::inCidr($ip, $cidr));
    }

    private static function inCidr(string $ip, string $cidr): bool
    {
        [$subnet, $prefix] = explode('/', $cidr, 2);
        $ipBin = inet_pton($ip);
        $subnetBin = inet_pton($subnet);
        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $prefixLength = (int) $prefix;
        $bytes = intdiv($prefixLength, 8);
        $bits = $prefixLength % 8;

        if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
            return false;
        }

        if ($bits === 0) {
            return true;
        }

        $mask = (~((1 << 8 - $bits) - 1)) & 0xFF;
        $ipByte = unpack('C', $ipBin[$bytes])[1];
        $subnetByte = unpack('C', $subnetBin[$bytes])[1];

        return ($ipByte & $mask) === ($subnetByte & $mask);
    }
}
