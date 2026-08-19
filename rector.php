<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        \Rector\CodingStyle\Rector\ArrowFunction\ArrowFunctionDelegatingCallToFirstClassCallableRector::class,
        \Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector::class,
        \Rector\Php53\Rector\Ternary\TernaryToElvisRector::class,
        \Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector::class,
        \Rector\PHPUnit\CodeQuality\Rector\MethodCall\ScalarArgumentToExpectedParamTypeRector::class,
    ])
    ->withPhpSets(php85: true)
    ->withPhpVersion(PhpVersion::PHP_85)
    ->withPreparedSets(deadCode: true)
    ->withSets([
        \Rector\PHPUnit\Set\PHPUnitSetList::COMPOSER_BASED,
        \Rector\PHPUnit\Set\PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        \Rector\PHPUnit\Set\PHPUnitSetList::PHPUNIT_NARROW_ASSERTS,
    ]);
