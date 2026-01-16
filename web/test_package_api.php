<?php
/**
 * 测试包裹 API 返回的图片数据 - 直接数据库查询版本
 */

// 数据库配置
$config = [
    'host' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'port' => '3306',
    'charset' => 'utf8',
];

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $packageId = isset($_GET['id']) ? (int)$_GET['id'] : 752124;
    $memberId = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 31966;
    
    $result = [
        'status' => 'success',
        'analysis' => []
    ];
    
    // 1. 检查包裹是否存在
    $stmt = $pdo->prepare("SELECT id, order_sn, express_num, member_id, status FROM yoshop_package WHERE id = ?");
    $stmt->execute([$packageId]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    $result['analysis']['package'] = $package;
    
    // 2. 检查 package_image 表中是否有该包裹的图片
    $stmt = $pdo->prepare("SELECT * FROM yoshop_package_image WHERE package_id = ?");
    $stmt->execute([$packageId]);
    $packageImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result['analysis']['package_image_records'] = $packageImages;
    $result['analysis']['package_image_count'] = count($packageImages);
    
    // 3. 检查 upload_file 表中的文件信息
    if (!empty($packageImages)) {
        $imageIds = array_column($packageImages, 'image_id');
        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $stmt = $pdo->prepare("SELECT * FROM yoshop_upload_file WHERE file_id IN ($placeholders)");
        $stmt->execute($imageIds);
        $uploadFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['analysis']['upload_file_records'] = $uploadFiles;
        
        // 4. 构建完整的图片 URL
        $fullImageUrls = [];
        foreach ($uploadFiles as $file) {
            if ($file['storage'] === 'local') {
                $fullImageUrls[] = 'uploads/' . $file['file_name'];
            } else {
                $fullImageUrls[] = $file['file_url'] . '/' . $file['file_name'];
            }
        }
        $result['analysis']['full_image_urls'] = $fullImageUrls;
    }
    
    // 5. 诊断问题
    $diagnosis = [];
    
    if (empty($package)) {
        $diagnosis[] = "❌ 包裹 ID {$packageId} 不存在";
    } else {
        $diagnosis[] = "✅ 包裹存在: {$package['order_sn']}";
    }
    
    if (empty($packageImages)) {
        $diagnosis[] = "❌ yoshop_package_image 表中没有该包裹的图片记录";
    } else {
        $diagnosis[] = "✅ yoshop_package_image 表中有 " . count($packageImages) . " 条图片记录";
    }
    
    if (!empty($packageImages) && empty($uploadFiles)) {
        $diagnosis[] = "❌ yoshop_upload_file 表中找不到对应的文件记录";
    } elseif (!empty($uploadFiles)) {
        $diagnosis[] = "✅ yoshop_upload_file 表中有 " . count($uploadFiles) . " 条文件记录";
    }
    
    if (!empty($fullImageUrls)) {
        $diagnosis[] = "✅ 图片 URL 已成功构建";
        $diagnosis[] = "🔍 问题可能在后端 API 代码的关联查询或数据处理逻辑";
    }
    
    $result['diagnosis'] = $diagnosis;
    
    // 6. 建议
    $result['suggestion'] = "数据库中有图片数据，但 API 返回空数组。问题在于后端 ThinkPHP 的关联查询。可能原因：\n" .
        "1. PackageImage 模型的 file() 关联没有正确加载\n" .
        "2. UploadFile 模型的 getFilePathAttr 计算属性没有被触发\n" .
        "3. 需要检查 ThinkPHP 的 with() 关联预加载是否正确工作";
    
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
