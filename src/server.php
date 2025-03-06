<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

// 获取应用实例
// 由于laravel还没有加载，没有base_path等函数，我们需要手动指定rpc.php的路径
$app = require_once __DIR__ . '/../../../../bootstrap/rpc.php';


// 创建请求
$request = Illuminate\Http\Request::capture();

// 处理请求
$response = $app->handle($request);

// 发送响应
$response->send();

// 终止应用
$app->terminate();
