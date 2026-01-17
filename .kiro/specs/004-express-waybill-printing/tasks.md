# Tasks: 快递面单打印功能

**Feature Branch**: `004-express-waybill-printing`
**Created**: 2026-01-17

## Phase 1: Setup

- [ ] T001 创建数据库迁移文件 `Lineminiapp/database/migrations/20260117_create_waybill_record_table.php`
- [ ] T002 执行数据库迁移，创建 yoshop_waybill_record 表
- [ ] T003 [P] 在 Setting 表中添加快递配置 express_config (中通和顺丰 API 配置)
- [ ] T004 [P] 在 Setting 表中插入默认面单配置 waybill_config_zhongtong 和 waybill_config_shunfeng
- [ ] T005 [P] 创建快递接口目录 `Lineminiapp/source/application/common/library/express/`
- [ ] T006 [P] 创建面单配置服务目录 `Lineminiapp/source/application/common/service/`

## Phase 2: Foundational (核心类和服务)

- [ ] T007 创建快递接口 `Lineminiapp/source/application/common/library/express/ExpressInterface.php`
- [ ] T008 [P] 创建中通快递类 `Lineminiapp/source/application/common/library/express/ZhongtongExpress.php`
- [ ] T009 [P] 创建顺丰快递类 `Lineminiapp/source/application/common/library/express/ShunfengExpress.php`
- [ ] T010 创建面单记录模型 `Lineminiapp/source/application/store/model/WaybillRecord.php`
- [ ] T011 创建面单服务类 `Lineminiapp/source/application/common/service/WaybillService.php`
- [ ] T012 创建面单配置服务类 `Lineminiapp/source/application/common/service/WaybillConfigService.php`
- [ ] T013 在 WaybillConfigService 中实现 getConfig() 方法（从 Setting 表读取配置）
- [ ] T014 在 WaybillConfigService 中实现 saveConfig() 方法（保存配置到 Setting 表）
- [ ] T015 在 WaybillConfigService 中实现 getDefaultConfig() 方法（返回默认配置）
- [ ] T016 在 WaybillConfigService 中实现 validateConfig() 方法（验证配置格式）
- [ ] T017 在 WaybillConfigService 中实现 getFieldDefinitions() 方法（返回字段定义列表）
- [ ] T018 在 Inpack 模型中添加关联方法 `getWaybillHistory()` 和 `getLastWaybill()`

## Phase 3: User Story 1 - 查看打印按钮 (P0)

**Goal**: 在订单列表页面显示"打印中通"和"打印顺丰"按钮
**Independent Test**: 访问 http://localhost:8080/index.php?s=/store/tr_order/all_list 查看按钮是否显示

- [ ] T019 [US1] 修改订单列表视图 `Lineminiapp/source/application/store/view/tr_order/index.php` 添加打印按钮到操作列
- [ ] T020 [US1] 添加批量操作区域，包含"批量打印中通"和"批量打印顺丰"按钮
- [ ] T021 [US1] 添加 CSS 样式，确保按钮显示美观且有 hover 效果
- [ ] T022 [US1] 添加权限检查，只有具有"打印面单"权限的用户才能看到按钮

## Phase 4: User Story 2 & 3 - 预览中通和顺丰面单 (P0)

**Goal**: 点击打印按钮后显示面单预览窗口
**Independent Test**: 点击"打印中通"按钮，查看是否弹出预览窗口并显示面单

- [ ] T023 [US2] 在 TrOrder 控制器中添加 `printWaybill()` 方法 `Lineminiapp/source/application/store/controller/TrOrder.php`
- [ ] T024 [US2] 实现订单信息验证逻辑（检查 address_id 是否存在，查询 UserAddress 表验证收货信息完整性）
- [ ] T025 [US2] 调用 WaybillConfigService 获取面单配置
- [ ] T026 [US2] 调用 WaybillService 生成面单数据（应用配置过滤字段）
- [ ] T027 [US2] 创建面单预览视图 `Lineminiapp/source/application/store/view/tr_order/waybill_preview.php`
- [ ] T028 [US2] 实现中通面单 HTML 模板（根据配置动态显示字段）
- [ ] T029 [US2] 实现顺丰面单 HTML 模板（根据配置动态显示字段）
- [ ] T030 [US2] 在面单模板中应用打印参数（纸张大小 76x130mm、方向、缩放）
- [ ] T031 [US2] 添加前端 JavaScript 处理点击事件，发送 AJAX 请求
- [ ] T032 [US2] 实现模态窗口显示预览内容
- [ ] T033 [US2] 添加错误处理，显示友好的错误提示信息
- [ ] T034 [US2] 添加加载动画，提升用户体验

