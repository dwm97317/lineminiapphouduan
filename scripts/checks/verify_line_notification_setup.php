<?php
/**
 * 验证 LINE 通知配置
 * 检查 LINE 消息模板是否已启用
 */

// 引入 ThinkPHP 框架
require_once __DIR__ . '/source/application/../thinkphp/base.php';

// 数据库配置
$config = [
    'type' => 'mysql',
    'hostname' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'hostport' => '3306',
    'charset' => 'utf8',
    'prefix' => 'yoshop_',
];

try {
    $db = \think\Db::connect($config);
    
    echo "=== LINE 通知配置检查 ===\n\n";
    
    // 1. 检查 line_messaging 配置
    $setting = $db->name('setting')
        ->where('key', 'line_messaging')
        ->find();
    
    if (!$setting) {
        echo "❌ 未找到 line_messaging 配置\n";
        exit;
    }
    
    $config = json_decode($setting['values'], true);
    
    echo "1. 全局配置:\n";
    echo "   - 是否启用: " . ($config['is_enable'] == '1' ? '✅ 是' : '❌ 否') . "\n";
    echo "   - Channel ID: " . ($config['channel_id'] ?? '未设置') . "\n";
    echo "   - Access Token: " . (isset($config['access_token']) && !empty($config['access_token']) ? '✅ 已设置' : '❌ 未设置') . "\n";
    echo "   - LIFF URL: " . ($config['liff_url'] ?? '未设置') . "\n\n";
    
    // 2. 检查模板配置
    echo "2. 消息模板配置:\n";
    
    $templates = [
        'inwarehouse' => '入库通知',
        'sendpack' => '发货通知'
    ];
    
    foreach ($templates as $key => $name) {
        echo "\n   【{$name}】:\n";
        
        if (!isset($config['templates'][$key])) {
            echo "   ❌ 模板不存在\n";
            continue;
        }
        
        $template = $config['templates'][$key];
        
        echo "   - 是否启用: " . (isset($template['is_enable']) && $template['is_enable'] == '1' ? '✅ 是' : '❌ 否') . "\n";
        echo "   - Alt Text: " . ($template['alt_text'] ?? '未设置') . "\n";
        echo "   - 发送图片: " . (isset($template['send_images']) && $template['send_images'] == '1' ? '✅ 是' : '❌ 否') . "\n";
        echo "   - Flex模板: " . (isset($template['flex_template']) ? '✅ 已设置' : '❌ 未设置') . "\n";
    }
    
    echo "\n\n=== 配置建议 ===\n";
    
    // 检查并给出建议
    $issues = [];
    
    if ($config['is_enable'] != '1') {
        $issues[] = "需要启用 LINE 消息通知全局开关";
    }
    
    if (empty($config['channel_id']) || empty($config['access_token'])) {
        $issues[] = "需要配置 Channel ID 和 Access Token";
    }
    
    foreach ($templates as $key => $name) {
        if (!isset($config['templates'][$key])) {
            $issues[] = "需要添加 {$name} 模板";
        } elseif ($config['templates'][$key]['is_enable'] != '1') {
            $issues[] = "需要启用 {$name} 模板";
        }
    }
    
    if (empty($issues)) {
        echo "✅ 配置完整，LINE 通知已准备就绪！\n";
    } else {
        echo "⚠️ 发现以下问题:\n";
        foreach ($issues as $i => $issue) {
            echo "   " . ($i + 1) . ". {$issue}\n";
        }
        echo "\n请访问后台 设置 > LINE配置 进行配置\n";
    }
    
    echo "\n=== 测试用户检查 ===\n";
    
    // 3. 检查是否有用户绑定了 LINE
    $lineUsers = $db->name('user')
        ->where('line_openid', '<>', '')
        ->whereNotNull('line_openid')
        ->count();
    
    echo "已绑定 LINE 的用户数: {$lineUsers}\n";
    
    if ($lineUsers == 0) {
        echo "⚠️ 没有用户绑定 LINE，无法发送通知\n";
    } else {
        echo "✅ 有用户已绑定，可以发送通知\n";
    }
    
} catch (\Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "堆栈: " . $e->getTraceAsString() . "\n";
}
