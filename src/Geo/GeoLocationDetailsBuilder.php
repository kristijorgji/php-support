<?php declare(strict_types = 1);

namespace kristijorgji\Geo;

use kristijorgji\Iso\Countries;

final class GeoLocationDetailsBuilder
{
    private readonly Countries $countryCode;
    private ?string $countryName;
    private readonly ?string $city;
    private ?string $countryFlag;
    private ?string $countryFlagEmoji;
    private readonly ?string $rawResponse;

    public function __construct(GeoLocationDetails $geoLocationDetails)
    {
        $this->countryCode = $geoLocationDetails->getCountryCode();
        $this->countryName = $geoLocationDetails->getCountryName();
        $this->city = $geoLocationDetails->getCity();
        $this->countryFlag = $geoLocationDetails->getCountryFlag();
        $this->countryFlagEmoji = $geoLocationDetails->getCountryFlagEmoji();
        $this->rawResponse = $geoLocationDetails->getRawResponse();
    }

    public function setCountryName(?string $countryName): self
    {
        $this->countryName = $countryName;
        return $this;
    }

    public function setCountryFlag(?string $countryFlag): self
    {
        $this->countryFlag = $countryFlag;
        return $this;
    }

    public function setCountryFlagEmoji(?string $countryFlagEmoji): self
    {
        $this->countryFlagEmoji = $countryFlagEmoji;
        return $this;
    }

    public function build(): GeoLocationDetails
    {
        return new GeoLocationDetails(
            $this->countryCode,
            $this->countryName,
            $this->city,
            $this->countryFlag,
            $this->countryFlagEmoji,
            $this->rawResponse,
        );
    }
}
