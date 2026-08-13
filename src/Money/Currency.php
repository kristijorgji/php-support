<?php declare(strict_types = 1);

namespace kristijorgji\Money;

/**
 * ISO-like currency code value object (not a product allowlist).
 */
final class Currency implements \Stringable
{
    public function __construct(private string $code)
    {
        $normalized = strtoupper(trim($code));
        if ($normalized === '') {
            throw new \InvalidArgumentException('Currency code must not be empty');
        }

        $this->code = $normalized;
    }

    public static function from(string|\Stringable $code): self
    {
        return new self((string) $code);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
