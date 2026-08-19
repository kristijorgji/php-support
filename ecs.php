<?php declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

$base = require __DIR__ . '/vendor/kristijorgji/php-coding-standard/ecs/base.php';
$php85 = require __DIR__ . '/vendor/kristijorgji/php-coding-standard/ecs/php85.php';

$config = ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRules(array_merge($base['rules'], $php85['rules']))
    ->withSkip(array_merge($base['skip'], $php85['skip']))
    ->withParallel()
    ->withCache(__DIR__ . '/.ecs_cache');

foreach (array_merge($base['rulesWithConfiguration'], $php85['rulesWithConfiguration']) as $ruleClass => $configuration) {
    $config = $config->withConfiguredRule($ruleClass, $configuration);
}

return $config;
