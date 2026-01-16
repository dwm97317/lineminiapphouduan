# Design Document: LINE消息通知自动触发集成

## Overview

本设计实现LINE消息通知系统与业务流程的自动集成，通过在关键业务节点（包裹入库、订单发货、支付完成等）插入通知触发逻辑，实现用户状态变更的实时通知。

设计采用事件驱动架构，在业务操作完成后异步触发通知发送，确保不影响主业务流程的性能。

## Architecture

### 系统架构

```
┌─────────────────┐
│  业务层         │
│  (Controllers)  │
└────────┬────────┘
         │ 业务操作
         ▼
┌─────────────────┐
│  模型层         │
│  (Models)       │
└────────┬────────┘
         │ 状态变更后
         ▼
┌─────────────────┐
│  通知触发层     │
│  (Triggers)     │
└────────┬────────┘
         │ 调用
         ▼
┌─────────────────┐
│  消息服务层     │
│  (Message       │
│   Services)     │
└────────┬────────┘
         │ 发送
         ▼
┌─────────────────┐
│  LINE API       │
└─────────────────┘
```

### 数据流

```
业务操作 → 数据库更新 → 触发通知 → 获取用户LINE ID → 
验证好友关系 → 渲染消息模板 → 获取图片(可选) → 发送到LINE API → 记录日志
```

## Components and Interfaces

### 0. LINE API扩展 - 用户资料获取

在LineMessage.php中添加获取用户资料的方法，用于验证好友关系。

```php
// 位置: source/application/common/library/line/LineMessage.php

/**
 * 获取用户资料
 * 注意：只有当用户是LINE OA的好友时，才能成功获取资料
 * @param string $userId LINE User ID
 * @return array|false 用户资料或false
 */
public function getUserProfile($userId)
{
    $url = $this->apiBaseUrl . "/v2/bot/profile/{$userId}";
    
    $headers = [
        'Authorization: Bearer ' . $this->accessToken,
        'Content-Type: application/json'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    // 如果返回404或403，说明用户不是好友
    return false;
}
```

### 1. 通知触发器 (Notification Triggers)

在业务模型的关键方法中插入通知触发逻辑。

#### 包裹入库触发器
```php
// 位置: source/application/api/model/Package.php 或相关控制器
// 在包裹入库方法中添加

public function inWarehouse($packageData) {
    // 原有业务逻辑
    $result = $this->save($packageData);
    
    if ($result) {
        // 触发入库通知
        $this->triggerInwarehouseNotification($this->id);
    }
    
    return $result;
}

protected function triggerInwarehouseNotification($packageId) {
    try {
        // 获取包裹完整信息（包含图片）
        $package = $this->with(['packageimage.file', 'shop'])->find($packageId);
        
        if (!$package || !$package['member_id']) {
            return false;
        }
        
        // 准备通知数据
        $data = [
            'wxapp_id' => $package['wxapp_id'],
            'member_id' => $package['member_id'],
            'id' => $package['id'],
            'shop_name' => $package['shop']['shop_name'] ?? '',
            'express_num' => $package['express_num'],
            'entering_warehouse_time' => $package['entering_warehouse_time'],
            'weight' => $package['weight'],
            'remark' => $package['remark'] ?? '包裹已入库',
        ];
        
        // 添加图片数据
        if (!empty($package['packageimage'])) {
            $data['packageimage'] = $package['packageimage']->toArray();
        }
        
        // 异步发送通知
        $messageService = new \app\common\service\message\line\Inwarehouse();
        $messageService->send($data);
        
    } catch (\Exception $e) {
        // 记录错误但不影响业务流程
        log_write([
            'describe' => 'LINE入库通知触发失败',
            'package_id' => $packageId,
            'error' => $e->getMessage(),
            'time' => date('Y-m-d H:i:s')
        ]);
    }
}
```

#### 发货通知触发器
```php
// 位置: source/application/api/model/Inpack.php 或相关控制器

public function markAsShipped($orderId, $trackingInfo) {
    // 原有业务逻辑
    $result = $this->where('id', $orderId)->update([
        'status' => 20, // 已发货
        't_order_sn' => $trackingInfo['tracking_number'],
        'send_time' => time(),
    ]);
    
    if ($result) {
        // 触发发货通知
        $this->triggerSendpackNotification($orderId);
    }
    
    return $result;
}

protected function triggerSendpackNotification($orderId) {
    try {
        $order = $this->find($orderId);
        
        if (!$order || !$order['member_id']) {
            return false;
        }
        
        $data = [
            'wxapp_id' => $order['wxapp_id'],
            'member_id' => $order['member_id'],
            'order_sn' => $order['order_sn'],
            't_order_sn' => $order['t_order_sn'],
            'weight' => $order['weight'],
            't_name' => $order['line']['name'] ?? '',
            'send_time' => date('Y-m-d H:i:s', $order['send_time']),
            'tracking_url' => $this->buildTrackingUrl($order['t_order_sn']),
        ];
        
        $messageService = new \app\common\service\message\line\Sendpack();
        $messageService->send($data);
        
    } catch (\Exception $e) {
        log_write([
            'describe' => 'LINE发货通知触发失败',
            'order_id' => $orderId,
            'error' => $e->getMessage(),
            'time' => date('Y-m-d H:i:s')
        ]);
    }
}
```

