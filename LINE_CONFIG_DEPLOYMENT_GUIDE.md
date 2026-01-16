# LINE 配置增强功能部署指南

## 部署前检查

### 1. 文件清单
确认以下文件已正确部署：

**模型层**
- `source/application/common/model/Setting.php` (已更新)

**服务层**
- `source/application/common/service/message/line/Basics.php` (新建)
- `source/application/common/service/message/line/Inwarehouse.php` (新建)
- `source/application/common/service/message/line/Sendpack.php` (新建)
- `source/application/common/service/message/line/Payment.php` (新建)
- `source/application/common/service/message/line/Dabaosuccess.php` (新建)
- `source/application/common/service/message/line/Payorder.php` (新建)
- `source/application/common/service/message/line/Toshop.php` (新建)
- `source/application/common/service/message/line/Outapply.php` (新建)
- `source/application/common/service/Message.php` (已更新)

**库文件**
- `source/application/common/library/line/LineMessage.php` (新建)

**控制器**
- `source/application/store/controller/setting/LineConfig.php` (已更新)

**视图**
- `source/application/store/view/setting/line_config/index.php` (已更新)

### 2. 依赖检查
- PHP >= 7.0
- cURL 扩展已启用
- ThinkPHP 5.x 框架

## 部署步骤

### 步骤 1: 上传文件
将所有更新的文件上传到服务器对应目录。

### 步骤 2: 清除缓存
```bash
# 清除 ThinkPHP 缓存
rm -rf runtime/cache/*
rm -rf runtime/temp/*
```

### 步骤 3: 配置 LINE Messaging API

1. 访问后台配置页面：
   ```
   https://your-domain.com/store/setting.line_config/index
   ```

2. 切换到"消息通知 (Messaging API)"标签页

3. 填写基础配置：
   - **启用消息通知**: 选择"启用"
   - **Channel ID**: 从 LINE Developers Console 获取
   - **Channel Secret**: 从 LINE Developers Console 获取
   - **Access Token**: 从 LINE Developers Console 获取（长期有效的 Channel Access Token）
   - **LIFF URL**: 你的 LIFF 应用 URL（例如：`https://liff.line.me/1234567890-abcdefgh`）

4. 配置 API 设置（可选，使用默认值即可）：
   - **API Base URL**: `https://api.line.me/v2/bot` (默认)
   - **超时时间**: 30 秒 (默认)
   - **重试次数**: 3 次 (默认)
   - **启用日志**: 是 (默认)

5. 配置消息模板：
   - 为每个需要的消息类型启用模板
   - 可以自定义"替代文本"和"优先级"
   - 点击"发送测试"按钮测试每个模板

6. 点击"提交保存"

### 步骤 4: 测试消息发送

#### 方法 1: 使用后台测试功能
1. 在配置页面，找到任意消息模板
2. 点击"发送测试"按钮
3. 输入测试用户的 LINE User ID
4. 检查 LINE 应用是否收到测试消息

#### 方法 2: 触发业务事件
1. 执行一个会触发消息的业务操作（如包裹入库）
2. 检查 LINE 应用是否收到通知
3. 检查日志文件确认发送状态

### 步骤 5: 检查日志
查看日志文件确认消息发送状态：
```bash
tail -f runtime/log/$(date +%Y%m%d).log | grep "LINE消息"
```

## 获取 LINE Messaging API 配置

### 1. 访问 LINE Developers Console
https://developers.line.biz/console/

### 2. 创建或选择 Provider

### 3. 创建 Messaging API Channel
- 点击"Create a new channel"
- 选择"Messaging API"
- 填写必要信息并创建

### 4. 获取配置信息

**Channel ID 和 Channel Secret**
- 在 Channel 的"Basic settings"标签页中找到

**Channel Access Token**
- 在"Messaging API"标签页中
- 找到"Channel access token (long-lived)"
- 点击"Issue"生成长期有效的 Token
- 复制 Token（只显示一次，请妥善保存）

**LIFF URL**
- 如果已创建 LIFF 应用，在"LIFF"标签页中找到 LIFF URL
- 格式：`https://liff.line.me/[LIFF_ID]`

### 5. 配置 Webhook（可选）
如果需要接收用户消息：
- 在"Messaging API"标签页中设置 Webhook URL
- 启用"Use webhook"

