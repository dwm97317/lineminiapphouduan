# Requirements Document - 客户联系配置功能

## Introduction

为 LINE Mini App 系统添加客户联系信息配置功能，允许商户在后台配置客户服务联系方式（Hotline、LINE Support、WeChat），并在前端主页展示这些信息。

## Glossary

- **System**: LINE Mini App 集运系统
- **Backend**: 商户后台管理系统
- **Frontend**: 用户前端应用（LINE Mini App）
- **Customer_Contact_Config**: 客户联系配置数据
- **Setting_Model**: 系统设置数据模型

## Requirements

### Requirement 1: 后台配置界面

**User Story:** 作为商户管理员，我想在后台 LINE 设置中配置客户联系信息，以便用户可以通过多种渠道联系客服。

#### Acceptance Criteria

1. WHEN 管理员访问后台 LINE 设置页面 THEN THE System SHALL 显示"客户联系"（ฝ่ายบริการลูกค้า）配置区域
2. THE System SHALL 提供以下三个输入字段：
   - Hotline (TH) - 泰国热线电话
   - LINE Support - LINE 官方账号 ID
   - WeChat - 微信客服账号
3. WHEN 管理员输入联系信息并保存 THEN THE System SHALL 验证数据格式并存储到数据库
4. WHEN 保存成功 THEN THE System SHALL 显示成功提示消息
5. WHEN 保存失败 THEN THE System SHALL 显示错误提示消息并保留用户输入

### Requirement 2: 数据存储

**User Story:** 作为系统，我需要安全地存储客户联系配置数据，以便前端可以获取。

#### Acceptance Criteria

1. THE System SHALL 将客户联系配置存储在 `yoshop_setting` 表中
2. THE System SHALL 使用 key 值 `customer_contact` 标识此配置
3. THE System SHALL 以 JSON 格式存储配置数据，包含以下字段：
   ```json
   {
     "hotline_th": "电话号码",
     "line_support": "LINE ID",
     "wechat": "微信号"
   }
   ```
4. WHEN 配置不存在时 THEN THE System SHALL 返回空值或默认值

### Requirement 3: API 接口

**User Story:** 作为前端开发者，我需要一个 API 接口来获取客户联系配置，以便在主页展示。

#### Acceptance Criteria

1. THE System SHALL 提供 API 端点 `api/page/customer_contact` 获取客户联系配置
2. WHEN 前端调用此 API THEN THE System SHALL 返回客户联系配置数据
3. THE System SHALL 返回 JSON 格式响应：
   ```json
   {
     "code": 1,
     "msg": "success",
     "data": {
       "hotline_th": "电话号码",
       "line_support": "LINE ID",
       "wechat": "微信号"
     }
   }
   ```
4. WHEN 配置不存在 THEN THE System SHALL 返回空对象 `{}`
5. THE System SHALL 支持跨域请求（CORS）

### Requirement 4: 前端主页展示

**User Story:** 作为用户，我想在主页看到客服联系方式，以便需要时可以快速联系客服。

#### Acceptance Criteria

1. WHEN 用户访问主页 THEN THE System SHALL 自动加载客户联系配置
2. WHEN 客户联系配置存在 THEN THE System SHALL 在主页显示客服联系区域
3. THE System SHALL 为每个联系方式提供可点击的链接：
   - Hotline: 点击拨打电话 `tel:电话号码`
   - LINE Support: 点击打开 LINE 聊天 `https://line.me/ti/p/~LINE_ID`
   - WeChat: 显示微信号（可复制）
4. WHEN 某个联系方式未配置 THEN THE System SHALL 不显示该联系方式
5. THE System SHALL 使用图标和文字清晰展示每个联系方式

### Requirement 5: 数据验证

**User Story:** 作为系统，我需要验证输入数据的格式，以确保数据质量。

#### Acceptance Criteria

1. WHEN 管理员输入 Hotline THEN THE System SHALL 验证电话号码格式（允许数字、+、-、空格）
2. WHEN 管理员输入 LINE Support THEN THE System SHALL 验证 LINE ID 格式（字母、数字、下划线）
3. WHEN 管理员输入 WeChat THEN THE System SHALL 验证微信号格式（字母、数字、下划线、连字符）
4. THE System SHALL 允许字段为空（可选配置）
5. WHEN 验证失败 THEN THE System SHALL 显示具体的错误提示

### Requirement 6: 权限控制

**User Story:** 作为系统管理员，我需要确保只有授权用户可以修改客户联系配置。

#### Acceptance Criteria

1. WHEN 未登录用户访问配置页面 THEN THE System SHALL 重定向到登录页面
2. WHEN 非管理员用户访问配置页面 THEN THE System SHALL 显示权限不足提示
3. THE System SHALL 记录配置修改日志（修改人、修改时间）

### Requirement 7: 国际化支持

**User Story:** 作为多语言用户，我希望界面文字支持我的语言。

#### Acceptance Criteria

1. THE System SHALL 支持泰语、中文、英语界面
2. THE System SHALL 为所有标签和提示信息提供翻译
3. WHEN 用户切换语言 THEN THE System SHALL 更新界面文字

## Technical Notes

### 数据库表结构
使用现有的 `yoshop_setting` 表：
- `key`: 'customer_contact'
- `values`: JSON 格式存储配置
- `wxapp_id`: 小程序 ID

### API 路由
- 后台保存: `POST /store/setting/line_config/save`
- 前端获取: `GET /api/page/customer_contact`

### 前端组件位置
- 主页: `zalo_mini_app-master/src/pages/Home/Index.jsx`
- 新增组件: `zalo_mini_app-master/src/components/CustomerContact/Index.jsx`

### 后端文件位置
- 控制器: `Lineminiapp/source/application/store/controller/setting/LineConfig.php`
- API 控制器: `Lineminiapp/source/application/api/controller/Page.php`
- 视图: `Lineminiapp/source/application/store/view/setting/line_config/index.php`

## Dependencies

- 依赖现有的 Setting 模型
- 依赖现有的 LINE 配置页面
- 前端依赖 React、Tailwind CSS
- 后端依赖 ThinkPHP 5.0

## Success Criteria

1. 商户可以在后台成功配置客户联系信息
2. 前端主页可以正确获取并展示客户联系信息
3. 所有联系方式链接可以正常工作
4. 界面美观、响应式设计
5. 支持多语言切换
