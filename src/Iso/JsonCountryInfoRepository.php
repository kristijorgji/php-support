<?php declare(strict_types = 1);

namespace kristijorgji\Iso;

use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use function dirname;
use function file_get_contents;
use function is_array;
use function is_file;
use function is_string;
use function json_decode;
use function sprintf;

final readonly class JsonCountryInfoRepository implements CountryInfoRepositoryInterface
{
    private const string FALLBACK_LOCALE = 'en';

    /** Bumped so poisoned pre-0.3.1 Redis entries (raw JSON arrays) are ignored. */
    private const string CACHE_KEY_PREFIX = 'countriesInfo.v2.';

    public function __construct(
        private ?CacheInterface $cache = null,
        private int $ttlSeconds = 604800,
        private ?string $dataDir = null,
    ) {
    }

    public function find(Countries $country, string $locale): ?CountryInfo
    {
        return $this->all($locale)[$country->value] ?? null;
    }

    /**
     * @return array<string, CountryInfo>
     */
    public function all(string $locale): array
    {
        $normalized = $locale !== '' ? $locale : self::FALLBACK_LOCALE;
        $cacheKey = self::CACHE_KEY_PREFIX . $normalized;
        if ($this->cache !== null) {
            $fromCache = $this->countryInfoMapFromCache($this->cache->get($cacheKey), $cacheKey);
            if ($fromCache !== null) {
                return $fromCache;
            }
        }

        $data = $this->loadLocale($normalized);
        if ($data === [] && $normalized !== self::FALLBACK_LOCALE) {
            $data = $this->loadLocale(self::FALLBACK_LOCALE);
        }

        if ($this->cache !== null) {
            $this->cache->set($cacheKey, $data, $this->ttlSeconds);
        }

        return $data;
    }

    /**
     * @return array<string, CountryInfo>
     */
    private function loadLocale(string $locale): array
    {
        $path = sprintf('%s/%s.json', $this->resolvedDataDir(), $locale);
        if (!is_file($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException(sprintf('Unable to read country info file %s', $path));
        }

        /** @var array<string, array{name: string, flagEmoji: string}>|null $decoded */
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf('Invalid country info JSON in %s', $path));
        }

        return $this->hydrateLocaleMap($decoded);
    }

    /**
     * Accept only CountryInfo maps or hydrate raw JSON-shaped maps; ignore junk.
     *
     * @return array<string, CountryInfo>|null
     */
    private function countryInfoMapFromCache(mixed $cached, string $cacheKey): ?array
    {
        if (!is_array($cached)) {
            return null;
        }

        if ($cached === []) {
            return [];
        }

        $allCountryInfo = true;
        $allRawMaps = true;
        foreach ($cached as $value) {
            if (!$value instanceof CountryInfo) {
                $allCountryInfo = false;
            }
            if (!is_array($value)) {
                $allRawMaps = false;
            }
        }

        if ($allCountryInfo) {
            /** @var array<string, CountryInfo> $cached */
            return $cached;
        }

        if (!$allRawMaps) {
            return null;
        }

        /** @var array<string, array{name?: string, flagEmoji?: string}> $cached */
        $hydrated = $this->hydrateLocaleMap($cached);
        if ($this->cache !== null && $hydrated !== []) {
            $this->cache->set($cacheKey, $hydrated, $this->ttlSeconds);
        }

        return $hydrated;
    }

    /**
     * @param array<string, array{name?: string, flagEmoji?: string}> $decoded
     * @return array<string, CountryInfo>
     */
    private function hydrateLocaleMap(array $decoded): array
    {
        $result = [];
        foreach ($decoded as $code => $info) {
            if (!is_string($code) || !is_array($info)) {
                continue;
            }

            $country = Countries::tryFrom($code);
            if ($country === null) {
                continue;
            }

            $result[$code] = new CountryInfo(
                $country,
                $info['name'] ?? '',
                $info['flagEmoji'] ?? '',
            );
        }

        return $result;
    }

    private function resolvedDataDir(): string
    {
        return $this->dataDir ?? dirname(__DIR__, 2) . '/data/countries_info';
    }
}
