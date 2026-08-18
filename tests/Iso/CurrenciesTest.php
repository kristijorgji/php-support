<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Iso;

use kristijorgji\Iso\Currencies;
use PHPUnit\Framework\TestCase;

class CurrenciesTest extends TestCase
{
    public function test_allowlist(): void
    {
        $this->assertSame(
            ['ALL', 'AUD', 'CAD', 'CHF', 'EUR', 'GBP', 'NZD', 'RUB', 'USD'],
            array_map(static fn (Currencies $c): string => $c->value, Currencies::cases()),
        );
    }
}