### 2. LINE OpenID字段适配与好友关系验证

修改Basics.php中的用户ID获取方法，支持line_openid字段，并添加好友关系验证。

#### 2.1 用户LINE ID获取

```php
// 位置: source/application/common/service/message/line/Basics.php

protected function getLineUserIdByUserId($userId)
{
    $user = User::where(['user_id' => $userId])->find();
    
    if (!$user) {
        return null;
    }
    
    // 优先使用 line_openid 字段
    if (!empty($user['line_openid'])) {
        return $user['line_openid'];
    }
    
    // 兼容旧字段 line_user_id
    if (!empty($user['line_user_id'])) {
        return $user['line_user_id'];
    }
    
    return null;
}
```

#### 2.2 好友关系验证

```php
// 位置: source/application/common/service/message/line/Basics.php

/**
 * 验证用户是否已添加LINE OA为好友
 * @param string $lineUserId LINE User ID
 * @param int $wxappId 小程序ID
 * @return bool
 */
protected function isFriendWithOA($lineUserId, $wxappId)
{
    // 检查缓存
    $cacheKey = "line_friendship_{$wxappId}_{$lineUserId}";
    $cached = cache($cacheKey);
    
    if ($cached !== null) {
        return $cached === 'yes';
    }
    
    try {
        // 获取 LINE 配置
        $config = SettingModel::getItem('line_messaging', $wxappId);
        
        if (empty($config['channel_id']) || empty($config['access_token'])) {
            return false;
        }
        
        // 创建 LINE 消息实例
        $lineMessage = new LineMessage(
            $config['channel_id'],
            $config['channel_secret'] ?? '',
            $config['access_token']
        );
        
        // 调用 LINE API 获取用户资料（如果用户是好友才能获取）
        $profile = $lineMessage->getUserProfile($lineUserId);
        
        if ($profile && isset($profile['userId'])) {
            // 用户是好友，缓存24小时
            cache($cacheKey, 'yes', 86400);
            return true;
        } else {
            // 用户不是好友，缓存1小时（较短时间，因为用户可能随时添加）
            cache($cacheKey, 'no', 3600);
            return false;
        }
        
    } catch (\Exception $e) {
        // API调用失败，记录日志
        log_write([
            'describe' => 'LINE好友关系验证失败',
            'wxapp_id' => $wxappId,
            'line_user_id' => $lineUserId,
            'error' => $e->getMessage(),
            'time' => date('Y-m-d H:i:s')
        ]);
        
        // 验证失败时，假设不是好友（安全策略）
        return false;
    }
}
```

#### 2.3 更新sendLineFlexMsg方法

在发送消息前添加好友关系验证：

```php
protected function sendLineFlexMsg($wxappId, $userId, $messageType, $data)
{
    try {
        // 获取 LINE 消息配置
        $config = SettingModel::getItem('line_messaging', $wxappId);
        
        // 检查是否启用
        if (empty($config['is_enable']) || $config['is_enable'] != '1') {
            $this->logMessageSend($wxappId, $userId, $messageType, false, '全局未启用');
            return false;
        }
        
        // 检查该消息类型是否启用
        if (empty($config['templates'][$messageType]) || 
            $config['templates'][$messageType]['is_enable'] != '1') {
            $this->logMessageSend($wxappId, $userId, $messageType, false, '模板未启用');
            return false;
        }
        
        // *** 新增：验证好友关系 ***
        if (!$this->isFriendWithOA($userId, $wxappId)) {
            $this->logMessageSend($wxappId, $userId, $messageType, false, '用户未添加LINE OA为好友');
            return false;
        }
        
        // ... 继续原有的发送逻辑 ...
    } catch (\Exception $e) {
        // 错误处理
    }
}
```

### 3. 消息服务类扩展

为每种消息类型创建对应的服务类（如果不存在）。

