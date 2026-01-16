<?php
/**
 * 测试包裹图片 API
 * 直接测试关联查询是否正常工作
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// 数据库配置
$config = [
    'host' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'port' => '3306',
    'charset' => 'utf8',
];

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 获取包裹ID列表
    $packageIds = [752124, 752123, 752121, 752120];
    
    $result = [
        'status' => 'success',
        'packages' => []
    ];
    
    foreach ($packageIds as $packageId) {
        // 1. 获取包裹基本信息
        $stmt = $pdo->prepare("SELECT id, express_num, status FROM yoshop_package WHERE id = ?");
        $stmt->execute([$packageId]);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$package) {
            $result['packages'][$packageId] = ['error' => 'Package not found'];
            continue;
        }
        
        // 2. 获取包裹图片
        $stmt = $pdo->prepare("
            SELECT pi.id, pi.package_id, pi.image_id, pi.wxapp_id
            FROM yoshop_package_image pi
            WHERE pi.package_id = ?
        ");
        $stmt->execute([$packageId]);
        $packageImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 3. 获取文件信息
        $images = [];
        foreach ($packageImages as $img) {
            $stmt = $pdo->prepare("
                SELECT file_id, storage, file_url, file_name
                FROM yoshop_upload_file
                WHERE file_id = ?
            ");
            $stmt->execute([$img['image_id']]);
            $file = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($file) {
                if ($file['storage'] === 'local') {
                    $filePath = '/uploads/' . $file['file_name'];
                } else {
                    $filePath = $file['file_url'] . '/' . $file['file_name'];
                }
                $images[] = [
                    'image_id' => $img['image_id'],
                    'file_path' => $filePath
                ];
            }
        }
        
        $result['packages'][$packageId] = [
            'package' => $package,
            'package_images_count' => count($packageImages),
            'package_images_raw' => $packageImages,
            'images' => $images
        ];
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
