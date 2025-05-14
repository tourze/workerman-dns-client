<?php

namespace Tourze\Workerman\DnsClient\Timer;

use Workerman\Timer as WorkermanTimerLib;

/**
 * Workerman定时器实现
 */
class WorkermanTimer implements TimerInterface
{
    public function add(float $interval, callable $callback, array $args = [], bool $persistent = true): int
    {
        return WorkermanTimerLib::add($interval, $callback, $args, $persistent);
    }

    public function del(int $timerId): bool
    {
        return WorkermanTimerLib::del($timerId);
    }
}
