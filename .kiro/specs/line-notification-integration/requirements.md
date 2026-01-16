# Requirements Document: LINE消息通知自动触发集成

## Introduction

本规范定义了LINE消息通知系统与业务流程的自动集成，确保在包裹状态变更、订单处理等关键业务节点自动向用户发送LINE通知消息。

## Glossary

- **System**: LINE消息通知自动触发系统
- **Package**: 包裹实体
- **Order**: 集运订单实体
- **User**: 系统用户（包含LINE OpenID）
- **Warehouse_Staff**: 仓管人员
- **Admin**: 后台管理员
- **LINE_OpenID**: LINE用户的唯一标识符（存储在user表的line_openid字段）
- **Message_Service**: LINE消息发送服务
- **Status_Change**: 业务状态变更事件

## Requirements

### Requirement 1: 包裹入库通知自动触发

**User Story:** 作为用户，当我的包裹入库时，我希望自动收到LINE通知，以便及时了解包裹状态。

#### Acceptance Criteria

1. WHEN 仓管人员在仓管端录入包裹入库信息 THEN THE System SHALL 自动发送入库通知到包裹所属用户的LINE账号
2. WHEN 后台管理员在后台录入包裹入库信息 THEN THE System SHALL 自动发送入库通知到包裹所属用户的LINE账号
3. WHEN 包裹状态从"待入库"变更为"已入库" THEN THE System SHALL 触发入库通知
4. WHEN 用户没有绑定LINE账号（line_openid为空） THEN THE System SHALL 跳过通知发送并记录日志
5. WHEN 入库通知模板未启用 THEN THE System SHALL 跳过通知发送
6. WHEN 包裹有关联图片且图片发送已启用 THEN THE System SHALL 在通知中附带包裹图片

### Requirement 2: 发货通知自动触发

**User Story:** 作为用户，当我的订单发货时，我希望自动收到LINE通知，以便追踪物流信息。

#### Acceptance Criteria

1. WHEN 仓管人员标记订单为"已发货" THEN THE System SHALL 自动发送发货通知到订单所属用户的LINE账号
2. WHEN 后台管理员标记订单为"已发货" THEN THE System SHALL 自动发送发货通知到订单所属用户的LINE账号
3. WHEN 订单状态从"待发货"变更为"已发货" THEN THE System SHALL 触发发货通知
4. WHEN 发货通知包含物流单号和追踪链接 THEN THE System SHALL 在消息中显示这些信息
5. WHEN 发货通知模板未启用 THEN THE System SHALL 跳过通知发送

### Requirement 3: 打包完成通知自动触发

**User Story:** 作为用户，当我的包裹打包完成时，我希望自动收到LINE通知，以便了解可以支付并发货。

#### Acceptance Criteria

1. WHEN 仓管人员完成包裹打包操作 THEN THE System SHALL 自动发送打包完成通知到用户的LINE账号
2. WHEN 打包订单状态变更为"打包完成" THEN THE System SHALL 触发打包完成通知
3. WHEN 打包完成通知包含包裹数量、重量、体积信息 THEN THE System SHALL 在消息中显示这些信息
4. WHEN 打包完成通知包含支付链接 THEN THE System SHALL 在消息中提供支付按钮
5. WHEN 打包完成通知模板未启用 THEN THE System SHALL 跳过通知发送

### Requirement 4: 支付成功通知自动触发

**User Story:** 作为用户，当我完成支付后，我希望自动收到LINE通知确认，以便确认支付状态。

#### Acceptance Criteria

1. WHEN 用户完成订单支付 THEN THE System SHALL 自动发送支付成功通知到用户的LINE账号
2. WHEN 订单支付状态从"未支付"变更为"已支付" THEN THE System SHALL 触发支付成功通知
3. WHEN 支付成功通知包含订单号和支付金额 THEN THE System SHALL 在消息中显示这些信息
4. WHEN 支付成功通知模板未启用 THEN THE System SHALL 跳过通知发送

### Requirement 5: 付款单生成通知自动触发

**User Story:** 作为用户，当系统生成付款单时，我希望自动收到LINE通知，以便及时支付。

#### Acceptance Criteria

1. WHEN 系统为用户生成付款单 THEN THE System SHALL 自动发送付款单通知到用户的LINE账号
2. WHEN 付款单包含应付金额和到期日期 THEN THE System SHALL 在消息中显示这些信息
3. WHEN 付款单通知包含支付链接 THEN THE System SHALL 在消息中提供支付按钮
4. WHEN 付款单通知模板未启用 THEN THE System SHALL 跳过通知发送

### Requirement 6: 到仓通知自动触发

**User Story:** 作为用户，当我的包裹到达仓库时，我希望自动收到LINE通知，以便了解包裹物流状态。

#### Acceptance Criteria

