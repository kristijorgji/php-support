<?php declare(strict_types = 1);

namespace kristijorgji\Iso;

use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use function file_get_contents;
use function is_file;
use function json_decode;
use function sprintf;

final class JsonCountryInfoRepository implements CountryInfoRepositoryInterface
{
    private const FALLBACK_LOCALE = 'en';

    public function __construct(
        private readonly ?CacheInterface $cache = null,
        private readonly int $ttlSeconds = 604800,
        private readonly ?string $dataDir = null,
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
        $cacheKey = sprintf('countriesInfo.%s', $normalized);
        if ($this->cache !== null) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
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

        $result = [];
        foreach ($decoded as $code => $info) {
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
