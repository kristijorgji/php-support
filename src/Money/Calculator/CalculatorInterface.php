<?php declare(strict_types = 1);

namespace kristijorgji\Money\Calculator;

interface CalculatorInterface
{
    /**
     * Compare a to b.
     *
     */
    public function compare(float|int|string $a, float|int|string $b) : int;

    /**
     * Add added to amount.
     *
     */
    public function add(float|int|string $amount, float|int|string $addend) : string;

    /**
     * Subtract subtrahend from amount.
     *
     */
    public function subtract(float|int|string $amount, float|int|string $subtrahend) : string;

    /**
     * Multiply amount with multiplier.
     *
     */
    public function multiply(float|int|string $amount, float|int|string $multiplier) : string;

    /**
     * Divide amount with divisor.
     *
     */
    public function divide(float|int|string $amount, float|int|string $divisor) : string;

    /**
     * Round number to following integer.
     *
     */
    public function ceil(float|int|string $number) : string;

    /**
     * Round number to preceding integer.
     *
     */
    public function floor(float|int|string $number) : string;

    /**
     * Returns the absolute value of the number.
     *
     */
    public function absolute(float|int|string $number) : string;

    /**
     * Round number, use rounding mode for tie-breaker.
     *
     */
    public function round(float|int|string $number, int $roundingMode) : string;

    /**
     * Share amount among ratio / total portions.
     *
     */
    public function share(float|int|string $amount, float|int $ratio, float|int $total) : string;
}
