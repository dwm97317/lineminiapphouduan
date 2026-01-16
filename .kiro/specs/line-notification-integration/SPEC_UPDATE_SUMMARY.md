# LINE通知集成规范更新总结

## 更新时间
2026-01-15

## 更新内容

### 1. Requirements.md 更新

#### 新增 Requirement 13: LINE OA好友关系验证

**背景**: LINE平台要求用户必须先添加LINE OA为好友，才能接收消息。这是LINE平台的强制要求。

**关键验收标准**:
- 发送任何通知前必须验证好友关系
- 非好友用户跳过发送并记录日志
- 缓存验证结果24小时以提高性能
- 处理LINE API返回的"用户未添加好友"错误
- 在日志中记录好友关系验证失败情况

### 2. Design.md 完善

#### 2.1 数据流更新
在原有流程中增加"验证好友关系"步骤：
```
业务操作 → 数据库更新 → 触发通知 → 获取用户LINE ID → 
验证好友关系 → 渲染消息模板 → 获取图片(可选) → 发送到LINE API → 记录日志
```

#### 2.2 新增组件：LINE API扩展
在`LineMessage.php`中添加`getUserProfile()`方法：
- 用于获取LINE用户资料
- 只有好友才能成功获取
- 返回404/403表示非好友

#### 2.3 新增组件：好友关系验证
在`Basics.php`中添加`isFriendWithOA()`方法：
- 调用LINE API验证好友关系
- 使用缓存提高性能（好友24小时，非好友1小时）
- 异常处理和日志记录

#### 2.4 更新sendLineFlexMsg方法
在发送消息前增加好友关系验证：
```php
if (!$this->isFriendWithOA($userId, $wxappId)) {
    $this->logMessageSend($wxappId, $userId, $messageType, false, '用户未添加LINE OA为好友');
    return false;
}
```

#### 2.5 错误处理策略更新
新增第3条：
- 用户未添加LINE OA为好友
- 在发送前验证好友关系
- 非好友则跳过发送，记录日志
- 缓存验证结果以提高性能

#### 2.6 日志记录增强
在日志中增加`is_friend`字段：
```php
log_write([
    'describe' => 'LINE通知触发',
    'notification_type' => 'inwarehouse',
    'package_id' => 123,
    'user_id' => 456,
    'line_openid' => 'Ud4e37...',
    'is_friend' => true/false,  // 新增
    'result' => 'success/failed',
    'error' => '错误信息（如果有）',
    'time' => date('Y-m-d H:i:s')
]);
```

#### 2.7 缓存策略文档化
明确好友关系缓存策略：
- 缓存键格式：`line_friendship_{$wxappId}_{$lineUserId}`
- 是好友：缓存24小时
- 非好友：缓存1小时（用户可能随时添加）
- 缓存清除：收到"用户未添加好友"错误时立即更新

#### 2.8 部署检查清单更新
新增检查项：
- [ ] 用户已添加LINE OA为好友
- [ ] 好友关系缓存正常工作

#### 2.9 新增回滚计划
提供4种回滚方案：
1. 禁用全局通知
2. 禁用特定消息类型
3. 代码回滚
4. 数据库回滚（无需，未修改表结构）

## 技术要点

### 好友关系验证原理
LINE平台限制：
- 只有用户主动添加LINE OA为好友后，OA才能向用户发送消息
- 可以通过调用`GET /v2/bot/profile/{userId}` API来验证
- 如果用户是好友，返回200和用户资料
- 如果用户不是好友，返回404或403

### 性能优化
使用缓存避免频繁调用LINE API：
- 好友状态相对稳定，缓存24小时
- 非好友状态可能变化，缓存1小时
- 发送成功时更新缓存为'yes'
- 收到"非好友"错误时更新缓存为'no'

### 用户体验考虑
- 非好友用户不会收到通知，但不影响业务流程
- 系统记录详细日志，便于管理员了解通知发送情况
- 可选功能：提供管理界面显示未添加好友的用户列表

## 下一步

规范文档已完成，等待用户审核：
1. ✅ requirements.md - 已添加Requirement 13
2. ✅ design.md - 已完善好友关系验证设计
3. ⏳ tasks.md - 待创建（用户审核通过后）

用户审核通过后，将创建tasks.md并开始实施。