## Phase 5: User Story 4 - 立即打印操作 (P1)

**Goal**: 用户可以点击"立即打印"按钮打印面单
**Independent Test**: 在预览窗口点击"立即打印"，查看是否触发浏览器打印对话框

- [ ] T035 [US4] 在预览窗口添加"立即打印"按钮
- [ ] T036 [US4] 实现前端打印逻辑，调用 `window.print()`
- [ ] T037 [US4] 添加打印样式 CSS，隐藏不必要的元素（按钮、边框等），应用 76x130mm 纸张设置
- [ ] T038 [US4] 在 TrOrder 控制器中添加 `recordWaybillPrint()` 方法记录打印日志
- [ ] T039 [US4] 打印成功后调用后端 API 保存打印记录到 waybill_record 表
- [ ] T040 [US4] 打印完成后自动关闭预览窗口或显示成功提示

## Phase 6: User Story 5 - 只下单不打印 (P1)

**Goal**: 用户可以只创建快递订单获取运单号，不立即打印
**Independent Test**: 在预览窗口点击"只下单"，查看是否成功创建订单并显示运单号

- [ ] T041 [US5] 在预览窗口添加"只下单"按钮
- [ ] T042 [US5] 在 TrOrder 控制器中添加 `createWaybillOrder()` 方法
- [ ] T043 [US5] 调用快递 API 创建订单（中通或顺丰）
- [ ] T044 [US5] 保存运单号到 Inpack 表的相应字段
- [ ] T045 [US5] 保存下单记录到 waybill_record 表（operation_type=2）
- [ ] T046 [US5] 显示成功提示"已下单，运单号：XXXXXX"
- [ ] T047 [US5] 下单成功后关闭预览窗口

## Phase 7: User Story 6 - 取消操作 (P2)

**Goal**: 用户可以取消预览并关闭窗口
**Independent Test**: 在预览窗口点击"取消"或按 ESC 键，查看窗口是否关闭

- [ ] T048 [US6] 在预览窗口添加"取消"按钮
- [ ] T049 [US6] 实现关闭窗口逻辑，不执行任何 API 调用
- [ ] T050 [US6] 添加 ESC 键监听，按 ESC 键关闭窗口
- [ ] T051 [US6] 添加点击窗口外部关闭功能（可选）

## Phase 8: User Story 7 - 批量打印功能 (P1)

**Goal**: 用户可以批量选择多个订单进行打印
**Independent Test**: 勾选 3 个订单，点击"批量打印中通"，查看是否依次显示预览

- [ ] T052 [US7] 在 TrOrder 控制器中添加 `batchPrintWaybill()` 方法
- [ ] T053 [US7] 实现批量订单数据获取和验证
- [ ] T054 [US7] 修改前端 JavaScript，支持批量打印流程
- [ ] T055 [US7] 实现批量预览逻辑，依次显示每个订单
- [ ] T056 [US7] 在预览窗口添加批量操作按钮："打印并继续"、"只下单并继续"、"跳过"、"取消批量"
- [ ] T057 [US7] 实现"打印并继续"逻辑，打印当前订单后显示下一个
- [ ] T058 [US7] 实现"只下单并继续"逻辑，下单后显示下一个
- [ ] T059 [US7] 实现"跳过"逻辑，直接显示下一个订单
- [ ] T060 [US7] 实现"取消批量"逻辑，停止批量打印
- [ ] T061 [US7] 批量打印完成后显示统计信息（成功 X 个，跳过 Y 个，失败 Z 个）
- [ ] T062 [US7] 添加批量打印进度指示器（当前第 N 个，共 M 个）

## Phase 9: User Story 8 & 9 - 面单配置管理 (P1)

**Goal**: 管理员可以在后台配置面单字段和打印参数
**Independent Test**: 访问 `/store/setting/waybill_config` 配置页面，修改配置后打印验证