#### Sendpack.php (发货通知)
```php
// 位置: source/application/common/service/message/line/Sendpack.php

namespace app\common\service\message\line;

class Sendpack extends Basics
{
    protected $param = [];
    
    public function send($param)
    {
        $this->param = $param;
        return $this->onSendLineMsg();
    }
    
    private function onSendLineMsg()
    {
        $orderInfo = $this->param;
        $wxappId = $orderInfo['wxapp_id'];
        
        $lineUserId = $this->getLineUserIdByUserId($orderInfo['member_id']);
        if (empty($lineUserId)) {
            return false;
        }
        
        $detailUrl = $this->buildLiffUrl(
            '/order/detail',
            ['order_sn' => $orderInfo['order_sn']],
            $wxappId
        );
        
        $data = [
            'order_sn' => $orderInfo['order_sn'] ?? '',
            't_order_sn' => $orderInfo['t_order_sn'] ?? '',
            'weight' => $orderInfo['weight'] ?? 0,
            't_name' => $orderInfo['t_name'] ?? '',
            'send_time' => $orderInfo['send_time'] ?? date('Y-m-d H:i:s'),
            'tracking_url' => $orderInfo['tracking_url'] ?? $detailUrl,
        ];
        
        return $this->sendLineFlexMsg($wxappId, $lineUserId, 'sendpack', $data);
    }
}
```

#### Payment.php (支付成功通知)
```php
// 位置: source/application/common/service/message/line/Payment.php

namespace app\common\service\message\line;

class Payment extends Basics
{
    protected $param = [];
    
    public function send($param)
    {
        $this->param = $param;
        return $this->onSendLineMsg();
    }
    
    private function onSendLineMsg()
    {
        $orderInfo = $this->param;
        $wxappId = $orderInfo['wxapp_id'];
        
        $lineUserId = $this->getLineUserIdByUserId($orderInfo['member_id']);
        if (empty($lineUserId)) {
            return false;
        }
        
        $detailUrl = $this->buildLiffUrl(
            '/order/detail',
            ['order_sn' => $orderInfo['order_sn']],
            $wxappId
        );
        
        $data = [
            'order_sn' => $orderInfo['order_sn'] ?? '',
            'total_free' => $orderInfo['total_free'] ?? 0,
            'pay_time' => $orderInfo['pay_time'] ?? date('Y-m-d H:i:s'),
            'remark' => $orderInfo['remark'] ?? '支付成功',
            'order_url' => $detailUrl,
        ];
        
        return $this->sendLineFlexMsg($wxappId, $lineUserId, 'payment', $data);
    }
}
```

#### Dabaosuccess.php (打包完成通知)
```php
// 位置: source/application/common/service/message/line/Dabaosuccess.php

namespace app\common\service\message\line;

class Dabaosuccess extends Basics
{
    protected $param = [];
    
    public function send($param)
    {
        $this->param = $param;
        return $this->onSendLineMsg();
    }
    
    private function onSendLineMsg()
    {
        $orderInfo = $this->param;
        $wxappId = $orderInfo['wxapp_id'];
        
        $lineUserId = $this->getLineUserIdByUserId($orderInfo['member_id']);
        if (empty($lineUserId)) {
            return false;
        }
        
        $payUrl = $this->buildLiffUrl(
            '/order/payment',
            ['order_id' => $orderInfo['order_id']],
            $wxappId
        );
        
        $data = [
            'order_sn' => $orderInfo['order_sn'] ?? '',
            'pack_count' => $orderInfo['pack_count'] ?? 0,
            'weight' => $orderInfo['weight'] ?? 0,
            'volume' => $orderInfo['volume'] ?? 0,
            'pay_url' => $payUrl,
        ];
        
        // 添加图片（如果有）
        if (!empty($orderInfo['images'])) {
            $data['images'] = $orderInfo['images'];
        }
        
        return $this->sendLineFlexMsg($wxappId, $lineUserId, 'dabaosuccess', $data);
    }
}
```

## Data Models

### User表字段

```sql
-- 用户表中的LINE相关字段
line_openid VARCHAR(255) -- LINE用户的OpenID（主要使用）
line_user_id VARCHAR(255) -- LINE用户ID（兼容旧数据）
```

### Package表关联

```sql
-- 包裹表
yoshop_package
  - id
  - member_id (关联用户)
  - express_num
  - weight
  - entering_warehouse_time
  - storage_id (关联仓库)
  - wxapp_id

-- 包裹图片表
yoshop_package_image
  - id
  - package_id
  - image_id (关联upload_file)

-- 文件表
yoshop_upload_file
  - file_id
  - file_url
  - file_name
```

## Integration Points

### 需要集成通知的业务节点

| 业务节点 | 文件位置 | 方法 | 通知类型 |
|---------|---------|------|---------|
| 包裹入库 | api/controller/Package.php | inWarehouse() | Inwarehouse |
| 订单发货 | api/controller/Inpack.php | shipOrder() | Sendpack |
| 支付成功 | api/controller/Payment.php | paymentCallback() | Payment |
| 打包完成 | api/controller/Package.php | packComplete() | Dabaosuccess |
| 生成付款单 | api/controller/Inpack.php | createPayOrder() | Payorder |
| 包裹到仓 | api/controller/Package.php | arriveWarehouse() | Toshop |
| 出库申请 | api/controller/Package.php | applyOut() | Outapply |

