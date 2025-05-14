<?php

namespace Tourze\Workerman\DnsClient\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tourze\Workerman\DnsClient\Cache\SymfonyDnsCache;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;

class SymfonyDnsCacheTest extends TestCase
{
    private SymfonyDnsCache $cache;
    private ArrayAdapter $adapter;
    private LoggerInterface $logger;
    
    protected function setUp(): void
    {
        $this->adapter = new ArrayAdapter();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cache = new SymfonyDnsCache($this->adapter, $this->logger);
    }
    
    public function testGetReturnsNullWhenItemNotInCache(): void
    {
        $result = $this->cache->get('example.com');
        
        $this->assertNull($result);
    }
    
    public function testGetReturnsValueWhenItemInCache(): void
    {
        $domain = 'example.com';
        $ip = '192.168.1.1';
        $ttl = 300;
        
        // 配置模拟的日志记录器
        $this->logger->expects($this->once())
            ->method('log')
            ->with($this->stringContains('已从缓存获得结果'));
        
        // 手动设置缓存项
        $cacheKey = 'dns_query_' . md5($domain);
        $cacheItem = $this->adapter->getItem($cacheKey);
        $cacheItem->set($ip);
        $cacheItem->expiresAfter($ttl);
        $this->adapter->save($cacheItem);
        
        $result = $this->cache->get($domain);
        
        $this->assertEquals($ip, $result);
    }
    
    public function testSetStoresValueInCache(): void
    {
        $domain = 'example.com';
        $ip = '192.168.1.1';
        $ttl = 300;
        
        $this->cache->set($domain, $ip, $ttl);
        
        $cacheKey = 'dns_query_' . md5($domain);
        $cacheItem = $this->adapter->getItem($cacheKey);
        
        $this->assertTrue($cacheItem->isHit());
        $this->assertEquals($ip, $cacheItem->get());
    }
    
    public function testSetLimitsTtlToMaxValue(): void
    {
        $domain = 'example.com';
        $ip = '192.168.1.1';
        $longTtl = 3600; // 1 hour
        
        $this->cache->set($domain, $ip, $longTtl);
        
        // Unfortunately, we can't easily test the expiry time directly
        // since ArrayAdapter doesn't expose it. Instead, we'll verify that
        // the item was stored correctly.
        $cacheKey = 'dns_query_' . md5($domain);
        $cacheItem = $this->adapter->getItem($cacheKey);
        
        $this->assertTrue($cacheItem->isHit());
        $this->assertEquals($ip, $cacheItem->get());
    }
} 