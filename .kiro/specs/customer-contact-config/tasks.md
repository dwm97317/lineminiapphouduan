# Tasks - 客户联系配置功能实现

## 概述
实现客户联系配置功能，允许商户在后台配置客服联系方式，并在前端主页展示。

## 任务列表

### Phase 1: 后端实现 (Backend)

#### Task 1.1: 更新 LineConfig 控制器 ✅
**文件**: `Lineminiapp/source/application/store/controller/setting/LineConfig.php`

**操作**:
- [x] 在 `save()` 方法中添加客户联系配置处理逻辑
- [x] 添加 `validateCustomerContact()` 验证方法
- [x] 使用 SettingModel 保存配置到数据库

**验证标准**:
- 能够接收并保存 hotline_th, line_support, wechat 三个字段
- 验证数据格式正确
- 保存成功返回成功消息

#### Task 1.2: 添加 API 接口 ✅
**文件**: `Lineminiapp/source/application/api/controller/Page.php`

**操作**:
- [x] 添加 `customerContact()` 方法获取客户联系配置
- [x] 添加 `CustomerContact()` 驼峰命名别名（ThinkPHP 路由兼容）
- [x] 返回 JSON 格式数据

**验证标准**:
- API 端点 `/api/page/customer_contact` 可访问
- 返回正确的 JSON 格式数据
- 配置不存在时返回空对象

#### Task 1.3: 更新后台视图 ✅
**文件**: `Lineminiapp/source/application/store/view/setting/line_config/index.php`

**操作**:
- [x] 在 tab1 (基础配置) 中添加客户联系配置区域
- [x] 添加 Hotline (TH) 输入框
- [x] 添加 LINE Support 输入框
- [x] 添加 WeChat 输入框
- [x] 添加帮助文本和示例

**验证标准**:
- 后台页面显示客户联系配置区域
- 输入框可以正常输入和保存
- 已保存的数据可以正确回显

### Phase 2: 前端实现 (Frontend)

#### Task 2.1: 创建 CustomerContact 组件 ✅
**文件**: `zalo_mini_app-master/src/components/CustomerContact/Index.jsx`

**操作**:
- [x] 创建 CustomerContact 组件
- [x] 实现 Hotline 按钮（tel: 链接）
- [x] 实现 LINE Support 按钮（line.me 链接）
- [x] 实现 WeChat 显示（复制功能）
- [x] 添加 LINE 主题样式

**验证标准**:
- 组件可以正确渲染
- 所有联系方式都为空时不显示组件
- 点击 Hotline 可以拨打电话
- 点击 LINE 可以打开 LINE 聊天
- 点击 WeChat 可以复制微信号

#### Task 2.2: 集成到 Home 页面 ✅
**文件**: `zalo_mini_app-master/src/pages/Home/Index.jsx`

**操作**:
- [x] 添加 customerContact state
- [x] 在 useEffect 中调用 API 获取配置
- [x] 在 JSX 中添加 CustomerContact 组件
- [x] 调整布局位置

**验证标准**:
- 主页可以正确加载客户联系配置
- CustomerContact 组件正确显示
- 所有功能正常工作

### Phase 3: 测试与优化 (Testing)

#### Task 3.1: 功能测试
- [ ] 测试后台配置保存功能
- [ ] 测试 API 接口返回数据
- [ ] 测试前端组件渲染
- [ ] 测试所有链接和按钮功能
- [ ] 测试数据验证逻辑

#### Task 3.2: 边界测试
- [ ] 测试空数据情况
- [ ] 测试部分数据情况
- [ ] 测试无效数据格式
- [ ] 测试超长文本输入

#### Task 3.3: 用户体验优化
- [ ] 优化移动端显示效果
- [ ] 添加加载状态提示
- [ ] 优化错误提示信息
- [ ] 添加复制成功提示

### Phase 4: 文档与部署 (Documentation)

#### Task 4.1: 更新文档
- [ ] 更新 README.md
- [ ] 添加功能使用说明
- [ ] 添加配置示例

#### Task 4.2: 部署
- [ ] 部署后端代码
- [ ] 部署前端代码
- [ ] 清除缓存
- [ ] 验证生产环境功能

## 实现顺序

1. **后端优先**: 先完成 Phase 1 所有任务
2. **前端跟进**: 完成 Phase 2 所有任务
3. **测试验证**: 完成 Phase 3 所有任务
4. **文档部署**: 完成 Phase 4 所有任务

## 预计时间

- Phase 1: 30 分钟
- Phase 2: 30 分钟
- Phase 3: 20 分钟
- Phase 4: 10 分钟
- **总计**: 约 1.5 小时

## 依赖关系

```
Task 1.1 (LineConfig) ─┐
                       ├─> Task 1.2 (API) ─> Task 2.1 (Component) ─> Task 2.2 (Integration)
Task 1.3 (View) ───────┘                                              │
                                                                       ↓
                                                                   Task 3.x (Testing)
                                                                       ↓
                                                                   Task 4.x (Docs)
```

## 注意事项

1. **数据格式验证**: 确保所有输入数据格式正确
2. **向后兼容**: 不影响现有功能
3. **错误处理**: 完善的错误提示和处理
4. **安全性**: 防止 XSS 攻击，对输出进行转义
5. **性能**: 前端缓存配置数据，减少 API 调用

## 完成标准

- [ ] 所有任务完成并通过测试
- [ ] 代码通过 Code Review
- [ ] 文档更新完成
- [ ] 生产环境验证通过
- [ ] 用户反馈良好
