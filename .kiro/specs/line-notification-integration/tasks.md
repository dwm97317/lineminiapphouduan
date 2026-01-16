# Implementation Plan: LINE消息通知自动触发集成

## Overview

本实施计划将LINE消息通知系统集成到业务流程中，实现7种通知类型的自动触发，支持LINE OA好友关系验证、line_openid字段、图片发送等功能。

## Tasks

- [ ] 1. 扩展LINE API - 添加用户资料获取和好友关系验证
- [ ] 1.1 在LineMessage.php中添加getUserProfile()方法
  - 实现GET /v2/bot/profile/{userId} API调用
  - 返回200表示用户是好友，返回404/403表示非好友
  - 添加错误处理和超时设置
  - _Requirements: 13.1, 13.2, 13.3_

- [ ] 1.2 在Basics.php中添加isFriendWithOA()方法
  - 调用getUserProfile()验证好友关系
  - 实现缓存机制（好友24小时，非好友1小时）
  - 添加异常处理和日志记录
  - _Requirements: 13.1, 13.4, 13.5, 13.6_

- [ ] 1.3 更新Basics.php的getLineUserIdByUserId()方法
  - 优先使用line_openid字段
  - 兼容line_user_id字段
  - 添加空值检查
  - _Requirements: 8.1, 8.2, 8.3, 8.4_

- [ ] 1.4 更新Basics.php的sendLineFlexMsg()方法
  - 在发送前调用isFriendWithOA()验证好友关系
  - 非好友则跳过发送并记录日志
  - 更新日志记录包含is_friend字段
  - _Requirements: 13.1, 13.2, 13.6_

- [ ] 2. 创建消息服务类
- [ ] 2.1 创建Sendpack.php（发货通知）
  - 继承Basics类
  - 实现send()方法
  - 构建发货通知数据（订单号、物流单号、追踪链接等）
  - _Requirements: 2.1, 2.2, 2.3, 2.4_

- [ ] 2.2 创建Payment.php（支付成功通知）
  - 继承Basics类
  - 实现send()方法
  - 构建支付通知数据（订单号、支付金额、支付时间等）
  - _Requirements: 4.1, 4.2, 4.3_


- [ ] 2.3 创建Dabaosuccess.php（打包完成通知）
  - 继承Basics类
  - 实现send()方法
  - 构建打包通知数据（订单号、包裹数量、重量、体积、支付链接等）
  - 支持图片附带功能
  - _Requirements: 3.1, 3.2, 3.3, 3.4_

- [ ] 2.4 创建Payorder.php（付款单生成通知）
  - 继承Basics类
  - 实现send()方法
  - 构建付款单通知数据（付款单号、应付金额、到期日期、支付链接等）
  - _Requirements: 5.1, 5.2, 5.3_

- [ ] 2.5 创建Toshop.php（到仓通知）
  - 继承Basics类
  - 实现send()方法
  - 构建到仓通知数据（快递公司、快递单号、到仓时间等）
  - _Requirements: 6.1, 6.2, 6.3_

- [ ] 2.6 创建Outapply.php（出库申请通知）
  - 继承Basics类
  - 实现send()方法
  - 构建出库申请通知数据（申请单号、包裹数量、审核状态等）
  - _Requirements: 7.1, 7.2, 7.3_

- [ ] 3. 集成包裹入库通知触发
- [ ] 3.1 在Package.php控制器中添加入库通知触发
  - 找到包裹入库的业务方法
  - 在入库成功后调用triggerInwarehouseNotification()
  - 获取包裹完整信息（包含图片）
  - 准备通知数据并调用Inwarehouse服务
  - 添加try-catch错误处理
  - _Requirements: 1.1, 1.2, 1.3, 1.6, 9.1, 9.2_

- [ ] 4. 集成发货通知触发
- [ ] 4.1 在Inpack.php控制器中添加发货通知触发
  - 找到订单发货的业务方法
  - 在发货成功后调用triggerSendpackNotification()
  - 准备通知数据（订单号、物流单号、追踪链接等）
  - 调用Sendpack服务
  - 添加try-catch错误处理
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 9.1, 9.2_

- [ ] 5. 集成打包完成通知触发
- [ ] 5.1 在Package.php或Inpack.php控制器中添加打包完成通知触发
  - 找到打包完成的业务方法
  - 在打包成功后调用triggerDabaosuccessNotification()
  - 准备通知数据（订单号、包裹数量、重量、体积等）
  - 调用Dabaosuccess服务
  - 添加try-catch错误处理
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 9.1, 9.2_

- [ ] 6. 集成支付成功通知触发
- [ ] 6.1 在Payment.php控制器中添加支付成功通知触发
  - 找到支付回调的业务方法
  - 在支付成功后调用triggerPaymentNotification()
  - 准备通知数据（订单号、支付金额、支付时间等）
  - 调用Payment服务
  - 添加try-catch错误处理
  - _Requirements: 4.1, 4.2, 4.3, 9.1, 9.2_

- [ ] 7. 集成付款单生成通知触发
- [ ] 7.1 在Inpack.php控制器中添加付款单生成通知触发
  - 找到生成付款单的业务方法
  - 在付款单生成后调用triggerPayorderNotification()
  - 准备通知数据（付款单号、应付金额、到期日期等）
  - 调用Payorder服务
  - 添加try-catch错误处理
  - _Requirements: 5.1, 5.2, 5.3, 9.1, 9.2_

- [ ] 8. 集成到仓通知触发
- [ ] 8.1 在Package.php控制器中添加到仓通知触发
  - 找到包裹到仓的业务方法
  - 在到仓成功后调用triggerToshopNotification()
  - 准备通知数据（快递公司、快递单号、到仓时间等）
  - 调用Toshop服务
  - 添加try-catch错误处理
  - _Requirements: 6.1, 6.2, 6.3, 9.1, 9.2_

- [ ] 9. 集成出库申请通知触发
- [ ] 9.1 在Package.php控制器中添加出库申请通知触发
  - 找到出库申请的业务方法
  - 在申请提交后调用triggerOutapplyNotification()
  - 准备通知数据（申请单号、包裹数量、审核状态等）
  - 调用Outapply服务
  - 添加try-catch错误处理
  - _Requirements: 7.1, 7.2, 7.3, 9.1, 9.2_

- [ ] 10. 测试和验证
- [ ] 10.1 创建测试脚本test_notification_integration.php
  - 测试每种通知类型的触发
  - 测试好友关系验证功能
  - 测试line_openid字段读取
  - 测试缓存机制
  - 测试错误处理和日志记录
  - _Requirements: 所有需求_

- [ ] 10.2 端到端测试
  - 使用真实包裹数据测试入库通知
  - 使用真实订单数据测试发货通知
  - 测试非好友用户的处理
  - 测试无LINE账号用户的处理
  - 验证LINE消息接收
  - 检查日志记录
  - _Requirements: 所有需求_

- [ ] 11. Checkpoint - 确保所有测试通过
  - 确保所有测试通过，如有问题请向用户询问

## Notes

- 所有通知触发代码必须包裹在try-catch中，确保不影响业务流程
- 每个通知服务类都需要验证好友关系后才发送
- 优先使用line_openid字段，兼容line_user_id字段
- 缓存好友关系验证结果以提高性能
- 详细记录日志以便排查问题
- 图片发送功能仅在配置启用时生效
