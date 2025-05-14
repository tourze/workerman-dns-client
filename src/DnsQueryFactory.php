<?php

namespace Tourze\Workerman\DnsClient;

use React\Dns\Model\Message;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Tourze\Workerman\DnsClient\Cache\SymfonyDnsCache;
use Tourze\Workerman\DnsClient\Connection\WorkermanUdpConnectionFactory;
use Tourze\Workerman\DnsClient\Logger\WorkermanLogger;
use Tourze\Workerman\DnsClient\Protocol\ReactDnsProtocolHandler;
use Tourze\Workerman\DnsClient\Timer\WorkermanTimer;

/**
 * DNS查询客户端工厂
 */
class DnsQueryFactory
{
    /**
     * 创建默认的DNS查询客户端
     *
     * @param AdapterInterface $cache 缓存适配器
     * @param string $domain 要查询的域名
     * @param int $type 查询类型，默认为A记录
     * @param string $dnsServer DNS服务器地址
     * @param int $dnsPort DNS服务器端口
     * @param int $timeout 查询超时时间（秒）
     * @return DnsClientInterface
     */
    public static function create(
        AdapterInterface $cache,
        string $domain,
        int $type = Message::TYPE_A,
        string $dnsServer = DnsConfig::DEFAULT_DNS_SERVER,
        int $dnsPort = DnsConfig::DEFAULT_DNS_PORT,
        int $timeout = DnsConfig::DEFAULT_TIMEOUT
    ): DnsClientInterface {
        // 创建配置
        $config = new DnsConfig($domain, $type, $dnsServer, $dnsPort, $timeout);
        
        // 创建依赖组件
        $dnsCache = new SymfonyDnsCache($cache);
        $connectionFactory = new WorkermanUdpConnectionFactory();
        $protocolHandler = new ReactDnsProtocolHandler();
        $timer = new WorkermanTimer();
        $logger = new WorkermanLogger();
        
        // 创建DNS查询客户端
        return new DnsQuery(
            $config,
            $dnsCache,
            $connectionFactory,
            $protocolHandler,
            $timer,
            $logger
        );
    }
}
