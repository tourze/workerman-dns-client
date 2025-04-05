<?php

namespace Tourze\Workerman\DnsClient;

use React\Dns\Model\Message;
use React\Dns\Protocol\BinaryDumper;
use React\Dns\Protocol\Parser;
use React\Dns\Query\Query;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Workerman\Connection\AsyncUdpConnection;
use Workerman\Timer;
use Workerman\Worker;

/**
 * 发起DNS查询
 */
class DnsQuery
{
    /**
     * 默认DNS查询超时时间（秒）
     */
    private const DEFAULT_TIMEOUT = 5;

    public function __construct(
        private readonly AdapterInterface $cache,
        private readonly string $name,
        private readonly int $type,
        private readonly string $dnsServerAddress = '1.1.1.1',
        private readonly int $dnsServerPort = 53,
        private readonly int $timeout = self::DEFAULT_TIMEOUT,
    )
    {
    }

    /**
     * 解释DNS查询结果并调用回调函数
     *
     * @param callable<Message> $resolve
     * @param callable<Message>|null $reject
     * @return void
     */
    public function resolveIP(callable $resolve, callable $reject = null): void
    {
        $reject = $reject ?? function () {
        };

        $cacheItem = $this->cache->getItem('dns_query_' . md5($this->name));
        if ($cacheItem->isHit()) {
            $ip = $cacheItem->get();
            Worker::log("已从缓存获得结果：{$this->name}:$ip");
            $resolve($ip);
            return;
        }

        $query = new Query($this->name, $this->type, Message::CLASS_IN);
        $message = Message::createRequestForQuery($query);
        $dumper = new BinaryDumper();

        $packet = $dumper->toBinary($message);

        $connection = new AsyncUdpConnection("udp://{$this->dnsServerAddress}:{$this->dnsServerPort}");

        // 设置超时定时器
        $timerId = 0;
        if ($this->timeout > 0) {
            $timerId = Timer::add($this->timeout, function () use (&$timerId, $connection, $reject) {
                Worker::log("DNS查询超时 [{$this->name}]");
                Timer::del($timerId);
                $reject();
                $connection->close();
            }, [], false);
        }

        $connection->onMessage = function (AsyncUdpConnection $connection, string $data) use ($resolve, $reject, $cacheItem, &$timerId) {
            // 清除超时定时器
            if ($timerId) {
                Timer::del($timerId);
            }

            try {
                $parser = new Parser();
                $message = $parser->parseMessage($data);
                Worker::log("已从DNS[{$this->dnsServerAddress}:{$this->dnsServerPort}]获得结果：{$this->name}");

                $ip = $this->extractIPFromAnswers($message->answers);

                if ($ip === null) {
                    Worker::log("DNS解析失败 [{$this->name}]");
                    $reject();
                } else {
                    Worker::log("DNS解析成功 [{$this->name}]:$ip");
                    $this->cacheResult($cacheItem, $ip, $message->answers);
                    $resolve($ip);
                }
            } catch (\Throwable $e) {
                Worker::log("DNS解析错误 [{$this->name}]: " . $e->getMessage());
                $reject();
            } finally {
                $connection->close();
            }
        };

        $connection->onError = function (AsyncUdpConnection $connection, $code, $msg) use ($reject, &$timerId) {
            if ($timerId) {
                Timer::del($timerId);
            }
            Worker::log("DNS连接错误 [{$this->name}]: $code $msg");
            $reject();
            $connection->close();
        };

        Worker::log("开始从DNS[{$this->dnsServerAddress}:{$this->dnsServerPort}]查询：{$this->name}");
        $connection->connect();
        $connection->send($packet);
    }

    /**
     * 从DNS回答中提取有效IP
     *
     * @param array $answers DNS回答数组
     * @return string|null 有效IP或null
     */
    private function extractIPFromAnswers(array $answers): ?string
    {
        if (empty($answers)) {
            Worker::log("DNS解析为空 [{$this->name}]");
            return null;
        }

        foreach ($answers as $answer) {
            if (filter_var($answer->data, FILTER_VALIDATE_IP)) {
                return $answer->data;
            }
        }

        Worker::log("DNS解析找不到合法IP [{$this->name}]");
        return null;
    }

    /**
     * 缓存DNS解析结果
     *
     * @param CacheItem $cacheItem 缓存项
     * @param string $ip IP地址
     * @param array $answers DNS回答数组
     */
    private function cacheResult(CacheItem $cacheItem, string $ip, array $answers): void
    {
        $ttl = 600; // 默认TTL

        foreach ($answers as $answer) {
            if (filter_var($answer->data, FILTER_VALIDATE_IP)) {
                $ttl = $answer->ttl;
                break;
            }
        }

        $cacheItem->set($ip);
        $cacheItem->expiresAfter(min($ttl, 60)); // 限制TTL最大值为60秒
        $this->cache->save($cacheItem);
    }
}
