<?php

namespace Tourze\Workerman\DnsClient\Tests\Unit;

use PHPUnit\Framework\TestCase;
use React\Dns\Model\Message;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tourze\Workerman\DnsClient\DnsClientInterface;
use Tourze\Workerman\DnsClient\DnsConfig;
use Tourze\Workerman\DnsClient\DnsQueryFactory;

class DnsQueryFactoryTest extends TestCase
{
    public function testCreateWithDefaultParameters(): void
    {
        $cache = new ArrayAdapter();
        $client = DnsQueryFactory::create($cache, 'example.com');

        $this->assertInstanceOf(DnsClientInterface::class, $client);
    }

    public function testCreateWithCustomParameters(): void
    {
        $cache = new ArrayAdapter();
        $client = DnsQueryFactory::create(
            $cache,
            'test.com',
            Message::TYPE_AAAA,
            '1.1.1.1',
            5353,
            10
        );

        $this->assertInstanceOf(DnsClientInterface::class, $client);
    }

    public function testCreateWithDefaultDnsServer(): void
    {
        $cache = new ArrayAdapter();
        $client = DnsQueryFactory::create(
            $cache,
            'example.com',
            Message::TYPE_A,
            DnsConfig::DEFAULT_DNS_SERVER,
            DnsConfig::DEFAULT_DNS_PORT,
            DnsConfig::DEFAULT_TIMEOUT
        );

        $this->assertInstanceOf(DnsClientInterface::class, $client);
    }
}