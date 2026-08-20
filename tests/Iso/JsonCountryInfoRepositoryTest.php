<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Iso;

use DateInterval;
use kristijorgji\Iso\Countries;
use kristijorgji\Iso\CountryInfo;
use kristijorgji\Iso\JsonCountryInfoRepository;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use function array_key_exists;
use function array_keys;
use function file_get_contents;
use function json_decode;

final class JsonCountryInfoRepositoryTest extends TestCase
{
    private const string DATA_DIR = __DIR__ . '/../../data/countries_info';
    private const array SPOT_CHECK = ['AL', 'XK', 'MK', 'RS', 'DE', 'GR', 'IT', 'CH', 'US'];

    public function test_en_and_sq_have_identical_keys_and_flag_emojis(): void
    {
        $en = $this->load('en');
        $sq = $this->load('sq');

        $this->assertSame(array_keys($en), array_keys($sq));
        $this->assertNotEmpty($en);

        foreach ($en as $code => $info) {
            $this->assertNotSame('', $info['name'], $code);
            $this->assertNotSame('', $sq[$code]['name'], $code);
            $this->assertSame($info['flagEmoji'], $sq[$code]['flagEmoji'], $code);
        }
    }

    public function test_sq_uses_albanian_names_for_spot_checked_countries(): void
    {
        $en = $this->load('en');
        $sq = $this->load('sq');
        $expected = [
            'AL' => 'Shqipëri',
            'XK' => 'Kosovë',
            'MK' => 'Maqedonia e Veriut',
            'RS' => 'Serbi',
            'DE' => 'Gjermani',
            'GR' => 'Greqi',
            'IT' => 'Itali',
            'CH' => 'Zvicër',
            'US' => 'Shtetet e Bashkuara',
        ];

        foreach (self::SPOT_CHECK as $code) {
            $this->assertSame($expected[$code], $sq[$code]['name']);
            $this->assertNotSame($en[$code]['name'], $sq[$code]['name']);
        }
    }

    public function test_find_returns_info_and_unknown_locale_falls_back_to_en(): void
    {
        $repo = new JsonCountryInfoRepository(dataDir: self::DATA_DIR);
        $al = $repo->find(Countries::ALBANIA, 'en');
        $this->assertInstanceOf(CountryInfo::class, $al);
        $this->assertSame('Albania', $al->getName());
        $this->assertSame('🇦🇱', $al->getFlagEmoji());

        $fallback = $repo->find(Countries::ALBANIA, 'de');
        $this->assertInstanceOf(CountryInfo::class, $fallback);
        $this->assertSame('Albania', $fallback->getName());
    }

    public function test_cached_country_info_map_is_reused(): void
    {
        $info = new CountryInfo(Countries::ALBANIA, 'Cached Albania', '🇦🇱');
        $cache = new ArraySimpleCache([
            'countriesInfo.v2.en' => ['AL' => $info],
        ]);

        $repo = new JsonCountryInfoRepository(cache: $cache, dataDir: self::DATA_DIR);
        $found = $repo->find(Countries::ALBANIA, 'en');

        $this->assertSame($info, $found);
        $this->assertSame(1, $cache->gets);
        $this->assertSame(0, $cache->sets);
    }

    public function test_cached_raw_json_map_is_hydrated_to_country_info(): void
    {
        $cache = new ArraySimpleCache([
            'countriesInfo.v2.en' => [
                'AL' => ['name' => 'Albania From Cache', 'flagEmoji' => '🇦🇱'],
            ],
        ]);

        $repo = new JsonCountryInfoRepository(cache: $cache, dataDir: self::DATA_DIR);
        $found = $repo->find(Countries::ALBANIA, 'en');

        $this->assertInstanceOf(CountryInfo::class, $found);
        $this->assertSame('Albania From Cache', $found->getName());
        $this->assertSame('🇦🇱', $found->getFlagEmoji());
        $this->assertSame(1, $cache->sets);
        $rewritten = $cache->store['countriesInfo.v2.en'];
        $this->assertIsArray($rewritten);
        $this->assertInstanceOf(CountryInfo::class, $rewritten['AL']);
    }

    public function test_junk_cache_entry_is_ignored_and_disk_is_loaded(): void
    {
        $cache = new ArraySimpleCache([
            'countriesInfo.v2.en' => ['AL' => 'not-an-object-or-map'],
        ]);

        $repo = new JsonCountryInfoRepository(cache: $cache, dataDir: self::DATA_DIR);
        $found = $repo->find(Countries::ALBANIA, 'en');

        $this->assertInstanceOf(CountryInfo::class, $found);
        $this->assertSame('Albania', $found->getName());
        $this->assertSame(1, $cache->sets);
        $rewritten = $cache->store['countriesInfo.v2.en'];
        $this->assertIsArray($rewritten);
        $this->assertInstanceOf(CountryInfo::class, $rewritten['AL']);
    }

    /**
     * @return array<string, array{name: string, flagEmoji: string}>
     */
    private function load(string $locale): array
    {
        $decoded = json_decode((string) file_get_contents(self::DATA_DIR . '/' . $locale . '.json'), true);
        $this->assertIsArray($decoded);

        return $decoded;
    }
}

/**
 * In-memory PSR-16 cache for repository tests.
 *
 * @internal
 * @phpstan-type CacheValue CountryInfo|array<string, CountryInfo|array<string, string>|string>|string
 */
final class ArraySimpleCache implements CacheInterface
{
    public int $gets = 0;
    public int $sets = 0;

    /**
     * @param array<string, CacheValue> $store
     */
    public function __construct(public array $store = [])
    {
    }

    /**
     * @param CacheValue|null $default
     * @return CacheValue|null
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->gets++;

        return $this->store[$key] ?? $default;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        unset($ttl);
        $this->sets++;
        /** @var CacheValue $value */
        $this->store[$key] = $value;

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->store[$key]);

        return true;
    }

    public function clear(): bool
    {
        $this->store = [];

        return true;
    }

    /**
     * @param iterable<string> $keys
     * @param CacheValue|null $default
     * @return array<string, CacheValue|null>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $this->get($key, $default);
        }

        return $out;
    }

    /**
     * @param iterable<string, CacheValue> $values
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set((string) $key, $value, $ttl);
        }

        return true;
    }

    /**
     * @param iterable<string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->store);
    }
}
