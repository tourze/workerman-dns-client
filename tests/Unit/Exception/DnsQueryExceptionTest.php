<?php

namespace Tourze\Workerman\DnsClient\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\DnsClient\Exception\DnsQueryException;

class DnsQueryExceptionTest extends TestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $exception = new DnsQueryException('Query failed');
        
        $this->assertInstanceOf(DnsQueryException::class, $exception);
        $this->assertEquals('Query failed', $exception->getMessage());
    }

    public function testExceptionCanBeCreatedWithCode(): void
    {
        $exception = new DnsQueryException('Query failed', 789);
        
        $this->assertEquals('Query failed', $exception->getMessage());
        $this->assertEquals(789, $exception->getCode());
    }

    public function testExceptionCanBeCreatedWithPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new DnsQueryException('Query failed', 0, $previous);
        
        $this->assertEquals('Query failed', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}