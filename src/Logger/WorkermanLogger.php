<?php

namespace Tourze\Workerman\DnsClient\Logger;

use Workerman\Worker;

/**
 * 基于Workerman的日志记录器实现
 */
class WorkermanLogger implements LoggerInterface
{
    public function log(string $message): void
    {
        Worker::log($message);
    }
}
