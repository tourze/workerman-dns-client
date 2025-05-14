<?php

namespace Tourze\Workerman\DnsClient;

use Tourze\Workerman\DnsClient\Cache\DnsCacheInterface;
use Tourze\Workerman\DnsClient\Connection\UdpConnectionFactoryInterface;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Protocol\DnsProtocolHandlerInterface;
use Tourze\Workerman\DnsClient\Timer\TimerInterface;
use Workerman\Connection\AsyncUdpConnection;

/**
 * DNS查询客户端
 */
class DnsQuery implements DnsClientInterface
{
    /**
     * 构造DNS查询客户端
     */
    public function __construct(
        private readonly DnsConfig $config,
        private readonly DnsCacheInterface $cache,
        private readonly UdpConnectionFactoryInterface $connectionFactory,
        private readonly DnsProtocolHandlerInterface $protocolHandler,
        private readonly TimerInterface $timer,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function resolveIP(callable $resolve, ?callable $reject = null): void
    {
        $reject = $reject ?? function () {
        };

        $domain = $this->config->getName();

        // 尝试从缓存获取
        $cachedIp = $this->cache->get($domain);
        if ($cachedIp !== null) {
            $resolve($cachedIp);
            return;
        }

        // 创建DNS查询包
        $packet = $this->protocolHandler->createQueryPacket($domain, $this->config->getType());

        // 创建UDP连接
        $serverAddress = $this->config->getDnsServerAddress();
        $serverPort = $this->config->getDnsServerPort();
        $connection = $this->connectionFactory->createConnection($serverAddress, $serverPort);

        // 设置超时
        $timerId = 0;
        $timeout = $this->config->getTimeout();
        if ($timeout > 0) {
            $timerId = $this->setupTimeoutTimer($timeout, $connection, $reject);
        }

        // 设置消息回调
        $this->setupMessageHandler($connection, $domain, $resolve, $reject, $timerId);

        // 设置错误回调
        $this->setupErrorHandler($connection, $domain, $reject, $timerId);

        // 开始查询
        $this->logger->log("开始从DNS[{$serverAddress}:{$serverPort}]查询：{$domain}");
        $connection->connect();
        $connection->send($packet);
    }

    /**
     * 设置超时定时器
     *
     * @param int $timeout 超时时间（秒）
     * @param AsyncUdpConnection $connection UDP连接
     * @param callable $reject 失败回调
     * @return int 定时器ID
     */
    private function setupTimeoutTimer(int $timeout, AsyncUdpConnection $connection, callable $reject): int
    {
        $domain = $this->config->getName();

        return $this->timer->add($timeout, function () use ($domain, $connection, $reject) {
            $this->logger->log("DNS查询超时 [{$domain}]");
            $reject();
            $connection->close();
        }, [], false);
    }

    /**
     * 设置消息处理回调
     *
     * @param AsyncUdpConnection $connection UDP连接
     * @param string $domain 查询的域名
     * @param callable $resolve 成功回调
     * @param callable $reject 失败回调
     * @param int $timerId 超时定时器ID
     */
    private function setupMessageHandler(
        AsyncUdpConnection $connection,
        string $domain,
        callable $resolve,
        callable $reject,
        int $timerId
    ): void
    {
        $connection->onMessage = function (AsyncUdpConnection $connection, string $data) use (
            $domain,
            $resolve,
            $reject,
            $timerId
        ) {
            // 清除超时定时器
            if ($timerId) {
                $this->timer->del($timerId);
            }

            try {
                // 解析DNS响应
                $message = $this->protocolHandler->parseResponse($data);
                $this->logger->log("已从DNS[{$this->config->getDnsServerAddress()}:{$this->config->getDnsServerPort()}]获得结果：{$domain}");

                // 提取IP地址
                $ip = $this->protocolHandler->extractIPFromAnswers($message->answers);

                if ($ip === null) {
                    $this->logger->log("DNS解析失败 [{$domain}]");
                    $reject();
                } else {
                    $this->logger->log("DNS解析成功 [{$domain}]:$ip");

                    // 获取TTL并缓存结果
                    $ttl = $this->protocolHandler->getTtlFromAnswers($message->answers);
                    $this->cache->set($domain, $ip, $ttl);

                    $resolve($ip);
                }
            } catch (\Throwable $e) {
                $this->logger->log("DNS解析错误 [{$domain}]: " . $e->getMessage());
                $reject();
            } finally {
                $connection->close();
            }
        };
    }

    /**
     * 设置错误处理回调
     *
     * @param AsyncUdpConnection $connection UDP连接
     * @param string $domain 查询的域名
     * @param callable $reject 失败回调
     * @param int $timerId 超时定时器ID
     */
    private function setupErrorHandler(
        AsyncUdpConnection $connection,
        string $domain,
        callable $reject,
        int $timerId
    ): void
    {
        $connection->onError = function (AsyncUdpConnection $connection, $code, $msg) use (
            $domain,
            $reject,
            $timerId
        ) {
            if ($timerId) {
                $this->timer->del($timerId);
            }
            $this->logger->log("DNS连接错误 [{$domain}]: $code $msg");
            $reject();
            $connection->close();
        };
    }
}
