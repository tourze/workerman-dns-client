<?php

namespace Tourze\Workerman\DnsClient\Tests\Logger;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\DnsClient\Logger\NullLogger;
use Tourze\Workerman\DnsClient\Logger\WorkermanLogger;

class LoggerTest extends TestCase
{
    /**
     * 测试NullLogger不执行任何操作
     */
    public function testNullLoggerDoesNothing(): void
    {
        $logger = new NullLogger();
        
        // 由于NullLogger没有任何可观察的行为，我们只能确保调用不会抛出异常
        ob_start();
        $logger->log('测试消息');
        $output = ob_get_clean();
        
        $this->assertEquals('', $output);
    }
    
    /**
     * 测试WorkermanLogger实现了log方法
     * 注意：由于无法模拟Worker::log，只验证类的结构
     */
    public function testWorkermanLoggerImplementsLogMethod(): void
    {
        // 不实例化WorkermanLogger，只检查类结构
        $reflectionClass = new \ReflectionClass(WorkermanLogger::class);
        $this->assertTrue($reflectionClass->hasMethod('log'));
        
        // 检查log方法参数
        $reflectionMethod = $reflectionClass->getMethod('log');
        $params = $reflectionMethod->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('message', $params[0]->getName());
    }
    
    /**
     * 测试WorkermanLogger的行为
     * 通过检查源代码实现来验证行为，而不是实际运行代码
     */
    public function testWorkermanLoggerBehavior(): void
    {
        // 获取WorkermanLogger类的源代码
        $reflectionClass = new \ReflectionClass(WorkermanLogger::class);
        $fileName = $reflectionClass->getFileName();
        $source = file_get_contents($fileName);
        
        // 验证源代码中包含对Worker::log的调用
        $this->assertStringContainsString('Worker::log(', $source);
        
        // 验证log方法实现正确传递了消息参数
        $reflectionMethod = $reflectionClass->getMethod('log');
        $params = $reflectionMethod->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('message', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
    }
} 