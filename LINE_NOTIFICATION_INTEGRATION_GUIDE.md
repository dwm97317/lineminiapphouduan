# LINE消息通知自动触发集成指南

## 概述

本文档提供了将LINE消息通知集成到业务流程中的详细指南。所有消息服务类和好友关系验证功能已经实现完成。

## 已完成的工作

### 1. LINE API扩展 ✅
- ✅ `LineMessage.php` - 添加了 `getUserProfile()` 方法用于验证好友关系
- ✅ `Basics.php` - 添加了 `isFriendWithOA()` 方法，带智能缓存
- ✅ `Basics.php` - 更新了 `getLineUserIdByUserId()` 支持 `line_openid` 字段
- ✅ `Basics.php` - 更新了 `sendLineFlexMsg()` 集成好友关系验证
- ✅ `Basics.php` - 更新了 `logMessageSend()` 包含 `is_friend` 字段

### 2. 消息服务类 ✅
已创建以下6个消息服务类：
- ✅ `Sendpack.php` - 发货通知
- ✅ `Payment.php` - 支付成功通知
- ✅ `Dabaosuccess.php` - 打包完成通知
- ✅ `Payorder.php` - 付款单生成通知
- ✅ `Toshop.php` - 到仓通知
- ✅ `Outapply.php` - 出库申请通知

### 3. 现有服务类
- ✅ `Inwarehouse.php` - 包裹入库通知（已存在）

## 需要集成的业务触发点

以下是需要在业务控制器中添加通知触发的位置：

### 1. 包裹入库通知 (Inwarehouse)

**文件位置**: `source/application/api/controller/Package.php` 或相关控制器

**触发时机**: 当包裹状态变更为"已入库"时

**集成代码示例**:
```php
// 在包裹入库成功后添加
try {
    // 获取包裹完整信息（包含图片）
    $package = PackageModel::with(['packageimage.file', 'shop'])->find($packageId);
    
    if ($package && $package['member_id']) {
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
        
        // 发送通知
        $messageService = new \app\common\service\message\line\Inwarehouse();
        $messageService->send($data);
    }
} catch (\Exception $e) {
    // 记录错误但不影响业务流程
    log_write([
        'describe' => 'LINE入库通知触发失败',
        'package_id' => $packageId,
        'error' => $e->getMessage(),
        'time' => date('Y-m-d H:i:s')
    ]);
}
```

### 2. 发货通知 (Sendpack)

**文件位置**: `source/application/api/controller/Inpack.php` 或相关控制器

**触发时机**: 当订单状态变更为"已发货"时

**集成代码示例**:
```php
// 在订单发货成功后添加
try {
    $order = Inpack::find($orderId);
    
    if ($order && $order['member_id']) {
        $data = [
            'wxapp_id' => $order['wxapp_id'],
            'member_id' => $order['member_id'],
            'order_sn' => $order['order_sn'],
            't_order_sn' => $order['t_order_sn'],
            'weight' => $order['weight'],
            't_name' => $order['line']['name'] ?? '',
            'send_time' => date('Y-m-d H:i:s', $order['send_time']),
            'tracking_url' => '', // 物流追踪链接
        ];
        
        $messageService = new \app\common\service\message\line\Sendpack();
        $messageService->send($data);
    }
} catch (\Exception $e) {
    log_write([
        'describe' => 'LINE发货通知触发失败',
        'order_id' => $orderId,
        'error' => $e->getMessage(),
        'time' => date('Y-m-d H:i:s')
    ]);
}
```

### 3. 打包完成通知 (Dabaosuccess)

**文件位置**: `source/application/api/controller/Package.php` 或 `Inpack.php`

**触发时机**: 当打包订单状态变更为"打包完成"时

**集成代码示例**:
```php
// 在打包完成后添加
try {
    $order = Inpack::find($orderId);
    
    if ($order && $order['member_id']) {
        $data = [
            'wxapp_id' => $order['wxapp_id'],
            'member_id' => $order['member_id'],
            'order_sn' => $order['order_sn'],
            'pack_count' => $order['pack_count'] ?? 0,
            'weight' => $order['weight'],
            'volume' => $order['volume'],
            'order_id' => $order['id'],
        ];
        
        // 添加图片（如果有）
        if (!empty($order['images'])) {
            $data['images'] = $order['images'];
        }
        
        $messageService = new \app\common\service\message\line\Dabaosuccess();
        $messageService->send($data);
    }
} catch (\Exception $e) {
    log_write([
        'describe' => 'LINE打包完成通知触发失败',
        'order_id' => $orderId,
        'error' => $e->getMessage(),
        'time' => date('Y-m-d H:i:s')
    ]);
}
```

### 4. 支付成功通知 (Payment)

**文件位置**: `source/application/api/controller/Payment.php`

**触发时机**: 当订单支付状态从"未支付"变更为"已支付"时

