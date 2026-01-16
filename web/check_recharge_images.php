<?php
/**
 * 检查充值图片上传情况
 */

// 数据库配置
$config = [
    'host' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'port' => '3306',
    'charset' => 'utf8mb4',
];

echo "<h1>充值图片上传检查</h1>";

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p style='color: green;'>✅ 数据库连接成功</p>";
    
    // 1. 检查最近的certificate记录
    echo "<h2>1. 最近的Certificate记录 (最新5条)</h2>";
    $stmt = $pdo->query("
        SELECT id, cert_order, cert_price, cert_bank, cert_type, cert_date, user_id, wxapp_id, create_time 
        FROM yoshop_certificate 
        ORDER BY id DESC 
        LIMIT 5
    ");
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($certificates) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #4CAF50; color: white;'>";
        echo "<th>ID</th><th>订单号</th><th>金额</th><th>银行</th><th>类型</th><th>日期</th><th>用户ID</th><th>创建时间</th></tr>";
        foreach ($certificates as $cert) {
            echo "<tr>";
            echo "<td><strong>{$cert['id']}</strong></td>";
            echo "<td>{$cert['cert_order']}</td>";
            echo "<td>{$cert['cert_price']}</td>";
            echo "<td>{$cert['cert_bank']}</td>";
            echo "<td>{$cert['cert_type']}</td>";
            echo "<td>{$cert['cert_date']}</td>";
            echo "<td>{$cert['user_id']}</td>";
            echo "<td>{$cert['create_time']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 2. 对每个certificate检查关联的图片
        echo "<h2>2. Certificate关联的图片</h2>";
        foreach ($certificates as $cert) {
            echo "<h3>Certificate ID: {$cert['id']} (金额: {$cert['cert_price']})</h3>";
            
            $stmt = $pdo->prepare("
                SELECT ci.id, ci.cert_id, ci.image_id, ci.create_time,
                       uf.file_id, uf.file_name, uf.file_url, uf.storage, uf.file_size, uf.extension
                FROM yoshop_certificate_image ci
                LEFT JOIN yoshop_upload_file uf ON ci.image_id = uf.file_id
                WHERE ci.cert_id = ?
            ");
            $stmt->execute([$cert['id']]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($images) {
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
                echo "<tr style='background: #2196F3; color: white;'>";
                echo "<th>关联ID</th><th>Image ID</th><th>File ID</th><th>文件名</th><th>存储</th><th>大小</th><th>扩展名</th><th>创建时间</th></tr>";
                foreach ($images as $img) {
                    $highlight = empty($img['file_id']) ? 'background: #ffcccc;' : '';
                    echo "<tr style='$highlight'>";
                    echo "<td>{$img['id']}</td>";
                    echo "<td>{$img['image_id']}</td>";
                    echo "<td>" . ($img['file_id'] ?? '<span style="color:red;">NULL</span>') . "</td>";
                    echo "<td>" . ($img['file_name'] ?? '<span style="color:red;">NULL</span>') . "</td>";
                    echo "<td>" . ($img['storage'] ?? '<span style="color:red;">NULL</span>') . "</td>";
                    echo "<td>" . ($img['file_size'] ?? '<span style="color:red;">NULL</span>') . "</td>";
                    echo "<td>" . ($img['extension'] ?? '<span style="color:red;">NULL</span>') . "</td>";
                    echo "<td>{$img['create_time']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // 检查文件是否实际存在
                echo "<h4>文件存在性检查:</h4>";
                foreach ($images as $img) {
                    if ($img['file_name']) {
                        $filePath = __DIR__ . '/' . $img['file_name'];
                        $exists = file_exists($filePath);
                        $color = $exists ? 'green' : 'red';
                        $status = $exists ? '✅ 存在' : '❌ 不存在';
                        echo "<p style='color: $color;'>$status: {$img['file_name']}</p>";
                        echo "<p style='margin-left: 20px; font-size: 11px;'>检查路径: $filePath</p>";
                        if ($exists) {
                            $actualSize = filesize($filePath);
                            echo "<p style='margin-left: 20px;'>实际大小: " . number_format($actualSize) . " bytes</p>";
                        }
                    }
                }
            } else {
                echo "<p style='color: orange;'>⚠️ 没有关联的图片</p>";
            }
        }
    } else {
        echo "<p style='color: orange;'>⚠️ 没有找到certificate记录</p>";
    }
    
    // 3. 检查最近上传的文件
    echo "<h2>3. 最近上传的文件 (最新10条)</h2>";
    $stmt = $pdo->query("
        SELECT file_id, storage, file_name, file_url, file_size, file_type, extension, is_user, wxapp_id, create_time
        FROM yoshop_upload_file 
        ORDER BY file_id DESC 
        LIMIT 10
    ");
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($files) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #4CAF50; color: white;'>";
        echo "<th>File ID</th><th>存储</th><th>文件名</th><th>URL</th><th>大小</th><th>类型</th><th>扩展名</th><th>is_user</th><th>创建时间</th></tr>";
        foreach ($files as $file) {
            echo "<tr>";
            echo "<td><strong>{$file['file_id']}</strong></td>";
            echo "<td>{$file['storage']}</td>";
            echo "<td style='font-size: 11px; word-break: break-all;'>{$file['file_name']}</td>";
            echo "<td>{$file['file_url']}</td>";
            echo "<td>" . number_format($file['file_size']) . "</td>";
            echo "<td>{$file['file_type']}</td>";
            echo "<td>{$file['extension']}</td>";
            echo "<td>{$file['is_user']}</td>";
            echo "<td>{$file['create_time']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. 检查uploads/recharge目录
    echo "<h2>4. uploads/recharge 目录检查</h2>";
    $rechargeDir = __DIR__ . '/uploads/recharge';
    echo "<p>检查目录: $rechargeDir</p>";
    if (is_dir($rechargeDir)) {
        echo "<p style='color: green;'>✅ 目录存在</p>";
        
        // 列出所有日期目录
        $dateDirs = glob($rechargeDir . '/*', GLOB_ONLYDIR);
        if ($dateDirs) {
            echo "<h3>日期目录:</h3>";
            foreach ($dateDirs as $dateDir) {
                $dirName = basename($dateDir);
                $files = glob($dateDir . '/*');
                $fileCount = count($files);
                echo "<p><strong>$dirName</strong>: $fileCount 个文件</p>";
                if ($fileCount > 0) {
                    echo "<ul>";
                    foreach ($files as $file) {
                        $fileName = basename($file);
                        $fileSize = filesize($file);
                        echo "<li>$fileName (" . number_format($fileSize) . " bytes)</li>";
                    }
                    echo "</ul>";
                }
            }
        } else {
            echo "<p style='color: orange;'>⚠️ 没有日期子目录</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ 目录不存在</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ 错误: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1400px;
    margin: 20px auto;
    padding: 20px;
    background: #f5f5f5;
}
h1, h2, h3 {
    color: #333;
    border-bottom: 2px solid #4CAF50;
    padding-bottom: 10px;
}
table {
    background: white;
    margin: 20px 0;
    font-size: 12px;
}
th {
    padding: 10px;
    text-align: left;
}
td {
    padding: 8px;
}
tr:nth-child(even) {
    background: #f9f9f9;
}
</style>
