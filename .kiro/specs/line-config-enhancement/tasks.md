# 实施计划：LINE 配置增强

## 概述

本实施计划将 LINE 消息通知配置系统的设计转换为具体的编码任务。每个任务都基于前面的任务构建，最终将所有组件集成在一起。

## 任务列表

- [x] 1. 更新配置模型和数据结构
  - 在 `source/application/common/model/Setting.php` 中更新 `line_messaging` 配置结构
  - 添加 API 设置字段（api_base_url, timeout, retry_times, log_enabled）
  - 添加 LIFF 配置字段（liff_id, liff_url）
  - 添加七种消息模板的完整配置（inwarehouse, sendpack, payment, dabaosuccess, payorder, toshop, outapply）
  - 每个模板包含：is_enable, name, alt_text, priority, send_delay, flex_template, variables
  - _需求：1.1, 1.2, 1.3, 2.1, 2.2, 2.4, 10.1_

- [ ]* 1.1 为配置结构编写属性测试
  - **属性 1：配置结构完整性**
  - **验证：需求 1.1, 1.3, 2.4, 10.1**

- [ ]* 1.2 为默认模板编写属性测试
  - **属性 2：默认模板可用性**
  - **验证：需求 1.2**

- [x] 2. 创建 LINE 消息服务基类
  - 创建 `source/application/common/service/message/line/Basics.php`
  - 实现 `sendLineFlexMsg()` 方法：检查配置、发送 Flex Message、记录日志
  - 实现 `renderTemplate()` 方法：替换模板变量 {{variable}}
  - 实现 `buildLiffUrl()` 方法：构建深层链接 URL
  - 实现 `getLineUserIdByUserId()` 方法：从数据库获取 LINE User ID
  - 实现 `logMessageSend()` 方法：记录消息发送日志
  - _需求：3.1, 3.2, 3.4, 3.5, 4.2, 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ]* 2.1 为禁用模板跳过编写属性测试
  - **属性 3：禁用模板跳过**
  - **验证：需求 1.4, 3.2**

- [ ]* 2.2 为模板变量渲染编写属性测试
  - **属性 4：模板变量渲染**
  - **验证：需求 2.5, 5.2, 5.3, 5.5**

- [ ]* 2.3 为深层链接 URL 构建编写属性测试
  - **属性 6：深层链接 URL 构建**
  - **验证：需求 4.2, 4.5**

- [ ]* 2.4 为特殊字符编码编写属性测试
  - **属性 14：特殊字符编码**
  - **验证：需求 5.4**

- [ ]* 2.5 为 LINE User ID 检索编写单元测试
  - 测试有效 user_id 返回正确的 line_user_id
  - 测试无效 user_id 返回 null
  - _需求：3.4_

- [ ]* 2.6 为消息发送日志编写属性测试
  - **属性 8：消息发送日志**
  - **验证：需求 3.5, 9.1**

- [x] 3. 实现各场景消息服务类
  - [x] 3.1 创建 Inwarehouse.php（包裹入库通知）
    - 实现 `send()` 方法
    - 构建包裹详情页深层链接
    - 准备模板数据（shop_name, express_num, entering_warehouse_time, weight, remark, detail_url）
    - 调用 `sendLineFlexMsg()`
    - _需求：3.3_

  - [x] 3.2 创建 Sendpack.php（发货通知）
    - 实现 `send()` 方法
    - 构建物流跟踪页深层链接
    - 准备模板数据（order_sn, t_order_sn, weight, t_name, send_time, tracking_url）
    - _需求：3.3_

  - [x] 3.3 创建 Payment.php（支付成功通知）
    - 实现 `send()` 方法
    - 构建订单详情页深层链接
    - 准备模板数据（order_sn, total_free, pay_time, remark, order_url）
    - _需求：3.3_

  - [x] 3.4 创建 Dabaosuccess.php（打包完成通知）
    - 实现 `send()` 方法
    - 构建支付页深层链接
    - 准备模板数据（order_sn, pack_count, weight, volume, pay_url）
    - _需求：3.3_

  - [x] 3.5 创建 Payorder.php（付款单生成通知）
    - 实现 `send()` 方法
    - 构建支付页深层链接
    - 准备模板数据（order_sn, amount, create_time, pay_url）
    - _需求：3.3_

  - [x] 3.6 创建 Toshop.php（到仓通知）
    - 实现 `send()` 方法
    - 准备模板数据（shop_name, express_num, arrival_time）
    - _需求：3.3_

  - [x] 3.7 创建 Outapply.php（出库申请通知）
    - 实现 `send()` 方法
    - 准备模板数据（apply_sn, package_count, apply_time）
    - _需求：3.3_

- [ ]* 3.8 为场景类编写单元测试
  - 测试每个场景类的 send() 方法
  - 测试深层链接构建
  - 测试模板数据准备
  - _需求：3.3_

- [x] 4. 更新消息分发服务
  - 更新 `source/application/common/service/Message.php`
  - 添加 `$lineSceneList` 数组，映射场景名称到 LINE 服务类
  - 实现 `sendLine()` 方法：根据场景名称实例化对应的服务类
  - 更新 `send()` 方法：同时调用 `sendWx()` 和 `sendLine()`
  - _需求：7.1, 7.2, 7.3, 7.4, 7.5_