**集成代码示例**:
```php
// 在支付成功回调中添加
try {
    $order = Inpack::find($orderId);
    
    if ($order && $order['member_id']) {
        $data = [
            'wxapp_id' => $order['wxapp_id'],
            'member_id' => $order['member_id'],
            'order_sn' => $order['order_sn'],
            'total_free' => $order['total_free'],
            'pay_time' => date('Y-m-d H:i:s'),
            'remark' => '支付成功',
        ];
        
        $messageService = new \app\common\service\message\line\Payment();
        $messageService->send($data);
    }
} catch (\Exception $e) {
    log_write([
        'describe' => 'LINE支付成功通知触发失败',
        'order_id' => $orderId,
        'error' => $e->getMessage(),
        'time' => date('Y-m-d H:i:s')
    ]);
}
```

### 5. 付款单生成通知 (Payorder)

**文件位置**: `source/application/api/controller/Inpack.php`

**触发时机**: 当系统为用户生成付款单时

**集成代码示例**:
```php
// 在生成付款单后添加
try {
    $order = Inpack::find($orderId);
    
    if ($order && $order['member_id']) {
        $data = [
            'wxapp_id' => $order['wxapp_id'],
            'member_id' => $order['member_id'],
            'order_sn' => $order['order_sn'],
            'total_amount' => $order['total_free'],
            'due_date' => '', // 到期日期
            'remark' => '请及时支付',
            'order_id' => $order['id'],
        ];
        
        $messageService = new \app\common\service\message\line\Payorder();
        $messageService->send($data);
    }
} catch (\Exception $e) {
    log_write([
        'describe' => 'LINE付款单生成通知触发失败',
        'order_id' => $orderId,
        'error' => $e->getMessage(),
        'time' => date('Y-m-d H:i:s')
    ]);
}
```

### 6. 到仓通知 (Toshop)

**文件位置**: `source/application/api/controller/Package.php`

**触发时机**: 当包裹到达仓库并被扫描时

**集成代码示例**:
```php
// 在包裹到仓后添加
try {
    $package = PackageModel::find($packageId);
    
    if ($package && $package['member_id']) {
        $data = [
            'wxapp_id' => $package['wxapp_id'],
            'member_id' => $package['member_id'],
            'id' => $package['id'],
            'express_company' => $package['express_name'] ?? '',
            'express_num' => $package['express_num'],
            'arrive_time' => date('Y-m-d H:i:s'),
            'remark' => '包裹已到仓',
        ];
        
        $messageService = new \app\common\service\message\line\Toshop();
        $messageService->send($data);
    }
} catch (\Exception $e) {
    log_write([
        'describe' => 'LINE到仓通知触发失败',
        'package_id' => $packageId,
        'error' => $e->getMessage(),
        'time' => date('Y-m-d H:i:s')
    ]);
}
```

### 7. 出库申请通知 (Outapply)

**文件位置**: `source/application/api/controller/Package.php`

**触发时机**: 当用户提交出库申请时

**集成代码示例**:
```php
// 在出库申请提交后添加
try {
    $apply = OutApply::find($applyId); // 假设有出库申请模型
    
    if ($apply && $apply['member_id']) {
        $data = [
            'wxapp_id' => $apply['wxapp_id'],
            'member_id' => $apply['member_id'],
            'apply_sn' => $apply['apply_sn'],
            'package_count' => $apply['package_count'],
            'status' => '待审核',
            'remark' => '出库申请已提交',
            'apply_id' => $apply['id'],
        ];
        
        $messageService = new \app\common\service\message\line\Outapply();
        $messageService->send($data);
    }
} catch (\Exception $e) {
    log_write([
        'describe' => 'LINE出库申请通知触发失败',
        'apply_id' => $applyId,
        'error' => $e->getMessage(),
        'time' => date('Y-m-d H:i:s')
    ]);
}
```

## 关键特性

### 1. 好友关系验证
所有通知在发送前都会自动验证用户是否已添加LINE OA为好友：
- 是好友：发送通知，缓存24小时
- 非好友：跳过发送，记录日志，缓存1小时

### 2. line_openid字段支持
系统优先使用 `line_openid` 字段识别用户，兼容旧的 `line_user_id` 字段。

### 3. 错误处理
所有通知触发代码都包裹在 try-catch 中，确保通知发送失败不影响业务流程。

### 4. 日志记录
每次通知发送都会记录详细日志，包括：
- 通知类型
- 用户ID
- LINE OpenID
- 是否好友
- 发送结果
- 错误信息（如果有）

## 测试

使用提供的测试脚本 `test_notification_integration.php` 测试所有通知类型。

## 注意事项

1. **必须先配置LINE消息通知**
   - 在后台配置页面启用LINE消息通知
   - 配置各消息模板并启用
   - 确保LINE Access Token有效

2. **用户必须添加LINE OA为好友**
   - 这是LINE平台的强制要求
   - 非好友用户不会收到通知，但不影响业务流程

3. **图片发送功能**
   - 仅在配置启用时生效
   - 图片URL必须是HTTPS格式

4. **性能优化**
   - 好友关系验证结果会被缓存
   - 通知发送是异步的，不阻塞业务流程

## 部署检查清单

- [ ] LINE消息通知全局已启用
- [ ] 各消息模板已配置并启用
- [ ] LINE Access Token有效
- [ ] 用户表line_openid字段有数据
- [ ] 用户已添加LINE OA为好友
- [ ] 图片发送功能已配置（可选）
- [ ] 日志记录正常工作
- [ ] 好友关系缓存正常工作
- [ ] 所有业务触发点已集成
- [ ] 测试脚本验证通过
