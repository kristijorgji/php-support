<?php declare(strict_types = 1);

namespace kristijorgji\Enum;

use ReflectionClass;

abstract class Enum implements \Stringable
{
    /**
     * @var array<class-string, true>
     */
    private static $initiated = [];

    /**
     * @var array<class-string, array<string, string|int>>
     */
    private static $options = [];

    final public function __construct(private string|int $value)
    {
        self::initIfNot();

        $validValue = false;
        foreach (self::$options[static::class] as $allowedValue) {
            if ($value === $allowedValue) {
                $validValue = true;
                break;
            }
        }

        if (!$validValue) {
            throw new \InvalidArgumentException(
                sprintf('The provided value "%s" is invalid for enum class %s', $value, static::class)
            );
        }
    }

    final public function __toString(): string
    {
        return (string) $this->value;
    }

    /**
     * @throws \Exception
     */
    final public function getSelfKey(): string
    {
        foreach (self::$options[static::class] as $key => $value) {
            if ($this->value === $value) {
                return $key;
            }
        }

        throw new \Exception(sprintf('Value %s has no corresponding key', $this->value));
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function hasValue(string|int $needleValue): bool
    {
        self::initIfNot();
        foreach (self::$options[static::class] as $value) {
            if ($needleValue === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function getKey(string|int $needleValue): string
    {
        self::initIfNot();
        foreach (self::$options[static::class] as $key => $value) {
            if ($needleValue === $value) {
                return $key;
            }
        }

        throw new \InvalidArgumentException(sprintf('Value %s has no corresponding name', $needleValue));
    }

    /**
     * @return array<string, string|int>
     */
    public static function toArray(): array
    {
        self::initIfNot();
        return self::$options[static::class];
    }

    /**
     * @return (string|int)[]
     */
    public static function getValues(): array
    {
        self::initIfNot();
        return array_values(self::$options[static::class]);
    }

    private static function initIfNot(): void
    {
        if (isset(self::$initiated[static::class])) {
            return;
        }

        $reflector = new ReflectionClass(static::class);
        self::$options[static::class] = $reflector->getConstants();
        self::$initiated[static::class] = true;
    }
}
