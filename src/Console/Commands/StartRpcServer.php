<?php

namespace Ivampiresp\LaravelRpcServer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Swoole\HTTP\Server;
use Swoole\HTTP\Request as SwooleRequest;
use Swoole\HTTP\Response as SwooleResponse;

class StartRpcServer extends Command
{
    /**
     * 命令的名称和签名
     *
     * @var string
     */
    protected $signature = 'rpc:serve {--host=127.0.0.1} {--port=8000}';

    /**
     * 命令的描述
     *
     * @var string
     */
    protected $description = '启动RPC服务器';

    /**
     * 应用实例
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        // 检查是否安装了Swoole扩展
        if (!extension_loaded('swoole')) {
            $this->error('Swoole扩展未安装。请安装Swoole扩展后再试。');
            return 1;
        }

        // 检查bootstrap/rpc.php文件是否存在
        if (!file_exists(base_path('bootstrap/rpc.php'))) {
            $this->error('bootstrap/rpc.php文件不存在。请先运行 php artisan rpc:publish 命令发布该文件。');
            return 1;
        }

        $host = $this->option('host');
        $port = $this->option('port');

        $this->bootstrapRpcApplication();

        $server = new Server($host, $port);

        $server->on('request', function (SwooleRequest $swooleRequest, SwooleResponse $swooleResponse) {
            $this->handleRequest($swooleRequest, $swooleResponse);
        });

        $this->info("RPC服务器正在运行，地址：{$host}:{$port}");
        $server->start();

        return 0;
    }

    /**
     * 引导RPC应用程序
     *
     * @return void
     */
    protected function bootstrapRpcApplication()
    {
        $this->app = require base_path('bootstrap/rpc.php');

        $kernel = $this->app->make(Kernel::class);
        $kernel->bootstrap();
    }

    /**
     * 处理请求
     *
     * @param SwooleRequest $swooleRequest
     * @param SwooleResponse $swooleResponse
     * @return void
     */
    protected function handleRequest(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse)
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
