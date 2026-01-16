<?php
/**
 * 测试充值路径是否正确
 */

// 定义路径常量
define('APP_PATH', __DIR__ . '/source/application/');
define('ROOT_PATH', __DIR__ . '/');

echo "<h1>测试充值文件路径</h1>";

// 测试路径
$uploadPath = 'uploads/recharge/' . date('Ymd') . '/';
$fullPath = ROOT_PATH . 'web/' . $uploadPath;

echo "<h2>路径信息</h2>";
echo "<p><strong>ROOT_PATH:</strong> " . ROOT_PATH . "</p>";
echo "<p><strong>Upload Path:</strong> " . $uploadPath . "</p>";
echo "<p><strong>Full Path:</strong> " . $fullPath . "</p>";

// 检查目录是否存在
if (is_dir($fullPath)) {
    echo "<p style='color: green;'>✅ 目录已存在</p>";
} else {
    echo "<p style='color: orange;'>⚠️ 目录不存在，尝试创建...</p>";
    
    if (mkdir($fullPath, 0755, true)) {
        echo "<p style='color: green;'>✅ 目录创建成功</p>";
    } else {
        echo "<p style='color: red;'>❌ 目录创建失败</p>";
    }
}

// 测试写入文件
$testFile = $fullPath . 'test_' . time() . '.txt';
$testContent = 'Test recharge upload at ' . date('Y-m-d H:i:s');

echo "<h2>测试文件写入</h2>";
echo "<p><strong>Test File:</strong> " . $testFile . "</p>";

if (file_put_contents($testFile, $testContent)) {
    echo "<p style='color: green;'>✅ 文件写入成功</p>";
    
    // 读取文件验证
    $readContent = file_get_contents($testFile);
    echo "<p><strong>File Content:</strong> " . htmlspecialchars($readContent) . "</p>";
    
    // 删除测试文件
    unlink($testFile);
    echo "<p style='color: blue;'>🗑️ 测试文件已删除</p>";
} else {
    echo "<p style='color: red;'>❌ 文件写入失败</p>";
    echo "<p>请检查目录权限</p>";
}

// 测试Base64图片保存
echo "<h2>测试Base64图片保存</h2>";

$testImageBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

if (preg_match('/^data:image\/(\w+);base64,/', $testImageBase64, $type)) {
    $imageData = substr($testImageBase64, strpos($testImageBase64, ',') + 1);
    $imageType = strtolower($type[1]);
    
    echo "<p><strong>Image Type:</strong> " . $imageType . "</p>";
    
    $imageData = base64_decode($imageData);
    if ($imageData !== false) {
        $fileName = 'test_' . time() . '.' . $imageType;
        $filePath = $fullPath . $fileName;
        
        if (file_put_contents($filePath, $imageData)) {
            echo "<p style='color: green;'>✅ 图片保存成功</p>";
            echo "<p><strong>File Path:</strong> " . $filePath . "</p>";
            echo "<p><strong>File Size:</strong> " . filesize($filePath) . " bytes</p>";
            
            // 显示图片
            $webPath = '/uploads/recharge/' . date('Ymd') . '/' . $fileName;
            echo "<p><strong>Web Path:</strong> " . $webPath . "</p>";
            echo "<img src='" . $webPath . "' alt='Test Image' style='border: 1px solid #ccc; padding: 10px;' />";
            
            // 删除测试图片
            unlink($filePath);
            echo "<p style='color: blue;'>🗑️ 测试图片已删除</p>";
        } else {
            echo "<p style='color: red;'>❌ 图片保存失败</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Base64解码失败</p>";
    }
} else {
    echo "<p style='color: red;'>❌ 无效的Base64图片格式</p>";
}

echo "<h2>✅ 路径测试完成</h2>";
echo "<p>如果所有测试都通过，说明充值API的文件上传功能可以正常工作。</p>";
?>
