<?php declare(strict_types = 1);

namespace kristijorgji\Money\Tests\Calculator;

use kristijorgji\Money\Calculator\CalculatorInterface;
use kristijorgji\Money\Tests\RoundExamples;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function strlen;
use function substr;

abstract class CalculatorTestCase extends TestCase
{
    use RoundExamples;

    abstract protected function getCalculator(): CalculatorInterface;

    #[DataProvider('additionExamples')]
    #[Test]
    public function it_adds_two_values(
        int|float|string $value1,
        int|float|string $value2,
        int|float|string $expected,
    ): void {
        $this->assertEquals($expected, $this->getCalculator()->add($value1, $value2));
    }

    #[DataProvider('subtractionExamples')]
    #[Test]
    public function it_subtracts_a_value_from_another(
        int|float|string $value1,
        int|float|string $value2,
        int|float|string $expected,
    ): void {
        $this->assertEquals($expected, $this->getCalculator()->subtract($value1, $value2));
    }

    #[DataProvider('multiplicationExamples')]
    #[Test]
    public function it_multiplies_a_value_by_another(
        int|float|string $value1,
        int|float|string $value2,
        int|float|string $expected,
    ): void {
        $this->assertEquals($expected, $this->getCalculator()->multiply($value1, $value2));
    }

    #[DataProvider('divisionExamples')]
    #[Test]
    public function it_divides_a_value_by_another(
        int|float|string $value1,
        int|float|string $value2,
        int|float|string $expected,
    ): void {
        $result = $this->getCalculator()->divide($value1, $value2);
        $this->assertSame(substr($expected, 0, strlen($result)), $result);
    }

    #[DataProvider('ceilExamples')]
    #[Test]
    public function it_ceils_a_value(int|float|string $value, int|float|string $expected): void
    {
        $this->assertEquals($expected, $this->getCalculator()->ceil($value));
    }

    #[DataProvider('floorExamples')]
    #[Test]
    public function it_floors_a_value(int|float|string $value, int|float|string $expected): void
    {
        $this->assertEquals($expected, $this->getCalculator()->floor($value));
    }

    #[DataProvider('absoluteExamples')]
    #[Test]
    public function it_calculates_the_absolute_value(int|float|string $value, int|float|string $expected): void
    {
        $this->assertEquals($expected, $this->getCalculator()->absolute($value));
    }

    #[DataProvider('shareExamples')]
    #[Test]
    public function it_shares_a_value(
        int|float|string $value,
        int|float|string $ratio,
        int|float|string $total,
        int|float|string $expected,
    ): void {
        $this->assertEquals($expected, $this->getCalculator()->share($value, $ratio, $total));
    }

    #[DataProvider('roundExamples')]
    #[Test]
    public function it_rounds_a_value(int|float|string $value, int $mode, int|float|string $expected): void
    {
        $this->assertEquals($expected, $this->getCalculator()->round($value, $mode));
    }

    #[DataProvider('compareExamples')]
    #[Test]
    public function it_compares_values(int|float|string $left, int|float|string $right, int $expected): void
    {
        $this->assertSame($expected, $this->getCalculator()->compare($left, $right));
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public static function additionExamples(): array
    {
        return [
            [1, 1, '2.00000000000000'],
            [10, 5, '15.00000000000000'],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public static function subtractionExamples(): array
    {
        return [
            [1, 1, '0.00000000000000'],
            [10, 5, '5.00000000000000'],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public static function multiplicationExamples(): array
    {
        return [
            [1, 1.5, '1.50000000000000'],
            [10, 1.2500, '12.50000000000000'],
            [100, 0.29, '29.00000000000000'],
            [100, 0.029, '2.90000000000000'],
            [100, 0.0029, '0.29000000000000'],
            [1000, 0.29, '290.00000000000000'],
            [1000, 0.029, '29.00000000000000'],
            [1000, 0.0029, '2.90000000000000'],
            [2000, 0.0029, '5.80000000000000'],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public static function divisionExamples(): array
    {
        return [
            [6, 3, '2.00000000000000'],
            [100, 25, '4.00000000000000'],
            [2, 4, '0.50000000000000'],
            [20, 0.5, '40.00000000000000'],
            [2, 0.5, '4.00000000000000'],
            [181, 17, '10.64705882352941'],
            [98, 28, '3.50000000000000'],
            [98, 25, '3.92000000000000'],
            [98, 24, '4.083333333333333'],
            [1, 5.1555, '0.19396760740956'],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public static function ceilExamples(): array
    {
        return [
            [1.2, '2'],
            [-1.2, '-1'],
            [2.00, '2'],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public static function floorExamples(): array
    {
        return [
            [2.7, '2'],
            [-2.7, '-3'],
            [2.00, '2'],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public static function absoluteExamples(): array
    {
        return [
            [2, '2'],
            [-2, '2'],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public static function shareExamples(): array
    {
        return [
            [10, 2, 4, '5'],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public static function compareExamples(): array
    {
        return [
            [1, 0, 1],
            [1, 1, 0],
            [0, 1, -1],
            ['1', '0', 1],
            ['1', '1', 0],
            ['0', '1', -1],
            ['1', '0.0005', 1],
            ['1', '0.000000000000000000000000005', 1],
        ];
    }
}
