<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Support;

use kristijorgji\Support\Environments;
use PHPUnit\Framework\TestCase;

final class EnvironmentsTest extends TestCase
{
    public function test_cases(): void
    {
        $this->assertSame('production', Environments::PRODUCTION->value);
        $this->assertSame('testing', Environments::TESTING->value);
        $this->assertCount(5, Environments::cases());
    }
}
