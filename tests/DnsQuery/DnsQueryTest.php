<?php

namespace Tourze\Workerman\DnsClient\Tests\DnsQuery;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Dns\Model\Message;
use React\Dns\Model\Record;
use React\Dns\Query\Query;
use Tourze\Workerman\DnsClient\Cache\DnsCacheInterface;
use Tourze\Workerman\DnsClient\Connection\UdpConnectionFactoryInterface;
use Tourze\Workerman\DnsClient\DnsConfig;
use Tourze\Workerman\DnsClient\DnsQuery;
use Tourze\Workerman\DnsClient\Exception\DnsParseException;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Protocol\DnsProtocolHandlerInterface;
use Tourze\Workerman\DnsClient\Timer\TimerInterface;
use Workerman\Connection\AsyncUdpConnection;

class DnsQueryTest extends TestCase
{
    private DnsQuery $dnsQuery;
    private DnsConfig $config;
    private DnsCacheInterface|MockObject $cache;
    private UdpConnectionFactoryInterface|MockObject $connectionFactory;
    private DnsProtocolHandlerInterface|MockObject $protocolHandler;
    private TimerInterface|MockObject $timer;
    private LoggerInterface|MockObject $logger;
    private AsyncUdpConnection|MockObject $connection;
    
    protected function setUp(): void
    {
        $this->config = new DnsConfig('example.com', Message::TYPE_A);
        $this->cache = $this->createMock(DnsCacheInterface::class);
        $this->connectionFactory = $this->createMock(UdpConnectionFactoryInterface::class);
        $this->protocolHandler = $this->createMock(DnsProtocolHandlerInterface::class);
        $this->timer = $this->createMock(TimerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->connection = $this->createMock(AsyncUdpConnection::class);
        
        $this->dnsQuery = new DnsQuery(
            $this->config,
            $this->cache,
            $this->connectionFactory,
            $this->protocolHandler,
            $this->timer,
            $this->logger
        );
    }
    
    public function testResolveIPReturnsCachedValueWhenAvailable(): void
    {
        $domain = 'example.com';
        $cachedIp = '192.168.1.1';
        
        $this->cache->expects($this->once())
            ->method('get')
            ->with($domain)
            ->willReturn($cachedIp);
        
        // 确保解析成功回调被调用，且传入了缓存的IP地址
        $resolveCallback = $this->getMockCallable();
        $resolveCallback->expects($this->once())
            ->method('__invoke')
            ->with($cachedIp);
        
        // 确保拒绝回调未被调用
        $rejectCallback = $this->getMockCallable();
        $rejectCallback->expects($this->never())
            ->method('__invoke');
        
        $this->dnsQuery->resolveIP($resolveCallback, $rejectCallback);
    }
    
    public function testResolveIPPerformsQueryWhenNoCachedValue(): void
    {
        $domain = 'example.com';
        $dnsPacket = 'dns-query-packet';
        $dnsResponse = 'dns-response-data';
        $resolvedIp = '192.168.1.1';
        $ttl = 300;
        
        // 创建一个Message对象的模拟
        $answers = [
            new Record('example.com', Message::TYPE_A, Message::CLASS_IN, 300, $resolvedIp)
        ];
        
        // 由于无法直接模拟Message（它是final类），我们创建一个真实的消息对象
        $query = new Query('example.com', Message::TYPE_A, Message::CLASS_IN);
        $message = Message::createRequestForQuery($query);
        $message->answers = $answers;
        
        // 模拟缓存未命中
        $this->cache->expects($this->once())
            ->method('get')
            ->with($domain)
            ->willReturn(null);
        
        // 模拟创建DNS查询包
        $this->protocolHandler->expects($this->once())
            ->method('createQueryPacket')
            ->with($domain, Message::TYPE_A)
            ->willReturn($dnsPacket);
        
        // 模拟创建UDP连接
        $this->connectionFactory->expects($this->once())
            ->method('createConnection')
            ->with($this->config->getDnsServerAddress(), $this->config->getDnsServerPort())
            ->willReturn($this->connection);
        
        // 记录消息处理函数
        $this->connection->expects($this->once())
            ->method('connect');
        
        $this->connection->expects($this->once())
            ->method('send')
            ->with($dnsPacket);
        
        // 运行测试方法
        $resolveCallback = $this->getMockCallable();
        $rejectCallback = $this->getMockCallable();
        
        $this->dnsQuery->resolveIP($resolveCallback, $rejectCallback);
        
        // 获取设置的消息处理函数
        $messageHandler = $this->connection->onMessage;
        $this->assertIsCallable($messageHandler);
        
        // 模拟DNS响应和成功解析        
        $this->protocolHandler->expects($this->once())
            ->method('parseResponse')
            ->with($dnsResponse)
            ->willReturn($message);
        
        $this->protocolHandler->expects($this->once())
            ->method('extractIPFromAnswers')
            ->with($answers)
            ->willReturn($resolvedIp);
        
        $this->protocolHandler->expects($this->once())
            ->method('getTtlFromAnswers')
            ->with($answers)
            ->willReturn($ttl);
        
        $this->cache->expects($this->once())
            ->method('set')
            ->with($domain, $resolvedIp, $ttl);
        
        $this->connection->expects($this->once())
            ->method('close');
        
        $resolveCallback->expects($this->once())
            ->method('__invoke')
            ->with($resolvedIp);
        
        // 触发消息处理函数
        $messageHandler($this->connection, $dnsResponse);
    }
    
    public function testResolveIPHandlesParsingError(): void
    {
        $domain = 'example.com';
        $dnsPacket = 'dns-query-packet';
        $dnsResponse = 'dns-response-data';
        
        // 模拟缓存未命中
        $this->cache->expects($this->once())
            ->method('get')
            ->with($domain)
            ->willReturn(null);
        
        // 模拟创建DNS查询包
        $this->protocolHandler->expects($this->once())
            ->method('createQueryPacket')
            ->with($domain, Message::TYPE_A)
            ->willReturn($dnsPacket);
        
        // 模拟创建UDP连接
        $this->connectionFactory->expects($this->once())
            ->method('createConnection')
            ->with($this->config->getDnsServerAddress(), $this->config->getDnsServerPort())
            ->willReturn($this->connection);
        
        // 运行测试方法
        $resolveCallback = $this->getMockCallable();
        $rejectCallback = $this->getMockCallable();
        
        $this->dnsQuery->resolveIP($resolveCallback, $rejectCallback);
        
        // 获取设置的消息处理函数
        $messageHandler = $this->connection->onMessage;
        
        // 模拟DNS解析异常
        $this->protocolHandler->expects($this->once())
            ->method('parseResponse')
            ->with($dnsResponse)
            ->willThrowException(new DnsParseException('Parse error'));
        
        $this->connection->expects($this->once())
            ->method('close');
        
        $resolveCallback->expects($this->never())
            ->method('__invoke');
        
        $rejectCallback->expects($this->once())
            ->method('__invoke');
        
        // 触发消息处理函数
        $messageHandler($this->connection, $dnsResponse);
    }
    
    public function testResolveIPHandlesNoValidIPFound(): void
    {
        $domain = 'example.com';
        $dnsPacket = 'dns-query-packet';
        $dnsResponse = 'dns-response-data';
        
        // 创建一个Message对象
        $answers = [
            new Record('example.com', Message::TYPE_A, Message::CLASS_IN, 300, 'not-an-ip')
        ];
        
        // 由于无法直接模拟Message（它是final类），我们创建一个真实的消息对象
        $query = new Query('example.com', Message::TYPE_A, Message::CLASS_IN);
        $message = Message::createRequestForQuery($query);
        $message->answers = $answers;
        
        // 模拟缓存未命中
        $this->cache->expects($this->once())
            ->method('get')
            ->with($domain)
            ->willReturn(null);
        
        // 模拟创建DNS查询包
        $this->protocolHandler->expects($this->once())
            ->method('createQueryPacket')
            ->with($domain, Message::TYPE_A)
            ->willReturn($dnsPacket);
        
        // 模拟创建UDP连接
        $this->connectionFactory->expects($this->once())
            ->method('createConnection')
            ->with($this->config->getDnsServerAddress(), $this->config->getDnsServerPort())
            ->willReturn($this->connection);
        
        // 运行测试方法
        $resolveCallback = $this->getMockCallable();
        $rejectCallback = $this->getMockCallable();
        
        $this->dnsQuery->resolveIP($resolveCallback, $rejectCallback);
        
        // 获取设置的消息处理函数
        $messageHandler = $this->connection->onMessage;
        
        // 模拟DNS响应解析成功但没有找到有效IP
        $this->protocolHandler->expects($this->once())
            ->method('parseResponse')
            ->with($dnsResponse)
            ->willReturn($message);
        
        $this->protocolHandler->expects($this->once())
            ->method('extractIPFromAnswers')
            ->with($answers)
            ->willReturn(null);
        
        $this->connection->expects($this->once())
            ->method('close');
        
        $resolveCallback->expects($this->never())
            ->method('__invoke');
        
        $rejectCallback->expects($this->once())
            ->method('__invoke');
        
        // 触发消息处理函数
        $messageHandler($this->connection, $dnsResponse);
    }
    
    public function testResolveIPHandlesConnectionError(): void
    {
        $domain = 'example.com';
        $dnsPacket = 'dns-query-packet';
        $errorCode = 123;
        $errorMessage = 'Connection error';
        
        // 模拟缓存未命中
        $this->cache->expects($this->once())
            ->method('get')
            ->with($domain)
            ->willReturn(null);
        
        // 模拟创建DNS查询包
        $this->protocolHandler->expects($this->once())
            ->method('createQueryPacket')
            ->with($domain, Message::TYPE_A)
            ->willReturn($dnsPacket);
        
        // 模拟创建UDP连接
        $this->connectionFactory->expects($this->once())
            ->method('createConnection')
            ->with($this->config->getDnsServerAddress(), $this->config->getDnsServerPort())
            ->willReturn($this->connection);
        
        // 运行测试方法
        $resolveCallback = $this->getMockCallable();
        $rejectCallback = $this->getMockCallable();
        
        $this->dnsQuery->resolveIP($resolveCallback, $rejectCallback);
        
        // 获取设置的错误处理函数
        $errorHandler = $this->connection->onError;
        $this->assertIsCallable($errorHandler);
        
        $this->connection->expects($this->once())
            ->method('close');
        
        $resolveCallback->expects($this->never())
            ->method('__invoke');
        
        $rejectCallback->expects($this->once())
            ->method('__invoke');
        
        // 触发错误处理函数
        $errorHandler($this->connection, $errorCode, $errorMessage);
    }
    
    /**
     * 创建一个可模拟的回调函数
     * 
     * @return MockObject
     */
    private function getMockCallable(): MockObject
    {
        return $this->getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();
    }
} 