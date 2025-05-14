<?php

namespace Tourze\Workerman\DnsClient\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\DnsClient\Exception\DnsConnectionException;
use Tourze\Workerman\DnsClient\Exception\DnsParseException;
use Tourze\Workerman\DnsClient\Exception\DnsQueryException;

class ExceptionTest extends TestCase
{
    /**
     * 测试基础DNS查询异常
     */
    public function testDnsQueryException(): void
    {
        $message = '查询错误';
        $code = 123;
        $previous = new \Exception('前一个错误');
        
        $exception = new DnsQueryException($message, $code, $previous);
        
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
    
    /**
     * 测试DNS解析异常
     */
    public function testDnsParseException(): void
    {
        $message = '解析错误';
        $code = 456;
        $previous = new \Exception('前一个错误');
        
        $exception = new DnsParseException($message, $code, $previous);
        
        $this->assertInstanceOf(DnsQueryException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
    
    /**
     * 测试DNS连接异常
     */
    public function testDnsConnectionException(): void
    {
        $message = '连接错误';
        $code = 789;
        $previous = new \Exception('前一个错误');
        
        $exception = new DnsConnectionException($message, $code, $previous);
        
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