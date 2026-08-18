<?php declare(strict_types = 1);

namespace kristijorgji\Geo\Resolvers;

use kristijorgji\Geo\Contracts\RequestContextInterface;
use kristijorgji\Geo\Exceptions\GeoLocationResolverException;
use kristijorgji\Geo\GeoLocationDetails;
use kristijorgji\Iso\Countries;
use function sprintf;
use function strtoupper;

final class HeaderCountryResolver implements GeoLocationResolverInterface
{
    public function __construct(
        private readonly RequestContextInterface $context,
        private readonly bool $trusted = false,
    ) {
    }

    public function name(): string
    {
        return 'header';
    }

    public function detailsFromIp(string $ip): GeoLocationDetails
    {
        if (!$this->trusted) {
            throw new GeoLocationResolverException('Country header is not trusted');
        }

        $clientIp = $this->context->clientIp();
        if ($clientIp === null || $clientIp !== $ip) {
            throw new GeoLocationResolverException(
                sprintf('Country header applies only to the current client IP, not %s', $ip),
            );
        }

        $header = $this->context->countryHeader();
        if ($header === null || $header === '') {
            throw new GeoLocationResolverException('Country header is empty');
        }

        $country = Countries::tryFrom(strtoupper($header));
        if ($country === null) {
            throw new GeoLocationResolverException(
                sprintf('Country header value %s is not a known country', $header),
            );
        }

        return new GeoLocationDetails(
            countryCode: $country,
            countryName: null,
            city: null,
            countryFlag: null,
            countryFlagEmoji: null,
            rawResponse: $header,
        );
    }
}
