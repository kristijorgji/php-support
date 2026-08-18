<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Iso;

use kristijorgji\Iso\Countries;
use PHPUnit\Framework\TestCase;

class CountriesTest extends TestCase
{
    public function test_union_includes_south_sudan_and_kosovo(): void
    {
        $this->assertSame('SS', Countries::SOUTH_SUDAN->value);
        $this->assertSame('XK', Countries::KOSOVO->value);
        $this->assertSame('DE', Countries::GERMANY->value);
        $this->assertCount(250, Countries::cases());
    }

    public function test_try_from_unknown_code_is_null(): void
    {
        $this->assertNull(Countries::tryFrom('EU'));
        $this->assertNull(Countries::tryFrom('AP'));
    }
}
