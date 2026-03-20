<?php
/**
 * Bot API Key 配置工具
 * 
 * 用于快速配置 Bot API Keys 到缓存系统
 * 访问：http://localhost/web/set_bot_api_key.php
 */

// Load ThinkPHP framework
define('APP_PATH', __DIR__ . '/../source/application/');
define('RUNTIME_PATH', __DIR__ . '/../source/runtime/');
require __DIR__ . '/../source/thinkphp/start.php';

use think\Cache;

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bot API Key 配置工具</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #1e1e1e;
            color: #d4d4d4;
        }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .info { color: #9cdcfe; }
        .code {
            background: #2d2d2d;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            word-break: break-all;
        }
        button {
            background: #0e639c;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 3px;
            font-size: 14px;
            margin: 5px;
        }
        button:hover {
            background: #1177bb;
        }
        input, select {
            background: #3c3c3c;
            color: #d4d4d4;
            border: 1px solid #555;
            padding: 8px;
            border-radius: 3px;
            margin: 5px 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #555;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #2d2d2d;
        }
    </style>
</head>
<body>
    <h2>🔧 Bot API Key 配置工具</h2>
    <hr>
    
    <?php
    try {
        // 处理表单提交
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $apiKey = $_POST['api_key'] ?? '';
            $botName = $_POST['bot_name'] ?? '';
            $wxappIds = $_POST['wxapp_ids'] ?? [];
            
            if ($apiKey) {
                // 保存到缓存
                $cacheData = [
                    'api_key' => $apiKey,
                    'name' => $botName,
                    'wxapp_ids' => empty($wxapp_ids) ? ['*'] : $wxapp_ids,
                    'is_valid' => true,
                    'created_time' => date('Y-m-d H:i:s'),
                ];
                
                Cache::set('bot_api_key:' . $apiKey, $cacheData, 86400 * 365); // 1 年有效期
                
                echo "<p class='success'>✅ API Key 配置成功！</p>";
                echo "<div class='code'>";
                echo "API Key: <strong>{$apiKey}</strong><br>";
                echo "Bot Name: {$botName}<br>";
                echo "Allowed Wxapp IDs: " . json_encode($cacheData['wxapp_ids']);
                echo "</div>";
            } else {
                echo "<p class='error'>❌ API Key 不能为空</p>";
            }
        }
        
        // 显示当前已配置的 API Keys
        echo "<h3>📋 当前已配置的 API Keys</h3>";
        
        // 注意：ThinkPHP Cache 没有直接的 keys() 方法，这里只是示例
        echo "<p class='info'>💡 提示：API Keys 存储在缓存中，前缀为 'bot_api_key:'</p>";
        
        ?>
        
        <h3>➕ 添加新的 API Key</h3>
        <form method="post">
            <table>
                <tr>
                    <td width="150"><label>API Key:</label></td>
                    <td>
                        <input type="text" name="api_key" required 
                               placeholder="例如：sk_test_1234567890abcdef"
                               style="width: 400px;">
                        <br><small>建议使用随机生成的字符串</small>
                    </td>
                </tr>
                <tr>
                    <td><label>Bot 名称:</label></td>
                    <td>
                        <input type="text" name="bot_name" required 
                               placeholder="例如：FB_Bot_Production"
                               style="width: 400px;">
                    </td>
                </tr>
                <tr>
                    <td><label>允许的商户 ID:</label></td>
                    <td>
                        <input type="checkbox" name="wxapp_ids[]" value="*" checked> 
                        <label>所有商户 (*)</label>
                        <br>
                        <input type="text" name="wxapp_ids_custom" 
                               placeholder="或输入特定 ID，多个用逗号分隔 (如：10001,10002)"
                               style="width: 400px;">
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="submit">💾 保存配置</button>
                        <button type="button" onclick="generateApiKey()">🎲 生成随机 API Key</button>
                    </td>
                </tr>
            </table>
        </form>
        
        <h3>📖 使用说明</h3>
        <div class="code">
            <h4>1. Bot API 调用方式:</h4>
            <pre>GET /api/bot/customer/verify?customer_id=CUST_123&platform=facebook
Headers:
  X-Bot-API-Key: your_api_key_here
  
POST /api/bot/package/create
Headers:
  Content-Type: application/json
  X-Bot-API-Key: your_api_key_here
Body:
{
  "package_code": "PKG123",
  "customer_id": "CUST_123",
  "weight": 1.5
}</pre>
            
            <h4>2. 多租户隔离:</h4>
            <pre>- 每个 API Key 可以限制只能访问特定的 wxapp_id
- 请求时传递 wxapp_id 参数
- 系统会自动验证权限</pre>
            
            <h4>3. 安全建议:</h4>
            <pre>- 生产环境启用 IP 白名单 (修改 BotIpCheck::WHITELIST_IPS)
- 使用 HTTPS 传输
- 定期更换 API Keys
- 为不同的 Bot 配置不同的 Keys</pre>
        </div>
        
        <h3>🧪 测试 API</h3>
        <div class="code">
            <p>使用 curl 测试:</p>
            <pre># 测试 Customer ID 验证
curl -X GET "http://localhost/web/index.php?s=/api/bot/customer/verify&wxapp_id=10001&customer_id=CUST_001&platform=facebook" \
  -H "X-Bot-API-Key: your_api_key_here"

# 测试包裹创建
curl -X POST "http://localhost/web/index.php?s=/api/bot/package/create" \
  -H "Content-Type: application/json" \
  -H "X-Bot-API-Key: your_api_key_here" \
  -d '{
    "wxapp_id": "10001",
    "package_code": "PKG_TEST_001",
    "customer_id": "CUST_001",
    "weight": 1.5
  }'</pre>
        </div>
        
    <?php
    } catch (\Exception $e) {
        echo "<p class='error'>❌ 错误：" . $e->getMessage() . "</p>";
    }
    ?>
    
    <script>
        function generateApiKey() {
            const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
            let apiKey = 'sk_test_';
            for (let i = 0; i < 24; i++) {
                apiKey += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.querySelector('input[name="api_key"]').value = apiKey;
        }
        
        // 处理自定义 wxapp_ids
        document.querySelector('input[name="wxapp_ids_custom"]').addEventListener('change', function(e) {
            const customValue = e.target.value.trim();
            const allCheckbox = document.querySelector('input[name="wxapp_ids[]"][value="*"]');
            
            if (customValue) {
                allCheckbox.checked = false;
                const ids = customValue.split(',').map(id => id.trim());
                
                // 清除其他选项
                document.querySelectorAll('input[name="wxapp_ids[]"]:not([value="*"])').forEach(cb => {
                    cb.remove();
                });
                
                // 添加自定义 ID
                ids.forEach(id => {
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'wxapp_ids[]';
                    checkbox.value = id;
                    checkbox.checked = true;
                    e.target.parentNode.insertBefore(checkbox, e.target.nextSibling);
                    e.target.parentNode.insertBefore(document.createTextNode(' ' + id + ' '), checkbox, e.target.nextSibling);
                });
            } else {
                allCheckbox.checked = true;
            }
        });
    </script>
</body>
</html>
