<?php

namespace Tourze\Workerman\DnsClient\Logger;

/**
 * 空日志记录器实现
 */
class NullLogger implements LoggerInterface
{
    public function log(string $message): void
    {
        // 不做任何事情
    }
}
