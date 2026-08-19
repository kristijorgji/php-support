<?php declare(strict_types = 1);

namespace kristijorgji\Money;

use InvalidArgumentException;
use JsonSerializable;
use kristijorgji\Money\Calculator\BcMathCalculator;
use kristijorgji\Money\Calculator\CalculatorInterface;
use Stringable;
use function get_debug_type;
use function in_array;
use function is_float;
use function is_numeric;
use function sprintf;
use const PHP_ROUND_HALF_DOWN;
use const PHP_ROUND_HALF_EVEN;
use const PHP_ROUND_HALF_ODD;
use const PHP_ROUND_HALF_UP;

final class Money implements JsonSerializable
{
    public const int ROUND_NONE = 0;
    public const int ROUND_HALF_UP = PHP_ROUND_HALF_UP;
    public const int ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN;
    public const int ROUND_HALF_EVEN = PHP_ROUND_HALF_EVEN;
    public const int ROUND_HALF_ODD = PHP_ROUND_HALF_ODD;
    public const int ROUND_UP = 5;
    public const int ROUND_DOWN = 6;
    public const int ROUND_HALF_POSITIVE_INFINITY = 7;
    public const int ROUND_HALF_NEGATIVE_INFINITY = 8;

    private readonly Number $amount;
    private readonly Currency $currency;
    private static ?CalculatorInterface $calculator = null;

    /**
     *
     * @throws InvalidArgumentException
     */
    public function __construct(float|int|string $value, Currency|string|Stringable $currency)
    {
        $this->currency = $currency instanceof Currency ? $currency : Currency::from($currency);
        $numberFromString = Number::fromString((string) $value);
        $this->amount = $numberFromString;
    }

    public function add(Money $addend): Money
    {
        $this->assertSameCurrency($addend);
        return new self(
            $this->getCalculator()->add(
                (string) $this->amount,
                (string) $addend->amount,
            ),
            $this->currency,
        );
    }

    public function subtract(Money $subtrahend): Money
    {
        $this->assertSameCurrency($subtrahend);
        return new self(
            $this->getCalculator()->subtract((string) $this->amount, (string) $subtrahend->amount),
            $this->currency,
        );
    }

    public function multiply(float|int|string $multiplier, int $roundingMode = self::ROUND_NONE): Money
    {
        $this->assertOperand($multiplier);
        $this->assertRoundingMode($roundingMode);

        if (is_float($multiplier)) {
            $multiplier = (string) Number::fromFloat($multiplier);
        }

        if ($roundingMode === self::ROUND_NONE) {
            return $this->newInstance($this->getCalculator()->multiply((string) $this->amount, $multiplier));
        }

        $product = $this->round(
            $this->getCalculator()->multiply(
                (string) $this->amount,
                (string) $multiplier,
            ),
            $roundingMode,
        );

        return $this->newInstance($product);
    }

    public function divide(float|int|string $divisor, int $roundingMode = self::ROUND_NONE): Money
    {
        $this->assertOperand($divisor);
        $this->assertRoundingMode($roundingMode);

        if (is_float($divisor)) {
            $divisor = (string) Number::fromFloat($divisor);
        }

        $calculator = $this->getCalculator();

        if ($calculator->compare((string) $divisor, '0') === 0) {
            throw new InvalidArgumentException('Division by zero');
        }

        if ($roundingMode === self::ROUND_NONE) {
            return $this->newInstance($calculator->divide((string) $this->amount, $divisor));
        }

        $quotient = $this->round($calculator->divide((string) $this->amount, (string) $divisor), $roundingMode);
        return $this->newInstance($quotient);
    }

    public function absolute() : Money
    {
        return $this->newInstance($this->getCalculator()->absolute((string) $this->amount));
    }

    /**
     * Checks whether the value represented by this object equals to the other.
     *
     *
     */
    public function equals(Money $other): bool
    {
        return $this->isSameCurrency($other) && (string) $this->amount === (string) $other->amount;
    }

    /**
     * Returns an integer less than, equal to, or greater than zero
     * if the value of this object is considered to be respectively
     * less than, equal to, or greater than the other.
     *
     *
     */
    public function compare(Money $other) : int
    {
        $this->assertSameCurrency($other);
        return $this->getCalculator()->compare((string) $this->amount, (string) $other->amount);
    }

