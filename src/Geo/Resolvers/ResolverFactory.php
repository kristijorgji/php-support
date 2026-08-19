<?php declare(strict_types = 1);

namespace kristijorgji\Geo\Resolvers;

use GuzzleHttp\ClientInterface;
use kristijorgji\Geo\Contracts\RequestContextInterface;
use kristijorgji\Geo\Exceptions\GeoConfigurationException;
use function array_key_exists;
use function explode;
use function filter_var;
use function implode;
use function is_array;
use function is_bool;
use function is_file;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;
use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;

/**
 * @phpstan-type GeoConfigValue bool|float|int|string|array<string, bool|float|int|string|null>|null
 * @phpstan-type GeoConfig array<string, GeoConfigValue>
 */
final readonly class ResolverFactory
{
    private const array KNOWN = ['header', 'maxmind', 'ipInfo', 'ipStack'];

    public function __construct(
        private ?ClientInterface $http = null,
        private ?RequestContextInterface $requestContext = null,
    ) {
    }

    /**
     * @param GeoConfig $config
     */
    public function make(string $name, array $config): GeoLocationResolverInterface
    {
        return match ($name) {
            'header' => new HeaderCountryResolver(
                $this->requestContext ?? throw new GeoConfigurationException(
                    'Resolver "header" requires a request context',
                ),
                self::requireBool($config, 'trustCountryHeader'),
            ),
            'maxmind' => new MaxMindDbResolver(self::requireExistingFile($config, 'maxMind.databasePath')),
            'ipInfo' => new IpInfoResolver($this->requireHttp(), self::requireString($config, 'ipInfo.token')),
            'ipStack' => new IpStackResolver($this->requireHttp(), self::requireString($config, 'ipStack.accessKey')),
            default => throw new GeoConfigurationException(
                sprintf('Unknown geo resolver "%s"; known: %s', $name, implode(', ', self::KNOWN)),
            ),
        };
    }

    /**
     * @param list<string> $names
     * @param GeoConfig $config
     * @return list<GeoLocationResolverInterface>
     */
    public function makeChain(array $names, array $config): array
    {
        if ($names === []) {
            throw new GeoConfigurationException('Geo resolver list is empty');
        }

        $resolvers = [];
        foreach ($names as $name) {
            $resolvers[] = $this->make($name, $config);
        }

        return $resolvers;
    }

    private function requireHttp(): ClientInterface
    {
        return $this->http ?? throw new GeoConfigurationException(
            'HTTP resolvers require a Guzzle client',
        );
    }

    /**
     * @param GeoConfig $config
     */
    private static function requireString(array $config, string $path): string
    {
        $value = self::valueAt($config, $path);
        if (!is_string($value) || $value === '') {
            throw new GeoConfigurationException(
                sprintf('Missing required geo config "%s"', $path),
            );
        }

        return $value;
    }

    /**
     * @param GeoConfig $config
     */
    private static function requireBool(array $config, string $path): bool
    {
        $value = self::valueAt($config, $path);
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($filtered !== null) {
                return $filtered;
            }
        }

        throw new GeoConfigurationException(
            sprintf('Missing required geo config "%s"', $path),
        );
    }

    /**
     * @param GeoConfig $config
     */
    private static function requireExistingFile(array $config, string $path): string
    {
        $file = self::requireString($config, $path);
        if (!is_file($file)) {
            throw new GeoConfigurationException(
                sprintf('Geo config "%s" points to missing file %s', $path, $file),
            );
        }

        return $file;
    }

    /**
     * @param GeoConfig $config
     */
    private static function valueAt(array $config, string $path): bool|float|int|string|array|null
    {
        $current = $config;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        if (is_bool($current) || is_float($current) || is_int($current) || is_string($current) || is_array($current)) {
            return $current;
        }

        return null;
    }
}
