<?php declare(strict_types = 1);

namespace kristijorgji\Geo;

use kristijorgji\Geo\Exceptions\GeoLocationServiceException;

interface GeoLocationServiceInterface
{
    /**
     * @throws GeoLocationServiceException
     */
    public function detailsFromIp(string $ip): GeoLocationDetails;
}
