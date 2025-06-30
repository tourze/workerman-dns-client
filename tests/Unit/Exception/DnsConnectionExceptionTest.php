<?php

namespace Tourze\Workerman\DnsClient\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\DnsClient\Exception\DnsConnectionException;

class DnsConnectionExceptionTest extends TestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $exception = new DnsConnectionException('Connection failed');
        
        $this->assertInstanceOf(DnsConnectionException::class, $exception);
        $this->assertEquals('Connection failed', $exception->getMessage());
    }

    public function testExceptionCanBeCreatedWithCode(): void
    {
        $exception = new DnsConnectionException('Connection failed', 123);
        
        $this->assertEquals('Connection failed', $exception->getMessage());
        $this->assertEquals(123, $exception->getCode());
    }

    public function testExceptionCanBeCreatedWithPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new DnsConnectionException('Connection failed', 0, $previous);
        
        $this->assertEquals('Connection failed', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}