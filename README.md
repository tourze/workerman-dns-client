# Workerman DNS Client

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/workerman-dns-client.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-dns-client)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/workerman-dns-client.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-dns-client)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/workerman-dns-client.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-dns-client)
[![License](https://img.shields.io/packagist/l/tourze/workerman-dns-client.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-dns-client)
[![codecov](https://codecov.io/gh/tourze/php-monorepo/branch/master/graph/badge.svg?token=COVERAGE_TOKEN&flag=workerman-dns-client)](https://codecov.io/gh/tourze/php-monorepo)

A high-performance asynchronous DNS query client specifically designed for the Workerman environment, providing non-blocking domain name resolution to IP addresses.

## Features

- **Asynchronous DNS Resolution** - Non-blocking DNS queries using Workerman's event loop
- **React DNS Protocol Support** - Built on the reliable React DNS library
- **Smart Caching** - Configurable result caching using Symfony Cache components
- **Modular Architecture** - Fully testable design with dependency injection
- **Interface-based Design** - Loose coupling allows easy customization and testing
- **Production Ready** - Comprehensive error handling and logging support

## Installation

```bash
composer require tourze/workerman-dns-client
```

## Requirements

- PHP 8.1 or higher
- ext-filter
- workerman/workerman ^5.1
- react/dns ^1.13
- symfony/cache ^7.3

## Quick Start

### Basic Usage

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

### Advanced Configuration

You can customize DNS server settings and timeouts:

```php
<?php

use React\Dns\Model\Message;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tourze\Workerman\DnsClient\DnsQueryFactory;

$cache = new ArrayAdapter();
$dnsClient = DnsQueryFactory::create(
    $cache,               // Cache adapter
    'example.com',        // Domain to query
    Message::TYPE_A,      // Query type (A, AAAA, MX, etc.)
    '8.8.8.8',           // DNS server address (default: 1.1.1.1)
    53,                  // DNS server port (default: 53)
    10                   // Query timeout in seconds (default: 5)
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

## Architecture

### Component Overview

The library follows a modular architecture with clear separation of concerns:

- **`DnsConfig`** - Immutable configuration object storing DNS query parameters
- **`DnsCacheInterface`** - Caching layer for DNS resolution results
- **`UdpConnectionFactoryInterface`** - Factory for creating UDP connections
- **`DnsProtocolHandlerInterface`** - DNS protocol operations and message handling
- **`TimerInterface`** - Timeout management for queries
- **`LoggerInterface`** - Logging and debugging support

### Custom Implementation

For advanced use cases, you can provide custom implementations:

```php
<?php

use Tourze\Workerman\DnsClient\Cache\DnsCacheInterface;
use Tourze\Workerman\DnsClient\Connection\UdpConnectionFactoryInterface;
use Tourze\Workerman\DnsClient\DnsConfig;
use Tourze\Workerman\DnsClient\DnsQuery;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Protocol\DnsProtocolHandlerInterface;
use Tourze\Workerman\DnsClient\Timer\TimerInterface;

// Custom configuration
$config = new DnsConfig('example.com', Message::TYPE_A, '8.8.8.8', 53, 10);

// Inject custom components
$dnsQuery = new DnsQuery(
    $config,               // Configuration
    $customCache,          // Your cache implementation
    $customConnectionFactory, // Your connection factory
    $customProtocolHandler,   // Your protocol handler
    $customTimer,          // Your timer implementation
    $customLogger          // Your logger implementation
);
```

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit packages/workerman-dns-client/tests
```

The library includes comprehensive unit tests covering all components and integration scenarios.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
