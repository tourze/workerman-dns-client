<?php

namespace Tourze\Workerman\DnsClient\Tests;

use PHPUnit\Framework\TestCase;
use React\Dns\Model\Message;
use Tourze\Workerman\DnsClient\DnsConfig;

class DnsConfigTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $name = 'example.com';
        $type = Message::TYPE_A;
        $dnsServer = '8.8.8.8';
        $dnsPort = 53;
        $timeout = 10;
        
        $config = new DnsConfig($name, $type, $dnsServer, $dnsPort, $timeout);
        
        $this->assertEquals($name, $config->getName());
        $this->assertEquals($type, $config->getType());
        $this->assertEquals($dnsServer, $config->getDnsServerAddress());
        $this->assertEquals($dnsPort, $config->getDnsServerPort());
        $this->assertEquals($timeout, $config->getTimeout());
    }
    
    public function testConstructorWithDefaultValues(): void
    {
        $name = 'example.com';
        $type = Message::TYPE_A;
        
        $config = new DnsConfig($name, $type);
        
        $this->assertEquals($name, $config->getName());
        $this->assertEquals($type, $config->getType());
        $this->assertEquals(DnsConfig::DEFAULT_DNS_SERVER, $config->getDnsServerAddress());
        $this->assertEquals(DnsConfig::DEFAULT_DNS_PORT, $config->getDnsServerPort());
        $this->assertEquals(DnsConfig::DEFAULT_TIMEOUT, $config->getTimeout());
    }
    
    public function testConstructorWithPartialCustomValues(): void
    {
        $name = 'example.com';
        $type = Message::TYPE_A;
        $dnsServer = '8.8.8.8';
        
        $config = new DnsConfig($name, $type, $dnsServer);
        
        $this->assertEquals($name, $config->getName());
        $this->assertEquals($type, $config->getType());
        $this->assertEquals($dnsServer, $config->getDnsServerAddress());
        $this->assertEquals(DnsConfig::DEFAULT_DNS_PORT, $config->getDnsServerPort());
        $this->assertEquals(DnsConfig::DEFAULT_TIMEOUT, $config->getTimeout());
    }
} 