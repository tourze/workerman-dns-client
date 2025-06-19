<?php

namespace Tourze\Workerman\DnsClient\Tests\Protocol;

use PHPUnit\Framework\TestCase;
use React\Dns\Model\Message;
use React\Dns\Model\Record;
use Tourze\Workerman\DnsClient\Exception\DnsParseException;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Protocol\ReactDnsProtocolHandler;

class ReactDnsProtocolHandlerTest extends TestCase
{
    private ReactDnsProtocolHandler $handler;
    private LoggerInterface $logger;
    
    protected function setUp(): void
    {
        // 创建一个模拟的日志记录器
        $this->logger = $this->createMock(LoggerInterface::class);
        
        // 注入模拟的日志记录器
        $this->handler = new ReactDnsProtocolHandler($this->logger);
    }
    
    public function testCreateQueryPacket(): void
    {
        $domain = 'example.com';
        $type = Message::TYPE_A;
        
        $packet = $this->handler->createQueryPacket($domain, $type);
        $this->assertNotEmpty($packet);
    }
    
    public function testParseResponseThrowsExceptionForInvalidData(): void
    {
        $this->expectException(DnsParseException::class);
        
        $this->handler->parseResponse('invalid-data');
    }
    
    public function testExtractIPFromAnswers_ReturnsNullForEmptyAnswers(): void
    {
        // 配置模拟的日志记录器
        $this->logger->expects($this->once())
            ->method('log')
            ->with($this->stringContains('DNS解析为空'));
            
        $result = $this->handler->extractIPFromAnswers([]);
        
        $this->assertNull($result);
    }
    
    public function testExtractIPFromAnswers_ReturnsIPWhenValid(): void
    {
        $answers = [
            $this->createAnswerRecord('example.com', '192.168.1.1', 600)
        ];
        
        $result = $this->handler->extractIPFromAnswers($answers);
        
        $this->assertEquals('192.168.1.1', $result);
    }
    
    public function testExtractIPFromAnswers_SkipsInvalidIPs(): void
    {
        $answers = [
            $this->createAnswerRecord('example.com', 'not-an-ip', 600),
            $this->createAnswerRecord('example.com', '192.168.1.1', 600)
        ];
        
        $result = $this->handler->extractIPFromAnswers($answers);
        
        $this->assertEquals('192.168.1.1', $result);
    }
    
    public function testExtractIPFromAnswers_ReturnsNullWhenNoValidIPs(): void
    {
        // 配置模拟的日志记录器
        $this->logger->expects($this->once())
            ->method('log')
            ->with($this->stringContains('DNS解析找不到合法IP'));
            
        $answers = [
            $this->createAnswerRecord('example.com', 'not-an-ip', 600),
            $this->createAnswerRecord('example.com', 'also-not-an-ip', 600)
        ];
        
        $result = $this->handler->extractIPFromAnswers($answers);
        
        $this->assertNull($result);
    }
    
    public function testGetTtlFromAnswers_UsesTtlFromValidIPRecord(): void
    {
        $answers = [
            $this->createAnswerRecord('example.com', 'not-an-ip', 100),
            $this->createAnswerRecord('example.com', '192.168.1.1', 300)
        ];
        
        $result = $this->handler->getTtlFromAnswers($answers);
        
        $this->assertEquals(300, $result);
    }
    
    public function testGetTtlFromAnswers_UsesDefaultWhenNoValidIPFound(): void
    {
        $answers = [
            $this->createAnswerRecord('example.com', 'not-an-ip', 100)
        ];
        
        $result = $this->handler->getTtlFromAnswers($answers);
        
        $this->assertEquals(600, $result); // Default TTL value
    }
    
    /**
     * 创建一个测试用的DNS回答记录
     */
    private function createAnswerRecord(string $name, string $data, int $ttl): Record
    {
        return new Record($name, Message::TYPE_A, Message::CLASS_IN, $ttl, $data);
    }
} 