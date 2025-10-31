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
final class DnsQueryExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeCreated(): void
    {
        // 使用具体子类测试抽象基类功能
        $exception = new DnsConnectionException('Query failed');

        $this->assertInstanceOf(DnsQueryException::class, $exception);
        $this->assertEquals('Query failed', $exception->getMessage());
    }

    public function testExceptionCanBeCreatedWithCode(): void
    {
        // 使用具体子类测试抽象基类功能
        $exception = new DnsParseException('Query failed', 789);

        $this->assertEquals('Query failed', $exception->getMessage());
        $this->assertEquals(789, $exception->getCode());
    }

    public function testExceptionCanBeCreatedWithPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        // 使用具体子类测试抽象基类功能
        $exception = new DnsConnectionException('Query failed', 0, $previous);

        $this->assertEquals('Query failed', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
