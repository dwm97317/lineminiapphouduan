<?php
/**
 * CORS 跨域配置文件
 * 用于本地开发环境，允许前端 localhost:5173 访问后端 API
 * 
 * 使用方法：
 * 在 index.php 文件开头引入此文件：
 * require_once __DIR__ . '/cors-config.php';
 */

// 获取请求来源
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// 允许的来源列表（本地开发环境）
$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:3000',
    'http://127.0.0.1:5173',
    'http://127.0.0.1:3000',
];

// 检查来源是否在允许列表中
if (in_array($origin, $allowedOrigins)) {
    // 允许跨域请求
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // 缓存预检请求结果 24 小时
}

// 允许的请求方法
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// 允许的请求头
header('Access-Control-Allow-Headers: Content-Type, Authorization, platform, token, X-Requested-With');

// 处理 OPTIONS 预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // 预检请求直接返回 200
    http_response_code(200);
    exit();
}

// 开发环境：显示所有错误
if (strpos($origin, 'localhost') !== false || strpos($origin, '127.0.0.1') !== false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
