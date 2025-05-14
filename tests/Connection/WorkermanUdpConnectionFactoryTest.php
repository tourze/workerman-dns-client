<?php

namespace Tourze\Workerman\DnsClient\Tests\Connection;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\DnsClient\Connection\WorkermanUdpConnectionFactory;
use Workerman\Connection\AsyncUdpConnection;

class WorkermanUdpConnectionFactoryTest extends TestCase
{
    public function testCreateConnection(): void
    {
        $factory = new WorkermanUdpConnectionFactory();
        $address = '8.8.8.8';
        $port = 53;
        
        $connection = $factory->createConnection($address, $port);
        
        $this->assertInstanceOf(AsyncUdpConnection::class, $connection);
        // 注意: getRemoteAddress实际返回的是不带协议前缀的地址
        $this->assertEquals("{$address}:{$port}", $connection->getRemoteAddress());
    }
    
    public function testCreateConnectionWithDifferentAddressAndPort(): void
    {
        $factory = new WorkermanUdpConnectionFactory();
        $address = '1.1.1.1';
        $port = 5353;
        
        $connection = $factory->createConnection($address, $port);
        
        $this->assertInstanceOf(AsyncUdpConnection::class, $connection);
        $this->assertEquals("{$address}:{$port}", $connection->getRemoteAddress());
    }
    
    public function testCreateConnectionWithNonDefaultPort(): void
    {
        $factory = new WorkermanUdpConnectionFactory();
        $address = '8.8.8.8';
        $port = 5353; // 非默认DNS端口
        
        $connection = $factory->createConnection($address, $port);
        
        $this->assertInstanceOf(AsyncUdpConnection::class, $connection);
        $this->assertEquals("{$address}:{$port}", $connection->getRemoteAddress());
    }
} 