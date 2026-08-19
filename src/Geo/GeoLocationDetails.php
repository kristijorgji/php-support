<?php declare(strict_types = 1);

namespace kristijorgji\Geo;

use JsonSerializable;
use kristijorgji\Iso\Countries;
use function json_decode;

final readonly class GeoLocationDetails implements JsonSerializable
{
    public function __construct(
        private Countries $countryCode,
        private ?string $countryName,
        private ?string $city,
        private ?string $countryFlag,
        private ?string $countryFlagEmoji,
        private ?string $rawResponse,
    ) {
    }

    public function getCountryCode(): Countries
    {
        return $this->countryCode;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCountryFlag(): ?string
    {
        return $this->countryFlag;
    }

    public function getCountryFlagEmoji(): ?string
    {
        return $this->countryFlagEmoji;
    }

    public function getRawResponse(): ?string
    {
        return $this->rawResponse;
    }

    public static function fromJson(string $json): self
    {
        $parsed = json_decode($json);

        return new self(
            countryCode: Countries::from($parsed->countryCode),
            countryName: $parsed->countryName ?? null,
            city: $parsed->city ?? null,
            countryFlag: $parsed->countryFlag ?? null,
            countryFlagEmoji: $parsed->countryFlagEmoji ?? null,
            rawResponse: $parsed->response ?? $parsed->rawResponse ?? null,
        );
    }

    /**
     * @return array{
     *     countryCode: string,
     *     countryName: string|null,
     *     city: string|null,
     *     countryFlag: string|null,
     *     countryFlagEmoji: string|null
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'countryCode' => $this->countryCode->value,
            'countryName' => $this->countryName,
            'city' => $this->city,
            'countryFlag' => $this->countryFlag,
            'countryFlagEmoji' => $this->countryFlagEmoji,
        ];
    }
}
