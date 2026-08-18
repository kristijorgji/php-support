<?php declare(strict_types = 1);

namespace kristijorgji\Support;

use function date;
use function microtime;
use function strtotime;
use function time;

class TimeManager
{
    public function nowAsUnixTimestamp(): int
    {
        return time();
    }

    public function nowMicroAsFloat(): float
    {
        return microtime(true);
    }

    public function nowAsMySqlTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }

    public function unixToMySqlTimestamp(int $unixTimestamp): string
    {
        return date('Y-m-d H:i:s', $unixTimestamp);
    }

    public function mysqlTimestampToUnixTimestamp(string $mysqlTimestamp): int
    {
        return (int) strtotime($mysqlTimestamp);
    }
}
