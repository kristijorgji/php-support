<?php declare(strict_types = 1);

namespace kristijorgji\Geo\Contracts;

interface RequestContextInterface
{
    public function clientIp(): ?string;

    public function countryHeader(): ?string;
}
