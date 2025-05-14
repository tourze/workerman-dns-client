<?php

namespace Tourze\Workerman\DnsClient\Timer;

/**
 * 定时器接口
 */
interface TimerInterface
{
    /**
     * 添加一个定时器
     *
     * @param float $interval 时间间隔
     * @param callable $callback 回调函数
     * @param array $args 回调函数参数
     * @param bool $persistent 是否是持久定时器
     * @return int 定时器ID
     */
    public function add(float $interval, callable $callback, array $args = [], bool $persistent = true): int;

    /**
     * 删除一个定时器
     *
     * @param int $timerId 定时器ID
     * @return bool 是否成功删除
     */
    public function del(int $timerId): bool;
}
