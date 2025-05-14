# Workerman DNS Client

This is a DNS query client for use in the Workerman environment, designed for asynchronous domain name resolution to IP addresses.

## Features

- Asynchronous DNS query support
- Uses React DNS protocol
- Caching of query results
- Fully testable modular design
- Interface-based, loose coupling with dependency injection

## Basic Usage

```php
<?php

use React\Dns\Model\Message;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tourze\Workerman\DnsClient\DnsQueryFactory;

// Create a cache adapter
$cache = new ArrayAdapter();

// Create a DNS query client
$dnsClient = DnsQueryFactory::create($cache, 'example.com', Message::TYPE_A);

// Perform DNS query
$dnsClient->resolveIP(
    function (string $ip) {
        echo "Resolution successful: $ip\n";
    },
    function () {
        echo "Resolution failed\n";
    }
);
```

## Advanced Usage

You can customize the DNS server address and timeout:

```php
<?php

use React\Dns\Model\Message;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tourze\Workerman\DnsClient\DnsQueryFactory;

$cache = new ArrayAdapter();
$dnsClient = DnsQueryFactory::create(
    $cache,               // Cache adapter
    'example.com',        // Domain to query
    Message::TYPE_A,      // Query type, defaults to A record
    '8.8.8.8',           // DNS server address, defaults to 1.1.1.1
    53,                  // DNS server port, defaults to 53
    10                   // Query timeout in seconds, defaults to 5
);

$dnsClient->resolveIP(
    function (string $ip) {
        echo "Resolution successful: $ip\n";
    },
    function () {
        echo "Resolution failed\n";
    }
);
```

## Custom Components

You can inject your own interface implementations to customize the DNS client behavior:

```php
<?php

use Tourze\Workerman\DnsClient\Cache\DnsCacheInterface;
use Tourze\Workerman\DnsClient\Connection\UdpConnectionFactoryInterface;
use Tourze\Workerman\DnsClient\DnsConfig;
use Tourze\Workerman\DnsClient\DnsQuery;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Protocol\DnsProtocolHandlerInterface;
use Tourze\Workerman\DnsClient\Timer\TimerInterface;

// Create your custom components...

$dnsQuery = new DnsQuery(
    $config,               // Configuration
    $cache,                // Cache
    $connectionFactory,    // Connection factory
    $protocolHandler,      // Protocol handler
    $timer,                // Timer
    $logger                // Logger
);
```

## Component Details

- `DnsConfig` - Stores DNS query configuration
- `DnsCacheInterface` - Provides DNS resolution result caching
- `UdpConnectionFactoryInterface` - Creates UDP connections
- `DnsProtocolHandlerInterface` - Handles DNS protocol operations
- `TimerInterface` - Handles timeouts
- `LoggerInterface` - Logs messages

## Testing

The library is designed with testing in mind. Each component can be mocked or replaced for unit testing.

```bash
./vendor/bin/phpunit packages/workerman-dns-client/tests
```

## Requirements

- PHP 8.1 or higher
- ext-filter
- workerman/workerman ^5.1
- react/dns ^1.13
- symfony/cache ^6.4
