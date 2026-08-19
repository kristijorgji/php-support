<?php declare(strict_types = 1);

namespace kristijorgji\Money\Tests\Calculator;

use InvalidArgumentException;
use kristijorgji\Money\Calculator\BcMathCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use function bcscale;
use function ini_get;

#[RequiresPhpExtension('bcmath')]
final class BcMathCalculatorTest extends CalculatorTestCase
{
    private string|false $defaultScale;

    protected function getCalculator(): BcMathCalculator
    {
        return new BcMathCalculator;
    }

    protected function setUp(): void
    {
        $this->defaultScale = ini_get('bcmath.scale');
    }

    protected function tearDown(): void
    {
        bcscale($this->defaultScale === false ? 0 : (int) $this->defaultScale);
    }

    #[DataProvider('additionExamples')]
    #[Test]
    public function it_adds_two_values_with_scale_set(
        int|float|string $value1,
        int|float|string $value2,
        int|float|string $expected,
    ): void {
        $this->assertEquals($expected, $this->getCalculator()->add($value1, $value2));
    }

    #[DataProvider('subtractionExamples')]
    #[Test]
    public function it_subtracts_a_value_from_another_with_scale_set(
        int|float|string $value1,
        int|float|string $value2,
        int|float|string $expected,
    ): void {
        $this->assertEquals($expected, $this->getCalculator()->subtract($value1, $value2));
    }

    #[Test]
    public function it_compares_numbers_close_to_zero(): void
    {
        $this->assertSame(1, $this->getCalculator()->compare('1', '0.0005'));
        $this->assertSame(1, $this->getCalculator()->compare('1', '0.000000000000000000000000005'));
    }

    public function test_round_with_wrong_round_mode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown rounding mode');
        echo ($this->getCalculator()->round('2,5', 2342424));
    }
}