## Error Handling

### 错误处理策略

1. **通知发送失败不影响业务流程**
   - 所有通知触发代码包裹在try-catch中
   - 异常仅记录日志，不抛出

2. **用户无LINE账号**
   - 检查line_openid是否为空
   - 为空则跳过发送，记录日志

3. **用户未添加LINE OA为好友**
   - 在发送前验证好友关系
   - 非好友则跳过发送，记录日志
   - 缓存验证结果以提高性能

4. **模板未启用**
   - 在Basics.php中已有检查逻辑
   - 未启用则直接返回false

5. **LINE API错误**
   - 记录完整的错误响应
   - 支持重试机制（最多3次）
   - 特殊处理"用户未添加好友"错误（更新缓存）

### 日志记录

```php
// 统一日志格式
log_write([
    'describe' => 'LINE通知触发',
    'notification_type' => 'inwarehouse',
    'package_id' => 123,
    'user_id' => 456,
    'line_openid' => 'Ud4e37...',
    'is_friend' => true/false,
    'result' => 'success/failed',
    'error' => '错误信息（如果有）',
    'time' => date('Y-m-d H:i:s')
]);
```

### 好友关系缓存策略

```php
// 缓存键格式
$cacheKey = "line_friendship_{$wxappId}_{$lineUserId}";

// 缓存时间
// - 是好友：24小时（86400秒）
// - 非好友：1小时（3600秒）- 较短时间，用户可能随时添加

// 缓存清除
// - 当收到LINE API "用户未添加好友"错误时，立即更新缓存为'no'
// - 当成功发送消息时，更新缓存为'yes'
```

## Testing Strategy

### 单元测试

1. **通知触发测试**
   - 测试每个业务节点的通知触发
   - 验证数据传递正确性

2. **LINE OpenID获取测试**
   - 测试line_openid字段读取
   - 测试line_user_id兼容性
   - 测试空值处理

3. **消息服务测试**
   - 测试每种消息类型的发送
   - 测试图片附带功能
   - 测试模板渲染

### 集成测试

1. **端到端测试**
   - 模拟完整业务流程
   - 验证通知自动发送
   - 检查LINE消息接收

2. **错误场景测试**
   - 用户无LINE账号
   - 模板未启用
   - LINE API错误
   - 网络超时

### 测试脚本

创建测试脚本验证各个通知类型：

```php
// test_notification_triggers.php
// 测试所有通知触发点
```

## Deployment Notes

### 部署步骤

1. **创建消息服务类**
   - Sendpack.php
   - Payment.php
   - Dabaosuccess.php
   - Payorder.php
   - Toshop.php
   - Outapply.php

2. **修改Basics.php**
   - 更新getLineUserIdByUserId()方法
   - 支持line_openid字段

3. **集成业务触发点**
   - 在各个控制器/模型中添加通知触发代码
   - 确保异常处理正确

4. **测试验证**
   - 使用测试脚本验证每种通知
   - 检查日志记录
   - 验证LINE消息接收

5. **监控上线**
   - 监控通知发送成功率
   - 检查错误日志
   - 收集用户反馈

### 配置检查清单

- [ ] LINE消息通知全局已启用
- [ ] 各消息模板已配置并启用
- [ ] LINE Access Token有效
- [ ] 用户表line_openid字段有数据
- [ ] 用户已添加LINE OA为好友
- [ ] 图片发送功能已配置（可选）
- [ ] 日志记录正常工作
- [ ] 好友关系缓存正常工作

### 回滚计划

如果部署后出现问题，可以通过以下方式快速回滚：

1. **禁用全局通知**
   - 在LINE配置页面关闭"启用LINE消息通知"开关
   - 所有自动通知将立即停止

2. **禁用特定消息类型**
   - 在LINE配置页面关闭特定消息类型的开关
   - 仅该类型的通知停止，其他继续工作

3. **代码回滚**
   - 移除业务控制器中的通知触发代码
   - 恢复原有的业务逻辑

4. **数据库回滚**
   - 无需数据库回滚（未修改表结构）

## Performance Considerations

1. **异步发送**
   - 通知发送不阻塞业务流程
   - 考虑使用消息队列（如Redis Queue）

2. **批量通知**
   - 对于批量操作，考虑合并通知
   - 避免短时间内发送大量消息

3. **缓存优化**
   - 缓存LINE配置信息
   - 缓存用户LINE OpenID映射

4. **数据库查询优化**
   - 使用with()预加载关联数据
   - 避免N+1查询问题
