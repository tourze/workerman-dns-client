<?php

namespace Tourze\Workerman\DnsClient\Cache;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Logger\NullLogger;

/**
 * 基于Symfony缓存的DNS缓存实现
 */
class SymfonyDnsCache implements DnsCacheInterface
{
    /**
     * 缓存键前缀
     */
    private const CACHE_PREFIX = 'dns_query_';

    /**
     * 最大缓存时间（秒）
     */
    private const MAX_TTL = 60;

    public function __construct(
        private readonly AdapterInterface $cache,
        private readonly LoggerInterface $logger = new NullLogger()
    )
    {
    }

    /**
     * 生成缓存键
     */
    private function getCacheKey(string $domain): string
    {
        return self::CACHE_PREFIX . md5($domain);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $domain): ?string
    {
        $cacheItem = $this->cache->getItem($this->getCacheKey($domain));

        if ($cacheItem->isHit()) {
            $ip = $cacheItem->get();
            $this->logger->log("已从缓存获得结果：{$domain}:$ip");
            return $ip;
        }

        return null;
    }

    public function set(string $domain, string $ip, int $ttl): void
    {
        $cacheItem = $this->cache->getItem($this->getCacheKey($domain));
        $cacheItem->set($ip);
        $cacheItem->expiresAfter(min($ttl, self::MAX_TTL)); // 限制TTL最大值
        $this->cache->save($cacheItem);
    }
}
