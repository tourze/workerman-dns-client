<?php

namespace Tourze\Workerman\DnsClient;

/**
 * DNS客户端接口
 */
interface DnsClientInterface
{
    /**
     * 解析域名为IP地址
     *
     * @param callable $resolve 成功回调函数，接收解析出的IP地址
     * @param callable|null $reject 失败回调函数
     * @return void
     */
    public function resolveIP(callable $resolve, ?callable $reject = null): void;
}
