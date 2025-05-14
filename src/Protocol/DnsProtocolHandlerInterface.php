<?php

namespace Tourze\Workerman\DnsClient\Protocol;

use React\Dns\Model\Message;

/**
 * DNS协议处理接口
 */
interface DnsProtocolHandlerInterface
{
    /**
     * 创建DNS查询数据包
     *
     * @param string $domain 域名
     * @param int $type 查询类型
     * @return string 二进制数据包
     */
    public function createQueryPacket(string $domain, int $type): string;

    /**
     * 解析DNS响应数据
     *
     * @param string $data 二进制响应数据
     * @return Message DNS响应消息对象
     */
    public function parseResponse(string $data): Message;

    /**
     * 从DNS回答中提取IP地址
     *
     * @param array $answers DNS回答数组
     * @return string|null IP地址或null
     */
    public function extractIPFromAnswers(array $answers): ?string;

    /**
     * 从DNS回答中获取TTL（生存时间）
     *
     * @param array $answers DNS回答数组
     * @return int TTL值（秒）
     */
    public function getTtlFromAnswers(array $answers): int;
}
