<?php declare(strict_types = 1);

namespace kristijorgji\Geo;

use kristijorgji\Geo\Exceptions\BogonCannotBeResolvedException;
use kristijorgji\Geo\Exceptions\GeoLocationResolverException;
use kristijorgji\Geo\Exceptions\GeoLocationServiceException;
use kristijorgji\Geo\Exceptions\PrivateIpCannotBeResolvedException;
use kristijorgji\Geo\Resolvers\GeoLocationResolverInterface;
use kristijorgji\Iso\CountryInfoRepositoryInterface;
use kristijorgji\Net\IpUtils;
use kristijorgji\Support\LocaleProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;
use function sprintf;

final class ChainGeoLocationService implements GeoLocationServiceInterface
{
    /**
     * @param list<GeoLocationResolverInterface> $resolvers ordered, first success wins
     */
    public function __construct(
        private readonly array $resolvers,
        private readonly LoggerInterface $logger,
        private readonly ?CountryInfoRepositoryInterface $countryInfo = null,
        private readonly ?LocaleProviderInterface $localeProvider = null,
        private readonly ?CacheInterface $cache = null,
        private readonly int $cacheTtlSeconds = 86400,
    ) {
    }

    public function detailsFromIp(string $ip): GeoLocationDetails
    {
        if (IpUtils::isPrivateIp($ip)) {
            $this->logger->debug(sprintf('Skipping geolocation for private IP %s', $ip));
            throw new PrivateIpCannotBeResolvedException(
                sprintf('GeoLocation details cannot be retrieved from private IP %s. Private IPs are not resolvable.', $ip),
            );
        }

        if (IpUtils::isBogon($ip)) {
            $this->logger->debug(sprintf('Skipping geolocation for bogon IP %s', $ip));
            throw new BogonCannotBeResolvedException(
                sprintf('%s is a bogon address and cannot be resolved.', $ip),
            );
        }

        $cacheKey = sprintf('geoLocation.%s', $ip);
        if ($this->cache !== null) {
            $cached = $this->cache->get($cacheKey);
            if (is_string($cached)) {
                return GeoLocationDetails::fromJson($cached);
            }
        }

        $result = null;
        foreach ($this->resolvers as $resolver) {
            try {
                $result = $resolver->detailsFromIp($ip);
                break;
            } catch (BogonCannotBeResolvedException $e) {
                $this->logger->info($e->getMessage());
                throw $e;
            } catch (GeoLocationResolverException $e) {
                $this->logger->warning(
                    sprintf('Failed to geolocate ip %s with resolver %s: %s', $ip, $resolver->name(), $e->getMessage()),
                    ['exception' => $e],
                );
            } catch (Throwable $e) {
                $this->logger->warning(
                    sprintf('Failed to geolocate ip %s with resolver %s: %s', $ip, $resolver->name(), $e->getMessage()),
                    ['exception' => $e],
                );
            }
        }

        if ($result === null) {
            throw new GeoLocationServiceException(sprintf('Could not resolve for ip %s', $ip));
        }

        if ($result->getCountryName() === null && $this->countryInfo !== null) {
            $locale = $this->localeProvider?->locale() ?? 'en';
            $info = $this->countryInfo->find($result->getCountryCode(), $locale);
            if ($info === null) {
                $this->logger->warning(
                    sprintf('Cannot find information about country %s', $result->getCountryCode()->value),
                );
            }

            $result = (new GeoLocationDetailsBuilder($result))
                ->setCountryName($info?->getName() ?? '')
                ->setCountryFlagEmoji($info?->getFlagEmoji() ?? '')
                ->build();
        }

        if ($this->cache !== null) {
            $this->cache->set($cacheKey, json_encode($result), $this->cacheTtlSeconds);
        }

        return $result;
    }
}
