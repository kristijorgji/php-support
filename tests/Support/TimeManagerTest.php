<?php declare(strict_types = 1);

namespace kristijorgji\Tests\Support;

use kristijorgji\Support\TimeManager;
use PHPUnit\Framework\TestCase;
use function date;

final class TimeManagerTest extends TestCase
{
    public function test_unix_mysql_roundtrip(): void
    {
        $tm = new TimeManager;
        $unix = 1_704_067_200;
        $mysql = $tm->unixToMySqlTimestamp($unix);
        $this->assertSame(date('Y-m-d H:i:s', $unix), $mysql);
        $this->assertSame($unix, $tm->mysqlTimestampToUnixTimestamp($mysql));
    }

    public function test_now_helpers_return_values(): void
    {
        $tm = new TimeManager;
        $this->assertGreaterThan(0, $tm->nowAsUnixTimestamp());
        $this->assertGreaterThan(0, $tm->nowMicroAsFloat());
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $tm->nowAsMySqlTimestamp());
    }
}