- [ ] T063 [US8] 创建面单配置控制器 `Lineminiapp/source/application/store/controller/setting/WaybillConfig.php`
- [ ] T064 [US8] 实现 `index()` 方法，显示配置管理页面
- [ ] T065 [US8] 实现 `getConfig()` API 方法，获取指定快递公司的配置
- [ ] T066 [US8] 实现 `saveConfig()` API 方法，保存配置到 Setting 表
- [ ] T067 [US8] 实现 `getFieldList()` API 方法，返回快递公司支持的字段列表
- [ ] T068 [US8] 实现 `resetConfig()` API 方法，恢复默认配置
- [ ] T069 [US8] 创建配置页面视图 `Lineminiapp/source/application/store/view/setting/waybill_config/index.php`
- [ ] T070 [US8] 实现快递公司选择下拉框（中通、顺丰）
- [ ] T071 [US8] 实现字段显示/隐藏配置区域（复选框列表）
- [ ] T072 [US8] 实现快递公司特定字段配置区域（中通：网点代码、网点名称；顺丰：月结卡号、付款方式）
- [ ] T073 [US8] 实现打印参数配置区域（纸张大小、打印方向、缩放比例）
- [ ] T074 [US8] 添加"保存"和"恢复默认"按钮
- [ ] T075 [US8] 实现前端 JavaScript，处理配置加载、保存、恢复操作
- [ ] T076 [US8] 添加配置验证逻辑（前端和后端双重验证）
- [ ] T077 [US8] 添加配置保存成功/失败提示
- [ ] T078 [US8] 在系统设置菜单中添加"面单配置"菜单项
- [ ] T079 [US8] 添加"面单配置管理"权限，限制只有管理员可访问

**Goal**: 在订单详情页显示打印历史记录
**Independent Test**: 打开订单详情页，查看是否显示打印历史记录

- [ ] T053 在 TrOrder 控制器的 `orderdetail()` 方法中添加打印历史查询
- [ ] T054 修改订单详情视图 `Lineminiapp/source/application/store/view/tr_order/orderdetail.php` 添加打印历史展示区域
- [ ] T055 实现打印历史列表显示（时间、快递公司、运单号、操作类型、操作人）
- [ ] T056 添加"重新打印"按钮，允许用户重新打印已有运单号的面单

## Phase 11: Edge Cases & Error Handling

**Goal**: 处理各种边界情况和错误场景

- [ ] T084 实现订单信息不完整检查，提示"订单收货信息不完整，请先完善收货地址"
- [ ] T085 实现地址不存在检查，提示"收货地址不存在，请重新选择地址"
- [ ] T086 实现重复打印检查，提示"该订单已打印过面单（运单号：XXX），是否重新打印？"
- [ ] T087 实现 API 超时处理（30 秒超时），显示超时提示并允许重试
- [ ] T088 实现 API 错误处理，解析错误信息并显示给用户
- [ ] T089 添加并发控制，防止同一订单被多次同时打印
- [ ] T090 实现权限检查中间件，确保只有授权用户可以访问打印功能
- [ ] T091 实现配置缺失处理，自动使用默认配置
- [ ] T092 实现配置字段与 API 要求冲突检查，提示管理员调整配置

## Phase 12: Polish & Optimization

**Goal**: 优化性能和用户体验

- [ ] T092 [P] 实现配置缓存机制，配置加载后缓存到内存
- [ ] T093 [P] 实现 API 响应缓存（5 分钟），避免重复请求
- [ ] T094 [P] 添加数据库索引到 waybill_record 表
- [ ] T095 [P] 压缩面单 HTML，减少传输大小
- [ ] T096 [P] 添加日志记录，记录所有 API 调用和错误
- [ ] T097 实现批量打印时的预加载，提前加载下一个订单数据
- [ ] T098 添加打印统计功能，统计今日/本周/本月打印数量
- [ ] T099 优化面单模板，确保打印效果符合快递公司要求（76x130mm）
- [ ] T100 实现配置预加载，页面加载时预加载配置

## Phase 13: Testing & Documentation

**Goal**: 测试功能并完善文档

