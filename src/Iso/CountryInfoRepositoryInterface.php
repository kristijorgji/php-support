<?php declare(strict_types = 1);

namespace kristijorgji\Iso;

interface CountryInfoRepositoryInterface
{
    public function find(Countries $country, string $locale): ?CountryInfo;

    /**
     * @return array<string, CountryInfo> keyed by ISO alpha-2
     */
    public function all(string $locale): array;
}
