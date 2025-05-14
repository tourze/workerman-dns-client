<?php

namespace Tourze\Workerman\DnsClient\Connection;

use Workerman\Connection\AsyncUdpConnection;

/**
 * UDP连接工厂接口
 */
interface UdpConnectionFactoryInterface
{
    /**
     * 创建一个UDP连接
     *
     * @param string $address UDP服务器地址
     * @param int $port UDP服务器端口
     * @return AsyncUdpConnection
     */
    public function createConnection(string $address, int $port): AsyncUdpConnection;
}
