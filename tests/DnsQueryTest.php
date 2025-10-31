<?php

namespace Tourze\Workerman\DnsClient\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\Workerman\DnsClient\Cache\DnsCacheInterface;
use Tourze\Workerman\DnsClient\Connection\UdpConnectionFactoryInterface;
use Tourze\Workerman\DnsClient\DnsConfig;
use Tourze\Workerman\DnsClient\DnsQuery;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Protocol\DnsProtocolHandlerInterface;
use Tourze\Workerman\DnsClient\Timer\TimerInterface;

/**
 * @internal
 */
#[CoversClass(DnsQuery::class)]
final class DnsQueryTest extends TestCase
{
    private DnsCacheInterface&MockObject $cache;

    private UdpConnectionFactoryInterface&MockObject $connectionFactory;

    private DnsProtocolHandlerInterface&MockObject $protocolHandler;

    private TimerInterface&MockObject $timer;

    private LoggerInterface&MockObject $logger;

    private DnsConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->createMock(DnsCacheInterface::class);
        $this->connectionFactory = $this->createMock(UdpConnectionFactoryInterface::class);
        $this->protocolHandler = $this->createMock(DnsProtocolHandlerInterface::class);
        $this->timer = $this->createMock(TimerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->config = new DnsConfig('example.com', 1, '8.8.8.8', 53, 5);
    }

    public function testConstructor(): void
    {
        $dnsQuery = new DnsQuery(
            $this->config,
            $this->cache,
            $this->connectionFactory,
            $this->protocolHandler,
            $this->timer,
            $this->logger
        );

        $this->assertInstanceOf(DnsQuery::class, $dnsQuery);
    }

    public function testResolveIPWithCachedResult(): void
    {
        $expectedIp = '192.0.2.1';
        $this->cache->expects($this->once())
            ->method('get')
            ->with('example.com')
            ->willReturn($expectedIp)
        ;

        $resolved = false;
        $resolvedIp = null;

        $dnsQuery = new DnsQuery(
            $this->config,
            $this->cache,
            $this->connectionFactory,
            $this->protocolHandler,
            $this->timer,
            $this->logger
        );

        $dnsQuery->resolveIP(function ($ip) use (&$resolved, &$resolvedIp): void {
            $resolved = true;
            $resolvedIp = $ip;
        });

        $this->assertTrue($resolved);
        $this->assertEquals($expectedIp, $resolvedIp);
    }
}
