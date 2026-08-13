<?php declare(strict_types = 1);

namespace kristijorgji\Money\Tests;

use kristijorgji\Money\Number;
use PHPUnit\Framework\TestCase;

class NumberTest extends TestCase
{
    /**
     * @dataProvider constructorValuesProvider
     */
    public function testConstructor(
        string $integerPart,
        string $fractionalPart,
        bool $isCorrect,
        string $expectedString = null
    )
    {
        if (!$isCorrect) {
            $this->expectException(\Throwable::class);
        }

        $number = new Number($integerPart, $fractionalPart);
        if ($expectedString !== null) {
            $this->assertEquals($expectedString, (string) $number);
        } {
            $this->assertTrue(true);
        }
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function constructorValuesProvider(): array
    {
        return [
            ['a', '2', false],
            ['2', 'a', false],
            ['a2', 'd2', false],
            ['-212424243', '121', true],
            ['-212424243', '-121', false],
            ['-', '121', true, '-0.121'],
        ];
    }

    /**
     * @dataProvider fromStringProvider
     */
    public function testFromString(string $input, bool $throwsException, ?string $expectedAsString = null)
    {
        if ($throwsException) {
            $this->expectException(\Throwable::class);
        }

        $number = Number::fromString($input);

        if ($expectedAsString !== null) {
            $this->assertEquals($expectedAsString, (string) $number);
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function fromStringProvider(): array
    {
        return [
            ['10', false, '10'],
            ['105.44', false, '105.44'],
            ['2a.3', true],
            ['-23', false, '-23'],
            ['-023', false, '-23'],
            ['-023.55', false, '-23.55'],
            ['023', false, '23'],
            ['023.34', false, '23.34'],
            ['-23.252', false],
            ['-23,2125', false, '-23.2125'],
            ['d', true],
            ['2.342', false],
            ['2,4423', false, '2.4423'],
            ['2', false],
            ['0', false],
            ['', true],
        ];
    }

    /**
     * @dataProvider fromFloatProvider
     */
    public function testFromFloat(float|int|string $input, bool $isCorrect, string $expectedString = '')
    {
        if (!$isCorrect) {
            $this->expectException(\Throwable::class);
        }

        $number = Number::fromFloat($input);
        $this->assertEquals($expectedString, (string) $number);
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function fromFloatProvider(): array
    {
        return [
            ['23.23', false],
            ['a23', false],
            [23212.455662323232323, true, '23212.45566232323108'],
            [23212.12345678, true, '23212.12345677999838'],
            [23212, true, '23212'],
            [023212, true, '9866'],
        ];
    }

    public function testIsDecimal()
    {
        $number = new Number('10');
        $this->assertFalse($number->isDecimal());

        $number = new Number('10', '2');
        $this->assertTrue($number->isDecimal());
    }

    public function testIsInteger()
    {
        $number = new Number('10');
        $this->assertTrue($number->isInteger());

        $number = new Number('10', '2');
        $this->assertFalse($number->isInteger());
    }

    public function testIsHalf()
    {
        $number = new Number('10', '5');
        $this->assertTrue($number->isHalf());

        $number = new Number('10', '2');
        $this->assertFalse($number->isInteger());
    }

    public function testIsNegative()
    {
        $number = new Number('10', '2');
        $this->assertFalse($number->isNegative());

        $number = new Number('-10', '2');
        $this->assertTrue($number->isNegative());
    }

    public function testGetIntegerPart()
    {
        $number = Number::fromString('12.2424212');
        $this->assertEquals('12', $number->getIntegerPart());
    }

    public function testFractionalPart()
    {
        $number = Number::fromString('12.12412');
        $this->assertEquals('12412', $number->getFractionalPart());
    }

    /**
     * @dataProvider toStringProvider
     */
    public function testToString(string $input)
    {
        $number = Number::fromString($input);
        $this->assertEquals($input, (string) $number);
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function toStringProvider(): array
    {
        return [
            ['12.231'],
            ['-1.552124'],
            ['34'],
            ['-12'],
            ['0'],
        ];
    }

    /**
     * @dataProvider numberExamples
     * @test
     */
    public function it_has_attributes(string $number, bool $decimal, bool $half, bool $currentEven, bool $negative, string $integerPart, string $fractionalPart)
    {
        $number = Number::fromString($number);
        $this->assertSame($decimal, $number->isDecimal());
        $this->assertSame($half, $number->isHalf());
        $this->assertSame($currentEven, $number->isCurrentEven());
        $this->assertSame($negative, $number->isNegative());
        $this->assertSame($integerPart, $number->getIntegerPart());
        $this->assertSame($fractionalPart, $number->getFractionalPart());
        $this->assertSame($negative ? '-1' : '1', $number->getIntegerRoundingMultiplier());
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function numberExamples(): array
    {
        return [
            ['0', false, false, true, false, '0', ''],
            ['0.00', false, false, true, false, '0', ''],
            ['0.5', true, true, true, false, '0', '5'],
            ['0.500', true, true, true, false, '0', '5'],
            ['-0', false, false, true, true, '-0', ''],
            ['-0.5', true, true, true, true, '-0', '5'],
            ['3', false, false, false, false, '3', ''],
            ['3.00', false, false, false, false, '3', ''],
            ['3.5', true, true, false, false, '3', '5'],
            ['3.500', true, true, false, false, '3', '5'],
            ['-3', false, false, false, true, '-3', ''],
            ['-3.5', true, true, false, true, '-3', '5'],
            ['10', false, false, true, false, '10', ''],
            ['10.00', false, false, true, false, '10', ''],
            ['10.5', true, true, true, false, '10', '5'],
            ['10.500', true, true, true, false, '10', '5'],
            ['10.9', true, false, true, false, '10', '9'],
            ['-10', false, false, true, true, '-10', ''],
            ['-0', false, false, true, true, '-0', ''],
            ['-10.5', true, true, true, true, '-10', '5'],
            ['-.5', true, true, true, true, '-0', '5'],
            ['.5', true, true, true, false, '0', '5'],
            [(string) PHP_INT_MAX, false, false, false, false, (string) PHP_INT_MAX, ''],
            [(string) -PHP_INT_MAX, false, false, false, true, (string) -PHP_INT_MAX, ''],
            [
                PHP_INT_MAX.PHP_INT_MAX.PHP_INT_MAX,
                false,
                false,
                false,
                false,
                PHP_INT_MAX.PHP_INT_MAX.PHP_INT_MAX,
                '',
            ],
            [
                -PHP_INT_MAX.PHP_INT_MAX.PHP_INT_MAX,
                false,
                false,
                false,
                true,
                -PHP_INT_MAX.PHP_INT_MAX.PHP_INT_MAX,
                '',
            ],
            [
                substr((string) PHP_INT_MAX, 0, strlen((string) PHP_INT_MAX) - 1).str_repeat('0', strlen((string) PHP_INT_MAX) - 1).PHP_INT_MAX,
                false,
                false,
                false,
                false,
                substr((string) PHP_INT_MAX, 0, strlen((string) PHP_INT_MAX) - 1).str_repeat('0', strlen((string) PHP_INT_MAX) - 1).PHP_INT_MAX,
                '',
            ],
        ];
    }

    /**
     * @dataProvider invalidNumberExamples
     * @test
     */
    public function it_fails_parsing_invalid_numbers(string $number)
    {
        $this->expectException(\Throwable::class);
        Number::fromString($number);
    }

    /**
     * @return array<string, array<int, bool|float|int|string|array|null|object>>
     */
    public function invalidNumberExamples(): array
    {
        return [
            [''],
            ['123456789012345678-123456'],
            ['---123'],
            ['123456789012345678+13456'],
            ['-123456789012345678.-13456'],
            ['+123456789'],
            ['+123456789012345678.+13456'],
        ];
    }

    public function testIsCloserToNext()
    {
        $number = new Number('10', '');
        $this->assertFalse($number->isCloserToNext());
    }
}
