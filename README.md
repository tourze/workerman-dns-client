# Workerman DNS Client

基于 Workerman 的 DNS 客户端，用于解析 DNS 域名，并提供支持自定义 DNS 解析的 TCP 连接。

## 安装

```bash
composer require tourze/workerman-dns-client
```

## 功能

1. 支持使用自定义 DNS 服务器进行 DNS 查询
2. 提供缓存机制避免重复查询
3. 扩展了 AsyncTcpConnection，支持使用自定义 DNS 解析

## 基本用法

### DNS 查询

```php
use React\Dns\Model\Message;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tourze\Workerman\DnsClient\DnsQuery;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

$worker = new Worker();
$worker->onWorkerStart = function() {
    // 创建缓存
    $cache = new ArrayAdapter();
    
    // 创建DNS查询
    $query = new DnsQuery(
        $cache,                      // 缓存适配器
        'example.com',               // 要查询的域名
        Message::TYPE_A,             // 查询类型 (A记录)
        '8.8.8.8',                   // DNS服务器IP (此处使用Google DNS)
        53                           // DNS服务器端口
    );
    
    // 解析IP
    $query->resolveIP(
        function($ip) {
            echo "解析成功: $ip\n";
        },
        function() {
            echo "解析失败\n";
        }
    );
};

Worker::runAll();
```

### 使用自定义DNS解析的AsyncTcpConnection

```php
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tourze\Workerman\DnsClient\DnsAsyncTcpConnection;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

$worker = new Worker();
$worker->onWorkerStart = function() {
    // 初始化DNS缓存
    DnsAsyncTcpConnection::initDnsCache(new ArrayAdapter());
    
    // 创建连接
    $connection = new DnsAsyncTcpConnection('ws://example.com:8080');
    
    // 设置DNS服务器 (可选)
    $connection->setDnsServer('1.1.1.1', 53);
    
    // 连接事件
    $connection->onConnect = function($connection) {
        echo "连接成功\n";
        $connection->send('hello');
    };
    
    $connection->onMessage = function($connection, $data) {
        echo "收到消息: $data\n";
    };
    
    $connection->onError = function($connection, $code, $msg) {
        echo "错误: $code $msg\n";
    };
    
    $connection->onClose = function($connection) {
        echo "连接关闭\n";
    };
    
    // 建立连接
    $connection->connect();
};

Worker::runAll();
```

## 高级用法

### 强制使用DNS解析

即使是IP地址也可以强制使用DNS解析（在某些特殊场景下可能需要）：

```php
$connection = new DnsAsyncTcpConnection('ws://192.168.1.1:8080');
$connection->forceDnsLookup(true);
```

### 使用不同的缓存适配器

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

// 使用文件缓存
$cache = new FilesystemAdapter('dns', 300, '/tmp/dns-cache');
DnsAsyncTcpConnection::initDnsCache($cache);
```
