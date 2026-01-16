<?php
// 检查LINE消息模板配置

$config = [
    'hostname' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'hostport' => '3306',
    'charset' => 'utf8mb4',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['hostname']};port={$config['hostport']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== LINE消息模板配置 ===\n\n";
    
    // 查询 yoshop_setting 表中的 line_messaging 配置
    $stmt = $pdo->query("
        SELECT `key`, `values`, `describe` 
        FROM yoshop_setting 
        WHERE `key` = 'line_messaging'
    ");
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "配置键: {$result['key']}\n";
        echo "描述: {$result['describe']}\n\n";
        
        $values = json_decode($result['values'], true);
        
        if ($values && isset($values['templates'])) {
            echo "=== 已配置的模板 ===\n\n";
            foreach ($values['templates'] as $key => $template) {
                echo "模板: $key\n";
                echo "  启用: " . ($template['enabled'] ? '是' : '否') . "\n";
                echo "  标题: {$template['title']}\n";
                if (!empty($template['variables'])) {
                    echo "  变量: " . implode(', ', array_keys($template['variables'])) . "\n";
                }
                echo "\n";
            }
        } else {
            echo "未找到模板配置\n";
        }
        
        // 检查是否有 inwarehouse 和 sendpack 模板
        echo "=== 关键模板检查 ===\n\n";
        $requiredTemplates = ['inwarehouse', 'sendpack'];
        foreach ($requiredTemplates as $tpl) {
            if (isset($values['templates'][$tpl])) {
                $status = $values['templates'][$tpl]['enabled'] ? '✓ 已启用' : '✗ 未启用';
                echo "$tpl: $status\n";
            } else {
                echo "$tpl: ✗ 未配置\n";
            }
        }
        
    } else {
        echo "未找到 line_messaging 配置\n";
    }
    
} catch (PDOException $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
