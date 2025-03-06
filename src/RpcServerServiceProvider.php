<?php

namespace Ivampiresp\LaravelRpcServer;

use Illuminate\Support\ServiceProvider;
use Ivampiresp\LaravelRpcServer\Console\Commands\StartRpcServer;
use Ivampiresp\LaravelRpcServer\Console\Commands\PublishRpcBootstrap;

class RpcServerServiceProvider extends ServiceProvider
{
    /**
     * 在注册后启动服务
     *
     * @return void
     */
    public function boot()
    {
        // 注册命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                StartRpcServer::class,
                PublishRpcBootstrap::class,
            ]);

            // 发布RPC引导文件
            $this->publishes([
                __DIR__ . '/stubs/rpc.php.stub' => base_path('bootstrap/rpc.php'),
            ], 'rpc-bootstrap');

            // 发布示例Procedure
            $this->publishes([
                __DIR__ . '/stubs/ExampleProcedure.php.stub' => app_path('Http/Procedures/ExampleProcedure.php'),
            ], 'rpc-examples');
        }
    }

    /**
     * 注册服务提供者
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
