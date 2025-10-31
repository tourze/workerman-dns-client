<?php

namespace Tourze\Workerman\DnsClient\Tests\DnsQuery;

/**
 * 用于测试的回调Mock类
 */
class MockCallable
{
    public bool $called = false;

    public mixed $args = null;

    /**
     * @param mixed ...$args
     */
    public function __invoke(mixed ...$args): void
    {
        $this->called = true;
        $this->args = $args;
    }
}