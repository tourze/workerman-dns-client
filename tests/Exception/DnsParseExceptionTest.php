<?php

namespace Tourze\Workerman\DnsClient\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\Workerman\DnsClient\Exception\DnsParseException;

/**
 * @internal
 */
#[CoversClass(DnsParseException::class)]
final class DnsParseExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $exception = new DnsParseException('Parse failed');

        $this->assertInstanceOf(DnsParseException::class, $exception);
        $this->assertEquals('Parse failed', $exception->getMessage());
    }

    public function testExceptionCanBeCreatedWithCode(): void
    {
        $exception = new DnsParseException('Parse failed', 456);

        $this->assertEquals('Parse failed', $exception->getMessage());
        $this->assertEquals(456, $exception->getCode());
    }

    public function testExceptionCanBeCreatedWithPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new DnsParseException('Parse failed', 0, $previous);

        $this->assertEquals('Parse failed', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
