<?php declare(strict_types = 1);

namespace kristijorgji\Support;

interface LocaleProviderInterface
{
    public function locale(): string;
}