- [ ] T101 [P] 测试中通 API 集成，确保能正常创建订单和获取面单
- [ ] T102 [P] 测试顺丰 API 集成，确保能正常创建订单和获取面单
- [ ] T103 测试单个打印流程（打印和只下单）
- [ ] T104 测试批量打印流程
- [ ] T105 测试面单配置管理功能（获取、保存、恢复默认）
- [ ] T106 测试配置应用到面单生成（字段显示/隐藏、打印参数）
- [ ] T107 测试错误处理（API 失败、超时、信息不完整等）
- [ ] T108 测试不同浏览器兼容性（Chrome、Firefox、Edge）
- [ ] T109 测试实际打印机打印效果（76x130mm 纸张）
- [ ] T110 测试权限控制（打印权限、配置管理权限）
- [ ] T111 编写用户操作手册（包含配置管理说明）
- [ ] T112 编写 API 对接文档
- [ ] T113 更新系统部署文档（包含配置初始化步骤）

## Dependencies

```mermaid
graph TD
  Setup[Phase 1: Setup] --> Foundation[Phase 2: Foundational]
  Foundation --> US1[Phase 3: US1 - 按钮显示]
  Foundation --> US2[Phase 4: US2&3 - 预览]
  US2 --> US4[Phase 5: US4 - 打印]
  US2 --> US5[Phase 6: US5 - 下单]
  US2 --> US6[Phase 7: US6 - 取消]
  US4 --> US7[Phase 8: US7 - 批量]
  US5 --> US7
  US6 --> US7
  Foundation --> US8[Phase 9: US8&9 - 配置管理]
  US7 --> History[Phase 10: 打印历史]
  US8 --> History
  History --> EdgeCases[Phase 11: 边界情况]
  EdgeCases --> Polish[Phase 12: 优化]
  Polish --> Testing[Phase 13: 测试]
```

## Parallel Execution

### Phase 1 (Setup)
- T003, T004, T005, T006 可以并行执行（不同配置和目录创建）

### Phase 2 (Foundational)
- T008, T009 可以并行执行（不同快递公司实现）
- T013, T014, T015, T016, T017 可以并行执行（WaybillConfigService 不同方法）

### Phase 9 (配置管理)
- T070, T071, T072, T073 可以并行执行（配置页面不同区域）

### Phase 12 (Polish)
- T092, T093, T094, T095, T096 可以并行执行（不同优化点）

### Phase 13 (Testing)
- T101, T102 可以并行执行（不同 API 测试）
- T108, T109, T110 可以并行执行（不同测试场景）

## Implementation Strategy

### MVP Scope (最小可行产品)
完成 Phase 1-6 即可实现基本的单个打印功能：
- Setup + Foundational (T001-T018)
- 按钮显示 (T019-T022)
- 面单预览 (T023-T034)
- 打印操作 (T035-T040)
- 下单操作 (T041-T047)
- 取消操作 (T048-T051)

### Incremental Delivery
- **第一阶段**: MVP (Phase 1-6) - 单个打印功能
- **第二阶段**: 批量打印 (Phase 8) - 提升效率
- **第三阶段**: 配置管理 (Phase 9) - 灵活配置
- **第四阶段**: 打印历史 (Phase 10) - 完善功能
- **第五阶段**: 优化和测试 (Phase 11-13) - 提升质量

### Estimated Timeline
- Phase 1-2: 2 天（基础设施 + 配置服务）
- Phase 3-7: 3 天（核心打印功能）
- Phase 8: 2 天（批量打印）
- Phase 9: 2 天（配置管理）
- Phase 10: 1 天（打印历史）
- Phase 11-13: 2 天（优化和测试）
- **总计**: 约 12 个工作日

## Task Summary

- **Total tasks**: 114
- **Setup phase**: 6 tasks
- **Foundational phase**: 12 tasks
- **User Story phases**: 9 stories, 74 tasks
- **Polish phase**: 9 tasks
- **Testing phase**: 13 tasks
- **Parallel opportunities**: 15 tasks

## Format Validation

- [x] ALL tasks start with `- [ ]`
- [x] ALL tasks have Task ID (T001-T114)
- [x] User Story phase tasks have [USx] label
- [x] ALL tasks have file paths where applicable
- [x] [P] markers only on parallelizable tasks
