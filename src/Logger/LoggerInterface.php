<?php

namespace Tourze\Workerman\DnsClient\Logger;

/**
 * 日志记录器接口
 */
interface LoggerInterface
{
    /**
     * 记录日志
     *
     * @param string $message 日志消息
     * @return void
     */
    public function log(string $message): void;
}
