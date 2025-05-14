<?php

namespace Tourze\Workerman\DnsClient\Cache;

/**
 * DNS缓存接口
 */
interface DnsCacheInterface
{
    /**
     * 从缓存中获取IP地址
     *
     * @param string $domain 域名
     * @return string|null 缓存的IP地址，如果不存在则返回null
     */
    public function get(string $domain): ?string;
    
    /**
     * 将IP地址存入缓存
     *
     * @param string $domain 域名
     * @param string $ip IP地址
     * @param int $ttl 缓存有效期（秒）
     * @return void
     */
    public function set(string $domain, string $ip, int $ttl): void;
}