- [ ]* 4.1 为场景名称路由编写属性测试
  - **属性 10：场景名称路由**
  - **验证：需求 7.3, 7.4**

- [ ]* 4.2 为双平台分发编写属性测试
  - **属性 11：双平台分发**
  - **验证：需求 7.2, 7.5**

- [x] 5. 检查点 - 确保所有测试通过
  - 确保所有测试通过，如有问题请询问用户

- [x] 6. 创建配置迁移服务
  - 创建 `source/application/common/service/LineConfigMigration.php`
  - 实现 `migrate()` 方法：检测旧配置格式并转换为新格式
  - 实现 `getDefaultTemplates()` 方法：返回所有默认模板配置
  - 实现各模板的 Flex Message JSON 结构（getInwarehouseTemplate, getSendpackTemplate 等）
  - 保留现有的 channel_id, channel_secret, access_token 值
  - _需求：8.1, 8.2, 8.3, 8.4, 8.5_

- [ ]* 6.1 为配置迁移编写属性测试
  - **属性 12：配置迁移保留**
  - **验证：需求 8.2, 8.3**

- [ ]* 6.2 为迁移幂等性编写属性测试
  - **属性 13：迁移幂等性**
  - **验证：需求 8.5**

- [x] 7. 更新后台控制器
  - 更新 `source/application/store/controller/setting/LineConfig.php`
  - 保持现有的 `index()` 方法结构（GET 显示页面，POST 保存配置）
  - 添加 `testMessage()` 方法：发送测试消息到指定 LINE User ID
  - 添加 `previewTemplate()` 方法：返回模板结构用于预览
  - 添加 `getTestData()` 私有方法：为每种消息类型生成测试数据
  - _需求：6.3, 6.4, 6.5, 6.6_

- [ ]* 7.1 为测试消息发送编写单元测试
  - 测试 testMessage() 方法正确发送消息
  - _需求：6.3_

- [ ]* 7.2 为模板预览编写单元测试
  - 测试 previewTemplate() 方法返回正确的模板数据
  - _需求：6.4_

- [x] 8. 更新后台视图文件
  - 更新 `source/application/store/view/setting/line_config/index.php`
  - 在"消息通知"标签页中添加 API 设置表单字段
  - 添加 LIFF 配置表单字段
  - 为七种消息模板添加配置面板（每个面板包含：启用开关、替代文本、优先级、发送延迟、变量列表）
  - 为每个模板添加"预览模板"和"发送测试"按钮
  - 添加 JavaScript 函数：previewTemplate() 和 testMessage()
  - _需求：6.1, 6.2_

- [x] 9. 实现错误处理和日志记录
  - 在 Basics.php 中添加 try-catch 块捕获 LINE API 异常
  - 实现重试逻辑（使用配置的 retry_times）
  - 添加详细的错误日志（包括错误代码、消息、上下文）
  - 实现条件日志记录（根据 log_enabled 配置）
  - 确保所有错误情况都返回 false 而不是抛出异常
  - _需求：3.6, 9.2, 9.3, 9.4, 10.5_

- [ ]* 9.1 为错误处理编写属性测试
  - **属性 9：无异常错误处理**
  - **验证：需求 3.6, 9.3**

- [ ]* 9.2 为错误详情日志编写单元测试
  - 测试 LINE API 错误被正确记录
  - _需求：9.2_

- [ ]* 9.3 为条件日志编写属性测试
  - **属性 17：条件日志**
  - **验证：需求 10.5**

- [x] 10. 实现 API 配置使用
  - 在 LineMessage.php 中使用配置的 api_base_url
  - 实现请求超时（使用配置的 timeout）
  - 实现重试机制（使用配置的 retry_times）
  - _需求：10.2, 10.3, 10.4_

- [ ]* 10.1 为 API 配置使用编写属性测试
  - **属性 16：API 配置使用**
  - **验证：需求 10.2, 10.3, 10.4**

- [x] 11. 检查点 - 确保所有测试通过
  - 确保所有测试通过，如有问题请询问用户

- [x] 12. 集成测试和验证
  - [x] 12.1 测试完整的消息发送流程
    - 模拟包裹入库事件
    - 验证配置加载、模板渲染、深层链接构建、API 调用、日志创建
    - _需求：所有需求_

  - [x] 12.2 测试配置管理流程
    - 通过控制器保存配置
    - 验证配置持久化到数据库
    - 验证可以正确检索配置
    - _需求：6.5, 6.6_

  - [x] 12.3 测试配置迁移
    - 创建旧格式配置
    - 运行迁移
    - 验证转换为新格式
    - 验证现有值被保留
    - _需求：8.1, 8.2, 8.3, 8.4, 8.5_

- [x] 13. 文档和部署准备
  - 更新 README 或相关文档，说明如何配置 LINE 消息通知
  - 创建配置示例文件
  - 准备数据库迁移脚本（如需要）
  - 编写部署说明

## 注意事项

- 标记为 `*` 的任务是可选的测试任务，可以跳过以加快 MVP 开发
- 每个任务都引用了具体的需求编号，便于追溯
- 检查点任务确保增量验证
- 属性测试验证通用正确性属性
- 单元测试验证具体示例和边缘情况
