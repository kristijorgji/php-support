<?php declare(strict_types = 1);

namespace kristijorgji\Money;

final class Number
{
    const DECIMAL_SEPARATOR = '.';
    const DECIMAL_PRECISION = '14';

    private string $integerPart;

    private string $fractionalPart;

    /**
     * @var array<int, int>
     */
    private static array $numbers = [0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1, 9 => 1];

    public function __construct(string $integerPart, string $fractionalPart = '')
    {
        if ('' === $integerPart && '' === $fractionalPart) {
            throw new \InvalidArgumentException('Empty number is invalid');
        }

        $this->integerPart = $this->parseIntegerPart((string) $integerPart);
        $this->fractionalPart = $this->parseFractionalPart((string) $fractionalPart);
    }

    public function isDecimal() : bool
    {
        return $this->fractionalPart !== '';
    }

    public function isInteger() : bool
    {
        return $this->fractionalPart === '';
    }

    public function isHalf() : bool
    {
        return $this->fractionalPart === '5';
    }

    public function isCurrentEven() : bool
    {
        $lastIntegerPartNumber = (int) $this->integerPart[strlen($this->integerPart) - 1];
        return $lastIntegerPartNumber % 2 === 0;
    }

    public function isCloserToNext() : bool
    {
        if ($this->fractionalPart === '') {
            return false;
        }

        return (int) $this->fractionalPart[0] >= 5;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->fractionalPart === '') {
            return $this->integerPart;
        }
        return $this->integerPart . self::DECIMAL_SEPARATOR . $this->fractionalPart;
    }

    /**
     * @param $number
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $number) : Number
    {
        $number = self::normalizeDecimalSeparator($number);
        $decimalSeparatorPosition = strpos($number, self::DECIMAL_SEPARATOR);
        if ($decimalSeparatorPosition === false) {
            return new self(self::removeLeadingZeros($number), '');
        }

        return new self(
            self::removeLeadingZeros(substr($number, 0, $decimalSeparatorPosition)),
            rtrim(substr($number, $decimalSeparatorPosition + 1), '0')
        );
    }

    private static function removeLeadingZeros(string $v): string
    {
        $r = '';
        $continiousZero = false;
        $firstSign = false;

        $l = strlen($v);
        for ($i = 0; $i < $l; $i++) {
            $c = $v[$i];
            if ($c === '-') {
                if ($i !== 0) {
                    throw new \ValueError();
                }
                $firstSign = true;
                $r .= $c;
            } else if ($c === '0' && $i < $l - 1) {
                if ($i === 0 || ($firstSign && $i === 1) || $continiousZero) {
                    $continiousZero = true;
                } else if ($i === 0 && $c === '0' && $l === 1) {
                    return $c;
                } else if($firstSign && $i === 1 && $c === '0' && $l === 2) {
                    return $v[0].'0';
                } else {
                    $r .= $c;
                }

            } else {
                $continiousZero = false;
                $r .= $c;
            }
        }
        return $r;
    }

    /**
     * @return Number
     */
    public static function fromFloat(float $floatingPoint)
    {
        $format = '%.' . self::DECIMAL_PRECISION . 'F';
        return self::fromString(sprintf($format, $floatingPoint));
    }

    public function isNegative() : bool
    {
        return $this->integerPart[0] === '-';
    }

    public function getIntegerPart() : string
    {
        return $this->integerPart;
    }

    public function getFractionalPart() : string
    {
        return $this->fractionalPart;
    }

    public function getIntegerRoundingMultiplier() : string
    {
        if ($this->integerPart[0] === '-') {
            return '-1';
        }

        return '1';
    }

    private static function parseIntegerPart(string $number) : string
    {
        if ('' === $number || '0' === $number) {
            return '0';
        }

        if ('-' === $number) {
            return '-0';
        }

        $nonZero = false;

        for ($position = 0, $characters = strlen($number); $position < $characters; ++$position) {
            $digit = $number[$position];
            if (!isset(self::$numbers[$digit]) && !(0 === $position && '-' === $digit)) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid integer part %s. Invalid digit %2 found', $number, $digit)
                );
            }

            if (false === $nonZero && '0' === $digit) {
                throw new \InvalidArgumentException(
                    'Leading zeros are not allowed'
                );
            }
            $nonZero = true;
        }

        return $number;
    }

    private static function parseFractionalPart(string $number) : string
    {
        if ('' === $number) {
            return $number;
        }

        for ($position = 0, $characters = strlen($number); $position < $characters; ++$position) {
            $digit = $number[$position];
            if (!isset(self::$numbers[$digit])) {
                throw new \InvalidArgumentException(
                    'Invalid fractional part '.$number.'. Invalid digit '.$digit.' found'
                );
            }
        }
        return $number;
    }

    private static function normalizeDecimalSeparator(string $number) : string
    {
        return str_replace(',', self::DECIMAL_SEPARATOR, $number);
    }
}
