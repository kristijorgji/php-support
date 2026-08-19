<?php declare(strict_types = 1);

namespace kristijorgji\Money\Calculator;

use InvalidArgumentException;
use kristijorgji\Money\Money;
use kristijorgji\Money\Number;
use function bcadd;
use function bccomp;
use function bcdiv;
use function bcmul;
use function bcsub;
use function ltrim;

final readonly class BcMathCalculator implements CalculatorInterface
{
    public function __construct(private int $scale = 14)
    {
    }

    public function compare(float|int|string $a, float|int|string $b) : int
    {
        return bccomp((string) $a, (string) $b, $this->scale);
    }

    public function add(float|int|string $amount, float|int|string $addend) : string
    {
        return bcadd((string) $amount, (string) $addend, $this->scale);
    }

    public function subtract(float|int|string $amount, float|int|string $subtrahend) : string
    {
        return bcsub((string) $amount, (string) $subtrahend, $this->scale);
    }

    public function multiply(float|int|string $amount, float|int|string $multiplier) : string
    {
        return bcmul((string) $amount, (string) $multiplier, $this->scale);
    }

    public function divide(float|int|string $amount, float|int|string $divisor) : string
    {
        return bcdiv((string) $amount, (string) $divisor, $this->scale);
    }

    public function ceil(float|int|string $number) : string
    {
        $number = Number::fromString((string) $number);
        if ($number->isDecimal() === false) {
            return (string) $number;
        }
        if ($number->isNegative()) {
            return bcadd((string) $number, '0', 0);
        }
        return bcadd((string) $number, '1', 0);
    }

    public function floor(float|int|string $number) : string
    {
        $number = Number::fromString((string) $number);
        if ($number->isDecimal() === false) {
            return (string) $number;
        }
        if ($number->isNegative()) {
            return bcadd((string) $number, '-1', 0);
        }
        return bcadd((string) $number, '0', 0);
    }

    public function absolute(float|int|string $number) : string
    {
        return ltrim((string) $number, '-');
    }

    public function round(float|int|string $number, int $roundingMode) : string
    {
        $number = Number::fromString((string) $number);
        if ($number->isDecimal() === false) {
            return (string) $number;
        }
        if ($number->isHalf() === false) {
            return $this->roundDigit($number);
        }
        if ($roundingMode === Money::ROUND_HALF_UP) {
            return bcadd(
                (string) $number,
                $number->getIntegerRoundingMultiplier(),
                0,
            );
        }
        if ($roundingMode === Money::ROUND_HALF_DOWN) {
            return bcadd((string) $number, '0', 0);
        }
        if ($roundingMode === Money::ROUND_HALF_EVEN) {
            if ($number->isCurrentEven()) {
                return bcadd((string) $number, '0', 0);
            }
            return bcadd(
                (string) $number,
                $number->getIntegerRoundingMultiplier(),
                0,
            );
        }
        if ($roundingMode === Money::ROUND_HALF_ODD) {
            if ($number->isCurrentEven()) {
                return bcadd(
                    (string) $number,
                    $number->getIntegerRoundingMultiplier(),
                    0,
                );
            }
            return bcadd((string) $number, '0', 0);
        }
        if ($roundingMode === Money::ROUND_HALF_POSITIVE_INFINITY) {
            if ($number->isNegative()) {
                return bcadd((string) $number, '0', 0);
            }
            return bcadd(
                (string) $number,
                $number->getIntegerRoundingMultiplier(),
                0,
            );
        }
        if ($roundingMode === Money::ROUND_HALF_NEGATIVE_INFINITY) {
            if ($number->isNegative()) {
                return bcadd(
                    (string) $number,
                    $number->getIntegerRoundingMultiplier(),
                    0,
                );
            }
            return bcadd(
                (string) $number,
                '0',
                0,
            );
        }
        throw new InvalidArgumentException('Unknown rounding mode');
    }

    /**
     * @param $number
     *
     */
    private function roundDigit(Number $number): string
    {
        if ($number->isCloserToNext()) {
            return bcadd(
                (string) $number,
                $number->getIntegerRoundingMultiplier(),
                0,
            );
        }
        return bcadd((string) $number, '0', 0);
    }

    public function share(float|int|string $amount, float|int $ratio, float|int $total) : string
    {
        return $this->floor(
            bcdiv(
                bcmul((string) $amount, (string) $ratio, $this->scale),
                (string) $total,
                $this->scale,
            ),
        );
    }
}
