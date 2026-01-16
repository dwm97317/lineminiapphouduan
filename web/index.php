<?php
// 设置跨域
if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type,Apptype, Sign, timestamp, X-gron, platform");
    header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH');
    exit;
}
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
// 响应头设置
header('Access-Control-Allow-Headers:content-type,token,id');
header("Access-Control-Request-Headers: Content-Type,Apptype, Sign, timestamp, X-gron , platform");
// [ 应用入口文件 ]

// 定义运行目录
define('WEB_PATH', __DIR__ . '/');

// 定义应用目录
define('APP_PATH', WEB_PATH . '../source/application/');

// 加载框架引导文件
require APP_PATH . '../thinkphp/start.php';
