# kristijorgji/php-support

Shared PHP primitives used by Single apps (and others):

- `kristijorgji\Money\*` — major-unit decimal money with bcmath (`Money`, `Number`, `Currency`, calculators)

## Install

```bash
composer require kristijorgji/php-support
```

Local path (sibling checkout under `s/`):

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

`Currency` is a generic code VO. Product lists like `App\Constants\Currencies` stay in the app:

```php
use kristijorgji\Money\Currency;
use kristijorgji\Money\Money;

new Money('10.00', new Currencies(Currencies::EUR));
// or: Currency::from($currencies) / Currency::from('EUR')
```

## License

MIT
