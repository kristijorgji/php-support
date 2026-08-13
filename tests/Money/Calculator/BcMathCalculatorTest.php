<?php declare(strict_types = 1);

namespace kristijorgji\Money\Tests\Calculator;

use kristijorgji\Money\Calculator\BcMathCalculator;

/**
 * @requires extension bcmath
 */
final class BcMathCalculatorTest extends CalculatorTestCase
{
    private string|false $defaultScale;

    protected function getCalculator(): BcMathCalculator
    {
        return new BcMathCalculator();
    }

    public function setUp(): void
    {
        $this->defaultScale = ini_get('bcmath.scale');
    }

    public function tearDown(): void
    {
        bcscale($this->defaultScale === false ? 0 : (int) $this->defaultScale);
    }

    /**
     * @dataProvider additionExamples
     * @test
     */
    public function it_adds_two_values_with_scale_set(int|float|string $value1, int|float|string $value2, int|float|string $expected)
    {
        $this->assertEquals($expected, $this->getCalculator()->add($value1, $value2));
    }

    /**
     * @dataProvider subtractionExamples
     * @test
     */
    public function it_subtracts_a_value_from_another_with_scale_set(int|float|string $value1, int|float|string $value2, int|float|string $expected)
    {
        $this->assertEquals($expected, $this->getCalculator()->subtract($value1, $value2));
    }

    /**
     * @test
     */
    public function it_compares_numbers_close_to_zero()
    {
        $this->assertEquals(1, $this->getCalculator()->compare('1', '0.0005'));
        $this->assertEquals(1, $this->getCalculator()->compare('1', '0.000000000000000000000000005'));
    }

    public function test_round_with_wrong_round_mode()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown rounding mode');
        echo ($this->getCalculator()->round('2,5', 2342424));
    }
}
