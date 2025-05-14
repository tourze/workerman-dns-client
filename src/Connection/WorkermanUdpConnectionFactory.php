<?php

namespace Tourze\Workerman\DnsClient\Connection;

use Workerman\Connection\AsyncUdpConnection;

/**
 * Workerman UDP连接工厂实现
 */
class WorkermanUdpConnectionFactory implements UdpConnectionFactoryInterface
{
    public function createConnection(string $address, int $port): AsyncUdpConnection
    {
        return new AsyncUdpConnection("udp://{$address}:{$port}");
    }
}
