<?php declare(strict_types = 1);

namespace kristijorgji\Geo\Resolvers;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use kristijorgji\Geo\Exceptions\BogonCannotBeResolvedException;
use kristijorgji\Geo\Exceptions\GeoLocationResolverException;
use kristijorgji\Geo\GeoLocationDetails;
use kristijorgji\Iso\Countries;
use function json_decode;
use function json_encode;
use function property_exists;
use function sprintf;

final class IpInfoResolver implements GeoLocationResolverInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly string $token,
        private readonly float $timeoutSeconds = 1.0,
    ) {
    }

    public function name(): string
    {
        return 'ipInfo';
    }

    public function detailsFromIp(string $ip): GeoLocationDetails
    {
        $response = $this->client->request(
            'GET',
            sprintf('https://ipinfo.io/%s?token=%s', $ip, $this->token),
            [RequestOptions::TIMEOUT => $this->timeoutSeconds],
        );
        $body = (string) $response->getBody();
        $parsedBody = json_decode($body);

        if (property_exists($parsedBody, 'bogon') && $parsedBody->bogon === true) {
            throw new BogonCannotBeResolvedException(
                sprintf('%s is a bogon address and cannot be resolved. https://ipinfo.io/bogon', $ip),
            );
        }

        if (!property_exists($parsedBody, 'country')) {
            throw new GeoLocationResolverException(
                sprintf('Could not resolve countryCode from this parsedBody %s', json_encode($parsedBody)),
            );
        }

        $country = Countries::tryFrom((string) $parsedBody->country);
        if ($country === null) {
            throw new GeoLocationResolverException(
                sprintf('Could not resolve countryCode from this parsedBody %s', json_encode($parsedBody)),
            );
        }

        return new GeoLocationDetails(
            countryCode: $country,
            countryName: null,
            city: $parsedBody->city ?? null,
            countryFlag: null,
            countryFlagEmoji: null,
            rawResponse: $body,
        );
    }
}
