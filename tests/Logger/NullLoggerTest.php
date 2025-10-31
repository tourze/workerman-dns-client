<?php

namespace Tourze\Workerman\DnsClient\Tests\Logger;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Logger\NullLogger;

/**
 * @internal
 */
#[CoversClass(NullLogger::class)]
final class NullLoggerTest extends TestCase
{
    public function testImplementsLoggerInterface(): void
    {
        $logger = new NullLogger();

        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testLogDoesNotThrowException(): void
    {
        $logger = new NullLogger();

        // 验证调用不产生任何输出或异常
        ob_start();
        $logger->log('Test message');
        $logger->log('');
        $logger->log('Another test message with special chars: 中文测试');
        $output = ob_get_clean();

        $this->assertEquals('', $output, 'NullLogger should not produce any output');
    }

    public function testLogCanBeCalledMultipleTimes(): void
    {
        $logger = new NullLogger();

        // 验证多次调用不产生任何输出
        ob_start();
        for ($i = 0; $i < 100; ++$i) {
            $logger->log("Message {$i}");
        }
        $output = ob_get_clean();

        $this->assertEquals('', $output, 'Multiple calls to NullLogger should not produce any output');
    }

    public function testLog(): void
    {
        $logger = new NullLogger();

        // 验证log方法存在且可调用
        $reflectionClass = new \ReflectionClass($logger);
        $this->assertTrue($reflectionClass->hasMethod('log'));

        // 验证方法签名正确
        $logMethod = $reflectionClass->getMethod('log');
        $this->assertTrue($logMethod->isPublic());

        $params = $logMethod->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('message', $params[0]->getName());

        // 验证参数类型
        $type = $params[0]->getType();
        if ($type instanceof \ReflectionNamedType) {
            $this->assertEquals('string', $type->getName());
        }

        // 验证返回类型为void
        $returnType = $logMethod->getReturnType();
        if ($returnType instanceof \ReflectionNamedType) {
            $this->assertEquals('void', $returnType->getName());
        }

        // 验证调用不产生任何输出
        ob_start();
        $logger->log('Test message');
        $output = ob_get_clean();
        $this->assertEquals('', $output);
    }
}
