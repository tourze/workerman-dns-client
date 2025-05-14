<?php

namespace Tourze\Workerman\DnsClient\Protocol;

use React\Dns\Model\Message;
use React\Dns\Protocol\BinaryDumper;
use React\Dns\Protocol\Parser;
use React\Dns\Query\Query;
use Tourze\Workerman\DnsClient\Exception\DnsParseException;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Logger\NullLogger;

/**
 * 基于React DNS的协议处理实现
 */
class ReactDnsProtocolHandler implements DnsProtocolHandlerInterface
{
    /**
     * 默认TTL（秒）
     */
    private const DEFAULT_TTL = 600;

    private readonly BinaryDumper $dumper;
    private readonly Parser $parser;

    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
        $this->dumper = new BinaryDumper();
        $this->parser = new Parser();
    }

    public function createQueryPacket(string $domain, int $type): string
    {
        $query = new Query($domain, $type, Message::CLASS_IN);
        $message = Message::createRequestForQuery($query);

        return $this->dumper->toBinary($message);
    }

    public function parseResponse(string $data): Message
    {
        try {
            return $this->parser->parseMessage($data);
        } catch (\Throwable $e) {
            throw new DnsParseException('DNS响应解析失败: ' . $e->getMessage(), 0, $e);
        }
    }

    public function extractIPFromAnswers(array $answers): ?string
    {
        if (empty($answers)) {
            $this->logger->log("DNS解析为空");
            return null;
        }

        foreach ($answers as $answer) {
            if (filter_var($answer->data, FILTER_VALIDATE_IP)) {
                return $answer->data;
            }
        }

        $this->logger->log("DNS解析找不到合法IP");
        return null;
    }

    public function getTtlFromAnswers(array $answers): int
    {
        $ttl = self::DEFAULT_TTL;

        foreach ($answers as $answer) {
            if (filter_var($answer->data, FILTER_VALIDATE_IP)) {
                $ttl = $answer->ttl;
                break;
            }
        }

        return $ttl;
    }
}
