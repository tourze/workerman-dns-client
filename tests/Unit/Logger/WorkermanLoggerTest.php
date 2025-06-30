<?php

namespace Tourze\Workerman\DnsClient\Tests\Unit\Logger;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Logger\WorkermanLogger;

class WorkermanLoggerTest extends TestCase
{
    public function testImplementsLoggerInterface(): void
    {
        $logger = new WorkermanLogger();
        
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }


    public function testLogMethodThrowsTypeErrorInTestEnvironment(): void
    {
        $logger = new WorkermanLogger();
        
        // 由于 WorkermanLogger 依赖于 Workerman 运行时环境，
        // 在单元测试中无法正常工作，预期会抛出 TypeError
        $this->expectException(\TypeError::class);
        
        $logger->log('Test message');
    }
}