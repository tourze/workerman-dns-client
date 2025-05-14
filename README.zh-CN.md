# workerman-dns-client

DNS Query Client in Workerman

## 安装

```bash
composer require tourze/workerman-dns-client
```

## Workerman DNS 客户端

这是在Workerman环境中使用的DNS查询客户端，用于异步解析域名到IP地址。

### 特性

- 支持异步DNS查询
- 使用React DNS协议
- 支持缓存查询结果
- 完全可测试的模块化设计
- 使用接口和依赖注入实现松耦合

### 用法

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

### 高级用法

您可以自定义DNS服务器地址和超时时间：

```php
<?php

use React\Dns\Model\Message;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tourze\Workerman\DnsClient\DnsQueryFactory;

$cache = new ArrayAdapter();
$dnsClient = DnsQueryFactory::create(
    $cache,               // 缓存适配器
    'example.com',        // 要查询的域名
    Message::TYPE_A,      // 查询类型，默认为A记录
    '8.8.8.8',           // DNS服务器地址，默认为1.1.1.1
    53,                  // DNS服务器端口，默认为53
    10                   // 查询超时时间（秒），默认为5
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

### 自定义组件

您可以注入自己实现的接口来自定义DNS客户端的行为：

```php
<?php

use Tourze\Workerman\DnsClient\Cache\DnsCacheInterface;
use Tourze\Workerman\DnsClient\Connection\UdpConnectionFactoryInterface;
use Tourze\Workerman\DnsClient\DnsConfig;
use Tourze\Workerman\DnsClient\DnsQuery;
use Tourze\Workerman\DnsClient\Logger\LoggerInterface;
use Tourze\Workerman\DnsClient\Protocol\DnsProtocolHandlerInterface;
use Tourze\Workerman\DnsClient\Timer\TimerInterface;

// 创建自定义组件...

$dnsQuery = new DnsQuery(
    $config,               // 配置
    $cache,                // 缓存
    $connectionFactory,    // 连接工厂
    $protocolHandler,      // 协议处理器
    $timer,                // 定时器
    $logger                // 日志记录器
);
```

### 组件详解

- `DnsConfig` - 存储DNS查询配置
- `DnsCacheInterface` - 提供DNS解析结果缓存
- `UdpConnectionFactoryInterface` - 创建UDP连接
- `DnsProtocolHandlerInterface` - 处理DNS协议相关操作
- `TimerInterface` - 处理超时
- `LoggerInterface` - 记录日志

## 配置

待补充

## 示例

待补充

## 参考文档

- [示例链接](https://example.com)
