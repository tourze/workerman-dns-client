<?php

namespace Tourze\Workerman\DnsClient\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\Workerman\DnsClient\Exception\DnsConnectionException;
use Tourze\Workerman\DnsClient\Exception\DnsParseException;
use Tourze\Workerman\DnsClient\Exception\DnsQueryException;

/**
 * @internal
 */
#[CoversClass(DnsQueryException::class)]
final class ExceptionTest extends AbstractExceptionTestCase
{
    /**
     * 测试基础DNS查询异常
     */
    public function testDnsQueryException(): void
    {
        $message = '查询错误';
        $code = 123;
        $previous = new \Exception('前一个错误');

        // 使用具体子类测试抽象基类功能
        $exception = new DnsConnectionException($message, $code, $previous);

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(DnsQueryException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * 测试异常层次结构
     */
    public function testExceptionHierarchy(): void
    {
        $parseException = new DnsParseException();
        $connectionException = new DnsConnectionException();

        // 验证异常的继承关系
        $this->assertInstanceOf(DnsQueryException::class, $parseException);
        $this->assertInstanceOf(\Exception::class, $parseException);

        $this->assertInstanceOf(DnsQueryException::class, $connectionException);
        $this->assertInstanceOf(\Exception::class, $connectionException);
    }
}
