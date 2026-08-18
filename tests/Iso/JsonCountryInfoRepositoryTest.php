<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Iso;

use kristijorgji\Iso\Countries;
use kristijorgji\Iso\JsonCountryInfoRepository;
use PHPUnit\Framework\TestCase;
use function array_keys;
use function file_get_contents;
use function json_decode;

class JsonCountryInfoRepositoryTest extends TestCase
{
    private const DATA_DIR = __DIR__ . '/../../data/countries_info';

    /** @var list<string> */
    private const SPOT_CHECK = ['AL', 'XK', 'MK', 'RS', 'DE', 'GR', 'IT', 'CH', 'US'];

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
        $this->assertNotNull($al);
        $this->assertSame('Albania', $al->getName());
        $this->assertSame('🇦🇱', $al->getFlagEmoji());

        $fallback = $repo->find(Countries::ALBANIA, 'de');
        $this->assertNotNull($fallback);
        $this->assertSame('Albania', $fallback->getName());
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
