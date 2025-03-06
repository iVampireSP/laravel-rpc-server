# Laravel RPC Server

这个Laravel扩展包提供了一个基于Swoole的RPC服务器，集成了[Sajya/Server](https://github.com/sajya/server)包来处理JSON-RPC请求。

## 要求

- PHP >= 8.0
- Laravel >= 8.0
- Swoole扩展
- Sajya/Server包

## 安装

通过Composer安装：

```bash
composer require ivampiresp/laravel-rpc-server
```

## 使用方法

### 发布RPC引导文件

首先，发布RPC引导文件到您的应用程序的bootstrap目录：

```bash
php artisan rpc:publish
```

这将创建`bootstrap/rpc.php`文件，您可以在其中定义RPC路由和中间件。

### 发布示例Procedure

您可以发布示例Procedure类作为参考：

```bash
php artisan vendor:publish --provider="ivampiresp\LaravelRpcServer\RpcServerServiceProvider" --tag="rpc-examples"
```

这将在`app/Http/Procedures`目录中创建`ExampleProcedure.php`文件。

### 配置RPC路由

编辑`bootstrap/rpc.php`文件，添加您的RPC路由：

```php
use App\Http\Procedures\YourProcedure;
use Illuminate\Support\Facades\Route;

// ...

->withRouting(
    health: '/up',
    then: function () {
        Route::rpc('/api', [YourProcedure::class]);
    }
)
```

### 创建Procedure类

创建一个Procedure类来处理RPC请求：

```php
<?php

namespace App\Http\Procedures;

use Sajya\Server\Procedure;

class YourProcedure extends Procedure
{
    /**
     * 方法名称
     *
     * @var string
     */
    public static string $name = 'yourProcedure';

    /**
     * 示例方法
     *
     * @param string $message
     * @return string
     */
    public function hello(string $message): string
    {
        return "Hello, {$message}!";
    }
}
```

### 启动RPC服务器

使用以下命令启动RPC服务器：

```bash
php artisan rpc:serve
```

您可以指定主机和端口：

```bash
php artisan rpc:serve --host=0.0.0.0 --port=9000
```

## 测试RPC服务

您可以使用curl或其他HTTP客户端测试RPC服务：

```bash
curl -X POST http://localhost:8000/api \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"example.hello","params":{"name":"世界"},"id":1}'
```

预期响应：

```json
{"jsonrpc":"2.0","result":"你好，世界！","id":1}
```

## 许可证

MIT 
