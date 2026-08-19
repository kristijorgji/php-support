<?php declare(strict_types = 1);

namespace kristijorgji\Iso;

final readonly class CountryInfo
{
    public function __construct(
        private Countries $country,
        private string $name,
        private string $flagEmoji,
    ) {
    }

    public function getCountry(): Countries
    {
        return $this->country;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFlagEmoji(): string
    {
        return $this->flagEmoji;
    }
}
