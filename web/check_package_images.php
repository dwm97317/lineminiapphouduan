<?php
/**
 * 检查包裹图片数据
 * 用于诊断为什么前端不显示图片
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
    // 连接数据库
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = [
        'status' => 'success',
        'checks' => []
    ];
    
    // 1. 检查 package_image 表是否存在
    $stmt = $pdo->query("SHOW TABLES LIKE 'yoshop_package_image'");
    $tableExists = $stmt->rowCount() > 0;
    $result['checks']['package_image_table_exists'] = $tableExists;
    
    if ($tableExists) {
        // 2. 检查 package_image 表结构
        $stmt = $pdo->query("DESCRIBE yoshop_package_image");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['checks']['package_image_columns'] = array_column($columns, 'Field');
        
        // 3. 统计 package_image 表记录数
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM yoshop_package_image");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        $result['checks']['package_image_total_records'] = (int)$count['total'];
        
        // 4. 获取最近10条 package_image 记录
        $stmt = $pdo->query("SELECT * FROM yoshop_package_image ORDER BY id DESC LIMIT 10");
        $recentImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['checks']['recent_package_images'] = $recentImages;
    }
    
    // 5. 检查 upload_file 表
    $stmt = $pdo->query("SHOW TABLES LIKE 'yoshop_upload_file'");
    $uploadTableExists = $stmt->rowCount() > 0;
    $result['checks']['upload_file_table_exists'] = $uploadTableExists;
    
    if ($uploadTableExists) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM yoshop_upload_file");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        $result['checks']['upload_file_total_records'] = (int)$count['total'];
    }
    
    // 6. 先检查 upload_file 表结构
    if ($uploadTableExists) {
        $stmt = $pdo->query("DESCRIBE yoshop_upload_file");
        $uploadColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['checks']['upload_file_columns'] = array_column($uploadColumns, 'Field');
    }
    
    // 7. 检查特定包裹的图片 (ID: 752124)
    $packageId = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 752124;
    $stmt = $pdo->prepare("
        SELECT pi.* 
        FROM yoshop_package_image pi 
        WHERE pi.package_id = ?
    ");
    $stmt->execute([$packageId]);
    $packageImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result['checks']['package_' . $packageId . '_images'] = $packageImages;
    
    // 8. 检查最近有图片的包裹
    if ($tableExists) {
        $stmt = $pdo->query("
            SELECT pi.package_id, COUNT(*) as image_count
            FROM yoshop_package_image pi
            GROUP BY pi.package_id
            ORDER BY pi.id DESC
            LIMIT 10
        ");
        $packagesWithImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['checks']['packages_with_images'] = $packagesWithImages;
    }
    
    // 9. 检查包裹表中是否有 images 字段
    $stmt = $pdo->query("DESCRIBE yoshop_package");
    $packageColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($packageColumns, 'Field');
    $result['checks']['package_has_images_column'] = in_array('images', $columnNames);
    $result['checks']['package_columns'] = $columnNames;
    
    // 10. 检查特定图片文件的详细信息
    $stmt = $pdo->query("
        SELECT file_id, storage, file_url, file_name, file_type, extension
        FROM yoshop_upload_file 
        WHERE file_id IN (603630, 603631, 602578, 602577, 602575)
    ");
    $fileDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result['checks']['file_details'] = $fileDetails;
    
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
