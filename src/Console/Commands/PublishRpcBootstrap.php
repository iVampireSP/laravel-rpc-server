<?php

namespace Ivampiresp\LaravelRpcServer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishRpcBootstrap extends Command
{
    /**
     * 命令的名称和签名
     *
     * @var string
     */
    protected $signature = 'rpc:publish';

    /**
     * 命令的描述
     *
     * @var string
     */
    protected $description = '发布RPC引导文件到bootstrap目录';

    /**
     * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        // 检查bootstrap目录是否存在
        if (!File::isDirectory(base_path('bootstrap'))) {
            $this->error('bootstrap目录不存在。');
            return 1;
        }

        // 发布rpc.php文件
        $source = __DIR__ . '/../../stubs/rpc.php.stub';
        $destination = base_path('bootstrap/rpc.php');

        if (File::exists($destination) && !$this->confirm('bootstrap/rpc.php文件已存在。是否覆盖？')) {
            $this->info('操作已取消。');
            return 0;
        }

        File::copy($source, $destination);
        $this->info('RPC引导文件已成功发布到bootstrap/rpc.php。');

        return 0;
    }
}
