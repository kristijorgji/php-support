<?php declare(strict_types = 1);

namespace kristijorgji\Geo\Resolvers;

use kristijorgji\Geo\Exceptions\GeoLocationResolverException;
use kristijorgji\Geo\GeoLocationDetails;

interface GeoLocationResolverInterface
{
    public function name(): string;

    /**
     * @throws GeoLocationResolverException
     */
    public function detailsFromIp(string $ip): GeoLocationDetails;
}
