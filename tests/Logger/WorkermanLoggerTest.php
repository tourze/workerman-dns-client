<?php

namespace Tourze\Workerman\DnsClient\Tests\Logger;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Logger\WorkermanLogger;

/**
 * @internal
 */
#[CoversClass(WorkermanLogger::class)]
final class WorkermanLoggerTest extends TestCase
{
    public function testImplementsLoggerInterface(): void
    {
        $logger = new WorkermanLogger();

        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testLog(): void
    {
        $logger = new WorkermanLogger();

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
    }
}
