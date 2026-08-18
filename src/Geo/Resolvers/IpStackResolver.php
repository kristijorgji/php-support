<?php declare(strict_types = 1);

namespace kristijorgji\Geo\Resolvers;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use kristijorgji\Geo\Exceptions\GeoLocationResolverException;
use kristijorgji\Geo\GeoLocationDetails;
use kristijorgji\Iso\Countries;
use function json_decode;
use function json_encode;
use function sprintf;

final class IpStackResolver implements GeoLocationResolverInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly string $accessKey,
        private readonly float $timeoutSeconds = 1.0,
    ) {
    }

    public function name(): string
    {
        return 'ipStack';
    }

    public function detailsFromIp(string $ip): GeoLocationDetails
    {
        $response = $this->client->request(
            'GET',
            sprintf('http://api.ipstack.com/%s?access_key=%s', $ip, $this->accessKey),
            [RequestOptions::TIMEOUT => $this->timeoutSeconds],
        );
        $body = (string) $response->getBody();
        $parsedBody = json_decode($body);

        if (($parsedBody->success ?? true) === false) {
            throw new GeoLocationResolverException(
                sprintf('Could not resolve countryCode from this parsedBody %s', json_encode($parsedBody)),
            );
        }

        $code = $parsedBody->country_code ?? null;
        $country = is_string($code) ? Countries::tryFrom($code) : null;
        if ($country === null) {
            throw new GeoLocationResolverException(
                sprintf('Could not resolve countryCode from this parsedBody %s', json_encode($parsedBody)),
            );
        }

        return new GeoLocationDetails(
            countryCode: $country,
            countryName: $parsedBody->country_name ?? null,
            city: $parsedBody->city ?? null,
            countryFlag: $parsedBody->location->country_flag ?? null,
            countryFlagEmoji: $parsedBody->location->country_flag_emoji ?? null,
            rawResponse: $body,
        );
    }
}