    /**
     * Checks whether the value represented by this object is greater than the other.
     *
     *
     */
    public function greaterThan(Money $other) : bool
    {
        return $this->compare($other) === 1;
    }

    public function greaterThanOrEqual(Money $other) : bool
    {
        return $this->compare($other) >= 0;
    }

    /**
     * Checks whether the value represented by this object is less than the other.
     *
     *
     */
    public function lessThan(Money $other) : bool
    {
        return $this->compare($other) === -1;
    }

    public function lessThanOrEqual(Money $other) : bool
    {
        return $this->compare($other) <= 0;
    }

    /**
     * Checks if the value represented by this object is zero.
     *
     */
    public function isZero() : bool
    {
        return $this->getCalculator()->compare((string) $this->amount, '0') === 0;
    }

    /**
     * Checks if the value represented by this object is positive.
     *
     */
    public function isPositive() : bool
    {
        return $this->getCalculator()->compare((string) $this->amount, '0') === 1;
    }

    /**
     * Checks if the value represented by this object is negative.
     *
     */
    public function isNegative() : bool
    {
        return $this->getCalculator()->compare((string) $this->amount, '0') === -1;
    }

    public function isSameCurrency(Money $other) : bool
    {
        return (string) $this->currency === (string) $other->getCurrency();
    }

    public function getAmount(): string
    {
        return (string) $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    private function getCalculator() : CalculatorInterface
    {
        if (self::$calculator === null) {
            self::$calculator = new BcMathCalculator;
        }

        return self::$calculator;
    }

    /**
     * @throws InvalidArgumentException If $other has a different currency
     */
    private function assertSameCurrency(Money $other): void
    {
        if (!$this->isSameCurrency($other)) {
            throw new InvalidArgumentException('Currencies must be identical');
        }
    }

    /**
     * Asserts that the operand is integer or float.
     *
     *
     * @throws InvalidArgumentException If $operand is neither integer nor float
     */
    private function assertOperand(float|int|string $operand): void
    {
        if (!is_numeric($operand)) {
            throw new InvalidArgumentException(sprintf(
                'Operand should be a numeric value, "%s" given.',
                get_debug_type($operand),
            ));
        }
    }

    /**
     * Asserts that rounding mode is a valid integer value.
     *
     *
     * @throws InvalidArgumentException If $roundingMode is not valid
     */
    private function assertRoundingMode(int $roundingMode): void
    {
        if (!in_array(
            $roundingMode,
            [
                self::ROUND_NONE,
                self::ROUND_HALF_DOWN,
                self::ROUND_HALF_EVEN,
                self::ROUND_HALF_ODD,
                self::ROUND_HALF_UP,
                self::ROUND_UP,
                self::ROUND_DOWN,
                self::ROUND_HALF_POSITIVE_INFINITY,
                self::ROUND_HALF_NEGATIVE_INFINITY,
            ],
            true,
        )) {
            throw new InvalidArgumentException(
                'Rounding mode should be Money::ROUND_NONE | Money::ROUND_HALF_DOWN | '.
                'Money::ROUND_HALF_EVEN | Money::ROUND_HALF_ODD | '.
                'Money::ROUND_HALF_UP | Money::ROUND_UP | Money::ROUND_DOWN'.
                'Money::ROUND_HALF_POSITIVE_INFINITY | Money::ROUND_HALF_NEGATIVE_INFINITY',
            );
        }
    }

    private function round(int|float|string $amount, int $roundingMode) : string
    {
        $this->assertRoundingMode($roundingMode);

        if ($roundingMode === self::ROUND_UP) {
            return $this->getCalculator()->ceil((string) $amount);
        }

        if ($roundingMode === self::ROUND_DOWN) {
            return $this->getCalculator()->floor((string) $amount);
        }

        return $this->getCalculator()->round((string) $amount, $roundingMode);
    }

    /**
     * Returns a new Money instance based on the current one using the Currency.
     *
     *
     *
     * @throws InvalidArgumentException If amount is not valid number
     */
    private function newInstance(int|string $amount) : Money
    {
        return new self($amount, $this->currency);
    }

    /**
     * @return array{amount: float, currency: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'amount' => (float) ((string) $this->amount),
            'currency' => (string) $this->currency,
        ];
    }
}
