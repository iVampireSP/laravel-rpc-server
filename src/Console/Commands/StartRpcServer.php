<?php

namespace Ivampiresp\LaravelRpcServer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Swoole\HTTP\Server as SwooleServer;
use Swoole\HTTP\Request as SwooleRequest;
use Swoole\HTTP\Response as SwooleResponse;

class StartRpcServer extends Command
{
    protected $signature = 'rpc:serve {--host=127.0.0.1} {--port=8000}';
    protected $description = '启动RPC服务器';
    protected $app;

    public function handle()
    {
        if (!file_exists(base_path('bootstrap/rpc.php'))) {
            $this->error('bootstrap/rpc.php文件不存在。请先运行 php artisan rpc:publish 命令发布该文件。');
            return 1;
        }

        $host = $this->option('host');
        $port = $this->option('port');

        $this->bootstrapRpcApplication();

        if (extension_loaded('swoole')) {
            $this->startSwooleServer($host, $port);
        } elseif (app()->environment('local')) {
            $this->startBuiltInServer($host, $port);
        } else {
            $this->error('Swoole扩展未安装，且不在本地环境。无法启动服务器。');
            return 1;
        }

        return 0;
    }

    protected function bootstrapRpcApplication()
    {
        $this->app = require base_path('bootstrap/rpc.php');
        $kernel = $this->app->make(Kernel::class);
        $kernel->bootstrap();
    }

    protected function startSwooleServer($host, $port)
    {
        $server = new SwooleServer($host, $port);

        $server->on('request', function (SwooleRequest $swooleRequest, SwooleResponse $swooleResponse) {
            $this->handleSwooleRequest($swooleRequest, $swooleResponse);
        });

        $this->info("Swoole RPC服务器正在运行，地址：{$host}:{$port}");
        $server->start();
    }

    protected function startBuiltInServer($host, $port)
    {
        $this->info("PHP内置RPC服务器正在运行，地址：{$host}:{$port}");
        $this->info("注意：这只适用于开发环境，不建议在生产环境中使用。");

        $command = sprintf(
            'php -S %s:%d -t %s %s',
            $host,
            $port,
            public_path(),
            // 获取 package 路径下的 server.php
            base_path('vendor/ivampiresp/laravel-rpc-server/src/server.php')
        );

        passthru($command);
    }

    protected function handleSwooleRequest(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse)
    {
        $request = Request::create(
            $swooleRequest->server['request_uri'],
            $swooleRequest->server['request_method'],
            $swooleRequest->post ?? [],
            $swooleRequest->cookie ?? [],
            $swooleRequest->files ?? [],
            $swooleRequest->server ?? [],
            $swooleRequest->rawContent()
        );

        $response = $this->app->handle($request);

        $swooleResponse->status($response->getStatusCode());
        foreach ($response->headers->all() as $name => $values) {
            $swooleResponse->header($name, implode(', ', $values));
        }

        $swooleResponse->end($response->getContent());
    }
}
