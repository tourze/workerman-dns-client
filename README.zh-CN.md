# Workerman DNS Client

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/workerman-dns-client.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-dns-client)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/workerman-dns-client.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-dns-client)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/workerman-dns-client.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-dns-client)
[![License](https://img.shields.io/packagist/l/tourze/workerman-dns-client.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-dns-client)
[![codecov](https://codecov.io/gh/tourze/php-monorepo/branch/master/graph/badge.svg?token=COVERAGE_TOKEN&flag=workerman-dns-client)](https://codecov.io/gh/tourze/php-monorepo)

专为 Workerman 环境设计的高性能异步 DNS 查询客户端，提供非阻塞的域名到 IP 地址解析功能。

## 特性

- **异步 DNS 解析** - 使用 Workerman 事件循环的非阻塞 DNS 查询
- **React DNS 协议支持** - 基于可靠的 React DNS 库构建
- **智能缓存** - 使用 Symfony Cache 组件的可配置结果缓存
- **模块化架构** - 完全可测试的依赖注入设计
- **接口化设计** - 松耦合设计便于自定义和测试
- **生产就绪** - 全面的错误处理和日志记录支持

## 安装

```bash
composer require tourze/workerman-dns-client
```

## 系统要求

- PHP 8.1 或更高版本
- ext-filter 扩展
- workerman/workerman ^5.1
- react/dns ^1.13
- symfony/cache ^7.3

## 快速开始

### 基本用法

```php
<?php

use React\Dns\Model\Message;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tourze\Workerman\DnsClient\DnsQueryFactory;

// 创建缓存适配器
$cache = new ArrayAdapter();

// 创建DNS查询客户端
$dnsClient = DnsQueryFactory::create($cache, 'example.com', Message::TYPE_A);

// 执行DNS查询
$dnsClient->resolveIP(
    function (string $ip) {
        echo "解析成功: $ip\n";
    },
    function () {
        echo "解析失败\n";
    }
);
```

### 高级配置

您可以自定义 DNS 服务器设置和超时时间：

```php
<?php

use React\Dns\Model\Message;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tourze\Workerman\DnsClient\DnsQueryFactory;

$cache = new ArrayAdapter();
$dnsClient = DnsQueryFactory::create(
    $cache,               // 缓存适配器
    'example.com',        // 要查询的域名
    Message::TYPE_A,      // 查询类型（A、AAAA、MX 等）
    '8.8.8.8',           // DNS服务器地址（默认：1.1.1.1）
    53,                  // DNS服务器端口（默认：53）
    10                   // 查询超时时间秒数（默认：5）
);

$dnsClient->resolveIP(
    function (string $ip) {
        echo "解析成功: $ip\n";
    },
    function () {
        echo "解析失败\n";
    }
);
```

## 架构设计

### 组件概览

该库遵循模块化架构设计，具有清晰的关注点分离：

- **`DnsConfig`** - 存储 DNS 查询参数的不可变配置对象
- **`DnsCacheInterface`** - DNS 解析结果的缓存层
- **`UdpConnectionFactoryInterface`** - 创建 UDP 连接的工厂
- **`DnsProtocolHandlerInterface`** - DNS 协议操作和消息处理
- **`TimerInterface`** - 查询的超时管理
- **`LoggerInterface`** - 日志记录和调试支持

### 自定义实现

对于高级用例，您可以提供自定义实现：

```php
<?php

use Tourze\Workerman\DnsClient\Cache\DnsCacheInterface;
use Tourze\Workerman\DnsClient\Connection\UdpConnectionFactoryInterface;
use Tourze\Workerman\DnsClient\DnsConfig;
use Tourze\Workerman\DnsClient\DnsQuery;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Protocol\DnsProtocolHandlerInterface;
use Tourze\Workerman\DnsClient\Timer\TimerInterface;

// 自定义配置
$config = new DnsConfig('example.com', Message::TYPE_A, '8.8.8.8', 53, 10);

// 注入自定义组件
$dnsQuery = new DnsQuery(
    $config,                  // 配置
    $customCache,             // 您的缓存实现
    $customConnectionFactory, // 您的连接工厂
    $customProtocolHandler,   // 您的协议处理器
    $customTimer,             // 您的定时器实现
    $customLogger             // 您的日志记录器
);
```

## 测试

运行测试套件：

```bash
./vendor/bin/phpunit packages/workerman-dns-client/tests
```

该库包含覆盖所有组件和集成场景的全面单元测试。

## 贡献

欢迎贡献代码！请随时提交 Pull Request。

## 许可证

MIT 许可证。详情请参见 [许可证文件](LICENSE)。