1. WHEN 包裹到达仓库并被扫描 THEN THE System SHALL 自动发送到仓通知到用户的LINE账号
2. WHEN 包裹状态变更为"已到仓" THEN THE System SHALL 触发到仓通知
3. WHEN 到仓通知包含快递公司和快递单号 THEN THE System SHALL 在消息中显示这些信息
4. WHEN 到仓通知模板未启用 THEN THE System SHALL 跳过通知发送

### Requirement 7: 出库申请通知自动触发

**User Story:** 作为用户，当我提交出库申请时，我希望自动收到LINE通知确认，以便了解申请状态。

#### Acceptance Criteria

1. WHEN 用户提交出库申请 THEN THE System SHALL 自动发送出库申请通知到用户的LINE账号
2. WHEN 出库申请包含申请单号和包裹数量 THEN THE System SHALL 在消息中显示这些信息
3. WHEN 出库申请通知包含审核状态 THEN THE System SHALL 在消息中显示当前状态
4. WHEN 出库申请通知模板未启用 THEN THE System SHALL 跳过通知发送

### Requirement 8: LINE OpenID字段支持

**User Story:** 作为系统，我需要正确识别用户的LINE账号，以便发送通知到正确的用户。

#### Acceptance Criteria

1. THE System SHALL 使用user表的line_openid字段作为LINE用户标识
2. WHEN 查询用户LINE标识时 THEN THE System SHALL 优先使用line_openid字段
3. WHEN line_openid字段为空或NULL THEN THE System SHALL 跳过该用户的通知发送
4. THE System SHALL 兼容历史数据中的line_user_id字段（如果存在）

### Requirement 9: 通知发送失败处理

**User Story:** 作为系统管理员，当通知发送失败时，我希望系统记录详细日志，以便排查问题。

#### Acceptance Criteria

1. WHEN 通知发送失败 THEN THE System SHALL 记录失败原因到日志
2. WHEN 通知发送失败 THEN THE System SHALL 不影响业务流程的正常执行
3. WHEN LINE API返回错误 THEN THE System SHALL 记录完整的错误响应
4. THE System SHALL 记录每次通知发送的结果（成功/失败）
5. THE System SHALL 记录通知发送的时间戳和目标用户

### Requirement 10: 通知开关控制

**User Story:** 作为系统管理员，我希望能够全局或单独控制每种通知的启用状态，以便灵活管理通知功能。

#### Acceptance Criteria

1. THE System SHALL 支持全局启用/禁用LINE消息通知
2. THE System SHALL 支持单独启用/禁用每种消息类型的通知
3. WHEN 全局通知被禁用 THEN THE System SHALL 不发送任何类型的通知
4. WHEN 特定消息类型被禁用 THEN THE System SHALL 仅跳过该类型的通知
5. THE System SHALL 在配置页面显示每种通知的启用状态

### Requirement 11: 异步通知发送

**User Story:** 作为系统，我需要异步发送通知，以便不阻塞业务流程的执行。

#### Acceptance Criteria

1. THE System SHALL 异步发送LINE通知消息
2. WHEN 通知发送耗时较长 THEN THE System SHALL 不阻塞业务操作的响应
3. WHEN 通知发送队列积压 THEN THE System SHALL 按优先级处理通知
4. THE System SHALL 支持通知发送重试机制（最多3次）

### Requirement 12: 包裹图片自动获取

**User Story:** 作为系统，当发送包裹相关通知时，我需要自动获取包裹图片，以便提供更丰富的通知内容。

#### Acceptance Criteria

1. WHEN 发送包裹入库通知 THEN THE System SHALL 自动查询包裹关联的图片
2. WHEN 包裹有多张图片 THEN THE System SHALL 根据配置的最大数量发送图片
3. WHEN 包裹没有图片 THEN THE System SHALL 仅发送文字通知
4. THE System SHALL 从package_image表和upload_file表联合查询图片URL
5. THE System SHALL 确保图片URL为HTTPS格式（LINE API要求）

### Requirement 13: LINE OA好友关系验证

**User Story:** 作为系统，我需要验证用户是否已添加LINE OA为好友，以便确保消息能够成功发送。

#### Acceptance Criteria

1. WHEN 发送任何类型的LINE通知前 THEN THE System SHALL 验证用户是否已添加LINE OA为好友
2. WHEN 用户未添加LINE OA为好友 THEN THE System SHALL 跳过通知发送并记录日志
3. WHEN 用户已添加LINE OA为好友 THEN THE System SHALL 继续发送通知
4. THE System SHALL 缓存好友关系验证结果以提高性能（缓存时间：24小时）
5. WHEN LINE API返回"用户未添加好友"错误 THEN THE System SHALL 更新缓存并跳过后续通知
6. THE System SHALL 在日志中记录好友关系验证失败的情况
7. THE System SHALL 提供管理界面显示未添加好友的用户列表（可选功能）
