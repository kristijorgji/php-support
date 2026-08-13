<?php declare(strict_types = 1);

namespace kristijorgji\Money\Tests;


use kristijorgji\Money\Money;
use kristijorgji\Money\Currency;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    use RoundExamples;
    const AMOUNT = 10;
    const OTHER_AMOUNT = 5;
    const CURRENCY = 'EUR';
    const OTHER_CURRENCY = 'USD';

    public function test_it_creates_money()
    {
        $money = new Money('20', Currency::from('AUD'));
        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals('20', $money->getAmount());
        $this->assertEquals('AUD', (string) $money->getCurrency());
    }

    /**
     * @dataProvider equalityExamples
     * @test
     */
    public function it_equals_to_another_money(int|string $amount, Currency $currency, bool $equality)
    {
        $money = new Money(self::AMOUNT, Currency::from(self::CURRENCY));
        $this->assertEquals($equality, $money->equals(new Money($amount, $currency)));
    }

    /**
     * @dataProvider comparisonExamples
     * @test
     */
    public function it_compares_two_amounts(int $other, int $result)
    {
        $money = new Money(self::AMOUNT, Currency::from(self::CURRENCY));
        $other = new Money($other, Currency::from(self::CURRENCY));
        $this->assertEquals($result, $money->compare($other));
        $this->assertEquals(1 === $result, $money->greaterThan($other));
        $this->assertEquals(0 <= $result, $money->greaterThanOrEqual($other));
        $this->assertEquals(-1 === $result, $money->lessThan($other));
        $this->assertEquals(0 >= $result, $money->lessThanOrEqual($other));
    }

    /**
     * @dataProvider roundExamples
     * @test
     */
    public function it_multiplies_the_amount(float|int|string $multiplier, int $roundingMode, int|string $result)
    {
        $money = new Money('1', Currency::from(self::CURRENCY));
        $money = $money->multiply($multiplier, $roundingMode);
        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals((string) $result, $money->getAmount());
    }

    /**
     * @dataProvider multiplyWithoutRoundingExamples
     * @test
     */
    public function it_multiplies_without_rounding(string $multiplier, string $result)
    {
        $money = new Money('1', Currency::from(self::CURRENCY));
        $money = $money->multiply($multiplier);
        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals((string) $result, $money->getAmount());
    }

    /**
     * @test
     */
    public function it_multiplies_the_amount_with_locale_that_uses_comma_separator()
    {
        setlocale(LC_ALL, 'es_ES.utf8');
        $money = new Money('100', Currency::from(self::CURRENCY));
        $money = $money->multiply(10 / 100);
        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals(10, $money->getAmount());
        setlocale(LC_ALL, null);
    }

    /**
     * @dataProvider invalidOperandExamples
     * @test
     * @param array<string, bool|float|int|string|array|null>|bool|string|null|object $operand
     */
    public function it_throws_an_exception_when_operand_is_invalid_during_multiplication(array|bool|string|object|null $operand)
    {
        $this->expectException(\Throwable::class);
        $money = new Money('1', Currency::from(self::CURRENCY));
        $money->multiply($operand);
    }

    /**
     * @dataProvider divideExamples
     * @test
     */
    public function it_divides_the_amount(int|float|string $value, int|float|string $divisor, int $roundingMode, int|string $result)
    {
        $money = new Money($value, Currency::from(self::CURRENCY));
        $money = $money->divide($divisor, $roundingMode);
        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals((string) $result, $money->getAmount());
    }

    /**
     * @dataProvider invalidOperandExamples
     * @test
     * @param array<string, bool|float|int|string|array|null>|bool|string|null|object $operand
     */
    public function it_throws_an_exception_when_operand_is_invalid_during_division(array|bool|string|object|null $operand)
    {
        $this->expectException(\Throwable::class);
        $money = new Money('1', Currency::from(self::CURRENCY));
        $money->divide($operand);
    }

    /**
     * @dataProvider comparatorExamples
     * @test
     */
    public function it_has_comparators(int|string $amount, bool $isZero, bool $isPositive, bool $isNegative)
    {
        $money = new Money($amount, Currency::from(self::CURRENCY));
        $this->assertEquals($isZero, $money->isZero());
        $this->assertEquals($isPositive, $money->isPositive());
        $this->assertEquals($isNegative, $money->isNegative());
    }

    /**
     * @dataProvider absoluteExamples
     * @test
     */
    public function it_calculates_the_absolute_amount(int|string $amount, int $result)
    {
        $money = new Money($amount, Currency::from(self::CURRENCY));
        $money = $money->absolute();
        $this->assertEquals($result, $money->getAmount());
    }

    public function test_it_converts_to_json()
    {
        $this->assertEquals(
            '{"amount":350,"currency":"EUR"}',
            json_encode(new Money('350', Currency::from('EUR')))
        );
    }

    public function test_it_supports_max_int()
    {
        $one = new Money('1', Currency::from('EUR'));
        $this->assertInstanceOf(Money::class, new Money(PHP_INT_MAX, Currency::from('EUR')));
        $this->assertInstanceOf(Money::class, (new Money(PHP_INT_MAX, Currency::from('EUR')))->add($one));
        $this->assertInstanceOf(Money::class, (new Money(PHP_INT_MAX, Currency::from('EUR')))->subtract($one));
    }

    public function test_divide_by_zero()
    {
        $this->expectException(\InvalidArgumentException::class);
        $one = new Money('1', Currency::from('EUR'));
        $one->divide(0);
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function equalityExamples(): array
    {
        return [
            [self::AMOUNT, Currency::from(self::CURRENCY), true],
            [self::AMOUNT + 1, Currency::from(self::CURRENCY), false],
            [self::AMOUNT, Currency::from(self::OTHER_CURRENCY), false],
            [self::AMOUNT + 1, Currency::from(self::OTHER_CURRENCY), false],
            [(string) self::AMOUNT, Currency::from(self::CURRENCY), true],
            [((string) self::AMOUNT).'.000', Currency::from(self::CURRENCY), true],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function multiplyWithoutRoundingExamples(): array
    {
        return [
            ['12.98989895654', '12.98989895654'],
            ['12.98989895654', '12.98989895654'],
            ['4.7', '4.7'],
            ['4', '4'],
            ['-4.2323', '-4.2323'],
            ['0', '0'],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function divideExamples(): array
    {
        return [
            [10, 4, Money::ROUND_UP, '3'],
            [10, 4, Money::ROUND_HALF_EVEN, '2'],
            [10, -3.9, Money::ROUND_HALF_EVEN, '-3'],
            [10, -4, Money::ROUND_HALF_EVEN, '-2'],
            [1, 1 / 2.1, Money::ROUND_HALF_ODD, '2'],
            [1, 1 / 2.5, Money::ROUND_HALF_ODD, '3'],
            [1, 1 / 3.5, Money::ROUND_HALF_ODD, '3'],
            [1, 1 / -2.1, Money::ROUND_HALF_ODD, '-2'],
            [1, 1 / -2.5, Money::ROUND_HALF_ODD, '-3'],
            [1, 1 / -3.5, Money::ROUND_HALF_ODD, '-3'],
            [1, 1 / 2, Money::ROUND_HALF_EVEN, '2'],
            [1, 1 / 2, Money::ROUND_HALF_ODD, '2'],
            [1, 1 / -2, Money::ROUND_HALF_ODD, '-2'],
            [1, 1 / 2.5, Money::ROUND_HALF_DOWN, '2'],
            [1, 1 / 2.6, Money::ROUND_HALF_DOWN, '3'],
            [1, 1 / -2.5, Money::ROUND_HALF_DOWN, '-2'],
            [1, 1 / -2.6, Money::ROUND_HALF_DOWN, '-3'],
            [1, 1 / 2.2, Money::ROUND_HALF_UP, '2'],
            [1, 1 / 2.5, Money::ROUND_HALF_UP, '3'],
            [1, 1 / 2, Money::ROUND_HALF_UP, '2'],
            [1, 1 / -2.5, Money::ROUND_HALF_UP, '-3'],
            [1, 1 / -2, Money::ROUND_HALF_UP, '-2'],
            [1, 1 / 2, Money::ROUND_HALF_DOWN, '2'],
            [1, 1 / '12.50', Money::ROUND_HALF_DOWN, '12'],
            [1, 1 / '-12.50', Money::ROUND_HALF_DOWN, '-12'],
            [1, 1 / -8328.578947368, Money::ROUND_HALF_UP, '-8329'],
            [1, 1 / -8328.5, Money::ROUND_HALF_UP, '-8329'],
            [1, 1 / 2.5, Money::ROUND_HALF_POSITIVE_INFINITY, '3'],
            [1, 1 / 2.6, Money::ROUND_HALF_POSITIVE_INFINITY, '3'],
            [1, 1 / -2.6, Money::ROUND_HALF_POSITIVE_INFINITY, '-3'],
            [1, 1 / 2, Money::ROUND_HALF_POSITIVE_INFINITY, '2'],
            [1, 1 / '12.50', Money::ROUND_HALF_POSITIVE_INFINITY, '13'],
            [1, 1 / '-12.50', Money::ROUND_HALF_POSITIVE_INFINITY, '-12'],
            [4, 1.7, Money::ROUND_HALF_NEGATIVE_INFINITY, '2'],
            [1, 1 / -2.5, Money::ROUND_HALF_NEGATIVE_INFINITY, '-3'],
            [10, -4, Money::ROUND_HALF_NEGATIVE_INFINITY, '-3'],
            [1, 1 / -8328.578947368, Money::ROUND_HALF_NEGATIVE_INFINITY, '-8329'],
            [1, 1 / -8328.5, Money::ROUND_HALF_NEGATIVE_INFINITY, '-8329'],
            [10, 2.3, Money::ROUND_NONE, '4.34782608695652'],
            [10, 4, Money::ROUND_NONE, '2.5']
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function comparisonExamples(): array
    {
        return [
            [self::AMOUNT, 0],
            [self::AMOUNT - 1, 1],
            [self::AMOUNT + 1, -1],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function invalidOperandExamples(): array
    {
        return [
            [[]],
            [false],
            ['operand'],
            [null],
            [new \stdClass()],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function comparatorExamples(): array
    {
        return [
            [1, false, true, false],
            [0, true, false, false],
            [-1, false, false, true],
            ['1', false, true, false],
            ['0', true, false, false],
            ['-1', false, false, true],
        ];
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function absoluteExamples(): array
    {
        return [
            [1, 1],
            [0, 0],
            [-1, 1],
            ['1', 1],
            ['0', 0],
            ['-1', 1],
        ];
    }

    public function test_wrong_round_mode()
    {
        $this->expectException(\InvalidArgumentException::class);
        $one = new Money('1', Currency::from('EUR'));
        $one->divide(1, 222);
    }

    public function test_divide_round_down()
    {
        $one = new Money('3', Currency::from('EUR'));
        $this->assertEquals(
            '1',
            $one->divide(2, Money::ROUND_DOWN)->getAmount()
        );
    }
}
