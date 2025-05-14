<?php

namespace Tourze\Workerman\DnsClient;

/**
 * DNS查询配置
 */
class DnsConfig
{
    /**
     * 默认DNS查询超时时间（秒）
     */
    public const DEFAULT_TIMEOUT = 5;

    /**
     * 默认DNS服务器地址
     */
    public const DEFAULT_DNS_SERVER = '1.1.1.1';

    /**
     * 默认DNS服务器端口
     */
    public const DEFAULT_DNS_PORT = 53;

    public function __construct(
        private readonly string $name,
        private readonly int $type,
        private readonly string $dnsServerAddress = self::DEFAULT_DNS_SERVER,
        private readonly int $dnsServerPort = self::DEFAULT_DNS_PORT,
        private readonly int $timeout = self::DEFAULT_TIMEOUT,
    ) {
    }

    /**
     * 获取查询的域名
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 获取查询类型
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * 获取DNS服务器地址
     */
    public function getDnsServerAddress(): string
    {
        return $this->dnsServerAddress;
    }

    /**
     * 获取DNS服务器端口
     */
    public function getDnsServerPort(): int
    {
        return $this->dnsServerPort;
    }

    /**
     * 获取查询超时时间
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