## 集成到业务代码

### 示例 1: 包裹入库通知
在包裹入库的业务逻辑中添加：

```php
use app\common\service\Message;

// 包裹入库成功后
Message::send('package.inwarehouse', [
    'wxapp_id' => $this->wxapp_id,
    'member_id' => $package['user_id'],
    'shop_name' => $warehouse['name'],
    'express_num' => $package['express_num'],
    'entering_warehouse_time' => $package['entering_warehouse_time'],
    'weight' => $package['weight'],
    'remark' => $package['remark'],
    'id' => $package['id']
]);
```

### 示例 2: 发货通知
在订单发货的业务逻辑中添加：

```php
Message::send('package.sendpack', [
    'wxapp_id' => $this->wxapp_id,
    'member_id' => $order['user_id'],
    'order_sn' => $order['order_sn'],
    't_order_sn' => $order['t_order_sn'],
    'weight' => $order['weight'],
    't_name' => $order['t_name'],
    'send_time' => date('Y-m-d H:i:s')
]);
```

### 示例 3: 支付成功通知
在支付回调中添加：

```php
Message::send('package.payment', [
    'wxapp_id' => $this->wxapp_id,
    'member_id' => $order['user_id'],
    'order_sn' => $order['order_sn'],
    'total_free' => $order['total_free'],
    'pay_time' => date('Y-m-d H:i:s'),
    'remark' => '感谢您的支付',
    'order_id' => $order['id']
]);
```

## 故障排查

### 问题 1: 消息发送失败
**检查项**：
1. 确认 LINE Messaging API 配置正确
2. 检查 Access Token 是否有效
3. 查看日志文件中的错误信息
4. 确认用户的 `line_user_id` 字段已正确保存

**解决方法**：
```bash
# 查看详细错误日志
tail -f runtime/log/$(date +%Y%m%d).log | grep "LINE"
```

### 问题 2: 用户未收到消息
**检查项**：
1. 确认用户已关注 LINE 官方账号
2. 确认用户的 LINE User ID 正确
3. 检查消息模板是否已启用
4. 查看 LINE Developers Console 中的错误日志

### 问题 3: 深层链接无法跳转
**检查项**：
1. 确认 LIFF URL 配置正确
2. 确认 LIFF 应用已正确设置
3. 检查链接格式是否正确

### 问题 4: 配置保存失败
**检查项**：
1. 检查数据库连接
2. 确认 `yoshop_setting` 表存在
3. 查看 PHP 错误日志

## 性能优化建议

### 1. 异步发送
对于高并发场景，建议使用队列异步发送消息：

```php
// 使用 ThinkPHP 队列
use think\Queue;

Queue::push('app\job\SendLineMessage', [
    'scene' => 'package.inwarehouse',
    'data' => $messageData
]);
```

### 2. 批量发送
如果需要向多个用户发送相同消息，可以使用 LINE 的 Multicast API（需要修改 LineMessage.php）。

### 3. 缓存配置
配置信息会频繁读取，建议启用缓存：

```php
// 在 Setting.php 中添加缓存
$config = cache('line_messaging_' . $wxappId);
if (!$config) {
    $config = SettingModel::getItem('line_messaging', $wxappId);
    cache('line_messaging_' . $wxappId, $config, 3600);
}
```

## 监控和维护

### 1. 日志监控
定期检查日志文件，关注：
- 消息发送失败率
- API 响应时间
- 错误类型分布

### 2. 配额监控
LINE Messaging API 有配额限制，需要监控：
- 每月免费消息数量
- 当前使用量
- 超额费用

### 3. 定期测试
建议每周执行一次完整的消息发送测试，确保所有模板正常工作。

## 安全建议

1. **保护 Access Token**
   - 不要在前端代码中暴露 Token
   - 定期更换 Token
   - 使用环境变量存储敏感信息

2. **验证用户身份**
   - 确认消息只发送给授权用户
   - 验证 LINE User ID 的有效性

3. **限流保护**
   - 实现发送频率限制
   - 防止恶意触发大量消息

## 支持和文档

- LINE Messaging API 文档: https://developers.line.biz/en/docs/messaging-api/
- Flex Message Simulator: https://developers.line.biz/flex-simulator/
- LINE Developers Console: https://developers.line.biz/console/

## 联系方式

如有问题，请联系技术支持团队。
