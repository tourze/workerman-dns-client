<?php

namespace Tourze\Workerman\DnsClient\Tests\Unit\Logger;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Logger\NullLogger;

class NullLoggerTest extends TestCase
{
    public function testImplementsLoggerInterface(): void
    {
        $logger = new NullLogger();
        
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testLogDoesNotThrowException(): void
    {
        $logger = new NullLogger();
        
        // 不应该抛出异常
        $logger->log('Test message');
        $logger->log('');
        $logger->log('Another test message with special chars: 中文测试');
        
        $this->assertTrue(true); // 如果到达这里，说明没有抛出异常
    }

    public function testLogCanBeCalledMultipleTimes(): void
    {
        $logger = new NullLogger();
        
        for ($i = 0; $i < 100; $i++) {
            $logger->log("Message $i");
        }
        
        $this->assertTrue(true); // 如果到达这里，说明没有抛出异常
    }
}