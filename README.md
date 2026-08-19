# kristijorgji/php-support

Shared PHP primitives:

- `kristijorgji\Money\*` — major-unit decimal money with bcmath (`Money`, `Number`, `Currency`, calculators)
- `kristijorgji\Iso\*` — ISO 3166-1 alpha-2 country enum (plus the
  commonly-needed non-ISO `XK` and `SS`), a nine-code product
  `Currencies` allowlist, and localized country name/flag lookup
- `kristijorgji\Net\IpUtils` — client IP extraction, private-IP and bogon checks
- `kristijorgji\Geo\*` — IP geolocation chain (`ChainGeoLocationService`) with header, MaxMind, ipinfo and ipstack resolvers
- `kristijorgji\Support\*` — `TimeManager`, `Environments`, `LocaleProviderInterface`

## Install

```bash
composer require kristijorgji/php-support
```

Local path (sibling checkout):

```json
"repositories": {
  "kj-php-support": {
    "type": "path",
    "url": "../php-support",
    "options": { "symlink": false }
  }
}
```

## Currency vs product allowlists

`kristijorgji\Money\Currency` is a generic code VO. The product
allowlist `kristijorgji\Iso\Currencies` is a nine-code allowlist for
products that sell in a fixed set of currencies:

```php
use kristijorgji\Iso\Currencies;
use kristijorgji\Money\Currency;
use kristijorgji\Money\Money;

new Money('10.00', Currency::from(Currencies::EUR));
```

Country defaults (e.g. falling back to Germany) stay in the consuming app — `Countries` has no `DEFAULT`.

## Geolocation

```php
use kristijorgji\Geo\ChainGeoLocationService;
use kristijorgji\Geo\Resolvers\HeaderCountryResolver;
use kristijorgji\Geo\Resolvers\IpInfoResolver;
use kristijorgji\Geo\Resolvers\IpStackResolver;
use kristijorgji\Geo\Resolvers\MaxMindDbResolver;

$service = new ChainGeoLocationService(
    resolvers: [$header, $maxmind, $ipInfo, $ipStack],
    logger: $psrLogger,
    countryInfo: $countryInfoRepository,
    localeProvider: $localeProvider,
    cache: $psr16Cache,
);
```

Bogon / private IPs are rejected locally before any resolver runs.
`BogonCannotBeResolvedException` aborts the chain; a single resolver
failure falls through to the next.

Optional packages (declared as `suggest`): `guzzlehttp/guzzle` for HTTP resolvers, `geoip2/geoip2` for `MaxMindDbResolver`.

Localized names live in `data/countries_info/{en,sq}.json`. Albanian names come from CLDR.

## License

MIT
