<?php declare(strict_types = 1);

namespace kristijorgji\Geo\Resolvers;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use kristijorgji\Geo\Exceptions\GeoLocationResolverException;
use kristijorgji\Geo\GeoLocationDetails;
use kristijorgji\Iso\Countries;
use MaxMind\Db\Reader\InvalidDatabaseException;
use function is_file;
use function sprintf;

final class MaxMindDbResolver implements GeoLocationResolverInterface
{
    private ?Reader $reader = null;

    public function __construct(
        private readonly string $databasePath,
        private readonly string $locale = 'en',
    ) {
    }

    public function name(): string
    {
        return 'maxmind';
    }

    public function detailsFromIp(string $ip): GeoLocationDetails
    {
        if (!is_file($this->databasePath)) {
            throw new GeoLocationResolverException(
                sprintf('MaxMind database not found at %s', $this->databasePath),
            );
        }

        try {
            $record = $this->reader()->city($ip);
        } catch (AddressNotFoundException $e) {
            throw new GeoLocationResolverException(
                sprintf('MaxMind has no record for %s', $ip),
                0,
                $e,
            );
        } catch (InvalidDatabaseException $e) {
            throw new GeoLocationResolverException(
                sprintf('MaxMind database at %s is invalid', $this->databasePath),
                0,
                $e,
            );
        }

        $code = $record->country->isoCode;
        $country = is_string($code) ? Countries::tryFrom($code) : null;
        if ($country === null) {
            throw new GeoLocationResolverException(
                sprintf('MaxMind returned unknown country code %s for %s', (string) $code, $ip),
            );
        }

        $city = $record->city->names[$this->locale] ?? $record->city->name;

        return new GeoLocationDetails(
            countryCode: $country,
            countryName: $record->country->name,
            city: $city,
            countryFlag: null,
            countryFlagEmoji: null,
            rawResponse: null,
        );
    }

    private function reader(): Reader
    {
        return $this->reader ??= new Reader($this->databasePath);
    }
}
