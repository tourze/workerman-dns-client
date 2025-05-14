<?php

namespace Tourze\Workerman\DnsClient\Tests\Factory;

use PHPUnit\Framework\TestCase;
use React\Dns\Model\Message;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Tourze\Workerman\DnsClient\DnsClientInterface;
use Tourze\Workerman\DnsClient\DnsQuery;
use Tourze\Workerman\DnsClient\DnsQueryFactory;

class DnsQueryFactoryTest extends TestCase
{
    public function testCreateReturnsValidDnsClient(): void
    {
        $cache = new ArrayAdapter();
        $domain = 'example.com';
        $type = Message::TYPE_A;
        $dnsServer = '8.8.8.8';
        $dnsPort = 53;
        $timeout = 10;
        
        $client = DnsQueryFactory::create($cache, $domain, $type, $dnsServer, $dnsPort, $timeout);
        
        $this->assertInstanceOf(DnsClientInterface::class, $client);
        $this->assertInstanceOf(DnsQuery::class, $client);
    }
    
    public function testCreateUsesDefaultValuesWhenNotProvided(): void
    {
        $cache = new ArrayAdapter();
        $domain = 'example.com';
        
        $client = DnsQueryFactory::create($cache, $domain);
        
        $this->assertInstanceOf(DnsClientInterface::class, $client);
        $this->assertInstanceOf(DnsQuery::class, $client);
        
        // 无法轻易测试默认值是否被使用，因为这些值在DnsQuery内部封装
        // 但至少我们可以确保工厂方法在没有提供默认值的情况下仍然能工作
    }
    
    public function testCreateWithDifferentQueryType(): void
    {
        $cache = new ArrayAdapter();
        $domain = 'example.com';
        $type = Message::TYPE_AAAA; // IPv6地址查询
        
        $client = DnsQueryFactory::create($cache, $domain, $type);
        
        $this->assertInstanceOf(DnsClientInterface::class, $client);
        $this->assertInstanceOf(DnsQuery::class, $client);
    }
    
    public function testCreateWithDifferentCacheAdapter(): void
    {
        $cache = new NullAdapter(); // 使用不同的缓存适配器
        $domain = 'example.com';
        
        $client = DnsQueryFactory::create($cache, $domain);
        
        $this->assertInstanceOf(DnsClientInterface::class, $client);
        $this->assertInstanceOf(DnsQuery::class, $client);
    }
    
    public function testFactoryCreatesCorrectDependencies(): void
    {
        // 检查工厂方法是否创建了正确的依赖关系
        $source = file_get_contents((new \ReflectionClass(DnsQueryFactory::class))->getFileName());
        
        // 验证创建了DnsConfig
        $this->assertStringContainsString('new DnsConfig(', $source);
        
        // 验证创建了SymfonyDnsCache
        $this->assertStringContainsString('new SymfonyDnsCache(', $source);
        
        // 验证创建了WorkermanUdpConnectionFactory
        $this->assertStringContainsString('new WorkermanUdpConnectionFactory(', $source);
        
        // 验证创建了ReactDnsProtocolHandler
        $this->assertStringContainsString('new ReactDnsProtocolHandler(', $source);
        
        // 验证创建了WorkermanTimer
        $this->assertStringContainsString('new WorkermanTimer(', $source);
        
        // 验证创建了WorkermanLogger
        $this->assertStringContainsString('new WorkermanLogger(', $source);
        
        // 验证创建了DnsQuery并注入了所有依赖
        $this->assertStringContainsString('new DnsQuery(', $source);
    }
} 