<?php
/**
 * 检查包裹数据结构
 */

define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\common\model\Package;

$data = Package::alias('a')
    ->field('a.*')
    ->with(['packageimage' => function($query) {
        $query->with('file');
    }])
    ->where(['express_num' => '31966asdsadas'])
    ->where('a.is_delete', 0)
    ->find();

if ($data) {
    echo "包裹数据:\n";
    echo "- ID: {$data['id']}\n";
    echo "- 尺寸 (size): " . ($data['size'] ?? 'NULL') . "\n";
    echo "- 唛头 (mark): " . ($data['mark'] ?? 'NULL') . "\n";
    echo "- 长 (length): " . ($data['length'] ?? 'NULL') . "\n";
    echo "- 宽 (width): " . ($data['width'] ?? 'NULL') . "\n";
    echo "- 高 (height): " . ($data['height'] ?? 'NULL') . "\n";
    echo "- 体积重 (volume_weight): " . ($data['volume_weight'] ?? 'NULL') . "\n";
    
    echo "\n图片数据:\n";
    if (!empty($data['packageimage'])) {
        foreach ($data['packageimage'] as $img) {
            echo "- 图片ID: {$img['id']}\n";
            if (isset($img['file'])) {
                echo "  文件路径: {$img['file']['file_path']}\n";
                echo "  外部路径: {$img['file']['external_url']}\n";
            }
        }
    } else {
        echo "无图片\n";
    }
    
    echo "\n完整数据:\n";
    print_r($data->toArray());
}
