# 用户规则 - 集运系统 SAAS 项目

## 项目概述
这是一个**集运系统 SAAS 后端项目**（Zalo集运服务），用于管理跨境物流、包裹集运、订单处理等业务。

### 技术栈
- **后端框架**: ThinkPHP 5.0
- **编程语言**: PHP (>=5.4.0)
- **数据库**: MySQL (数据库名: `zalo_zhuanyun`, 表前缀: `yoshop_`)
- **前端**: HTML5 + Vue.js (编译后的静态文件)
- **架构**: MVC架构，多模块应用

## 项目结构

### 核心目录
```
d:\2025profile\Lineminiapp\
├── source/                    # 后端源代码目录
│   ├── application/          # 应用代码
│   │   ├── admin/           # 管理后台模块
│   │   ├── api/             # API接口模块
│   │   ├── store/           # 商户/仓库模块
│   │   ├── task/            # 定时任务模块
│   │   ├── web/             # Web模块
│   │   ├── common/          # 公共模块
│   │   ├── common.php       # 公共函数库
│   │   ├── config.php       # 应用配置
│   │   └── database.php     # 数据库配置
│   ├── thinkphp/            # ThinkPHP框架核心
│   └── vendor/              # Composer依赖
├── web/                      # Web根目录
│   ├── index.php            # 入口文件
│   ├── html5/               # H5前端应用（Vue编译后）
│   ├── pc/                  # PC端页面
│   ├── assets/              # 静态资源
│   ├── uploads/             # 上传文件目录
│   └── lang/                # 多语言文件
└── version.json             # 版本信息 (当前: 1.1.41)
```

## 代码规范

### PHP 编码规范
1. **命名规范**:
   - 类名使用大驼峰命名法 (PascalCase): `OrderModel`, `UserController`
   - 方法名使用小驼峰命名法 (camelCase): `getUserInfo()`, `createOrder()`
   - 变量名使用小驼峰或下划线命名: `$userId`, `$order_id`
   - 数据库表使用下划线命名: `yoshop_order`, `yoshop_user`

2. **ThinkPHP 5.0 约定**:
   - 控制器继承自 `Controller` 基类
   - 模型类放在 `model` 目录
   - 使用命名空间: `namespace app\api\controller;`
   - 默认模块: `store`
   - 默认控制器: `Index`
   - 默认操作: `index`

3. **数据库操作**:
   - 使用 ThinkPHP ORM 进行数据库操作
   - 表前缀: `yoshop_`
   - 自动时间戳: 已启用
   - 时间格式: `Y-m-d H:i:s`

4. **API 响应格式**:
   - 默认返回类型: JSON
   - 使用 `renderSuccess()` 返回成功响应
   - 使用 `renderError()` 返回错误响应

### 安全规范
1. **加密函数**:
   - 使用 `encrypt()` 函数进行加密/解密
   - 密码使用 `yoshop_hash()` 进行哈希
   - 加密密钥配置: `en_key` (默认: 'slowertyy9383764726')

2. **跨域设置**:
   - 已在 `web/index.php` 中配置 CORS
   - 允许的请求方法: GET, POST, PUT, DELETE, OPTIONS, PATCH
   - 允许的头部: Content-Type, Apptype, Sign, timestamp, X-gron, platform

## 业务逻辑

### 核心业务模块
1. **集运订单** (`Inpack`):
   - 订单号生成规则可配置
   - 支持多种订单来源: 集运包裹打包、代购包裹打包、PC包裹生成、直邮订单等
   - 订单状态管理完整

2. **包裹管理** (`Package`):
   - 包裹打包、拆包
   - 包裹图片记录
   - 包裹物流跟踪

3. **用户管理** (`User`):
   - 用户地址管理（集运地址、商城地址、代收点地址）
   - 用户余额、充值
   - 用户线路管理

4. **路线管理** (`Line`):
   - 集运路线配置
   - 运费计算
   - 增值服务费用计算

5. **仓库管理** (`Shop`):
   - 多仓库支持
   - 仓库简称配置

### 订单号生成规则
支持以下组合方式:
- 10: 时间戳
- 20: 年月日 (YYYYMMDD)
- 30: 缩写年月日 (YYMMDD)
- 40: 年月日时分秒
- 50: 用户ID
- 60: 目的地ID
- 70: 仓库简称
- 90: 自定义字母
- 100: 自定义序号
- 110: 随机5位数

## 开发指南

### 添加新功能时
1. **创建控制器**: 在对应模块的 `controller` 目录下创建
2. **创建模型**: 在对应模块的 `model` 目录下创建
3. **数据库表**: 使用前缀 `yoshop_`
4. **路由配置**: 在 `route.php` 中配置（如需要）
5. **权限验证**: 在控制器中使用 `$this->getUser()` 获取当前用户

### 调试
- 调试模式: `app_debug => true` (已启用)
- 日志记录: 使用 `log_write()` 函数
- 日志路径: `source/runtime/log/`
- 使用 `pre()` 函数进行调试输出

### 常用公共函数 (common.php)
- `createSn()`: 生成订单号
- `encrypt($string, $operation)`: 加密/解密
- `yoshop_hash($password)`: 密码哈希
- `base_url()`: 获取当前域名及根路径
- `curl($url, $data)`: GET请求
- `curlPost($url, $data)`: POST请求
- `export_excel()`: 导出Excel
- `getServiceFree()`: 计算增值服务费用

## 多语言支持
- 语言文件位置: `web/lang/10001/zhHans.json` 和 `zhHans.js`
- 当前支持: 简体中文
- 语言切换: 默认关闭 (`lang_switch_on => false`)

## 依赖包
主要 Composer 依赖:
- `topthink/framework`: 5.0.* (ThinkPHP框架)
- `phpmailer/phpmailer`: ^6.6 (邮件发送)
- `qiniu/php-sdk`: ^7.2 (七牛云存储)
- `aliyuncs/oss-sdk-php`: ^2.3 (阿里云OSS)
- `qcloud/cos-sdk-v5`: ^1.2 (腾讯云COS)
- `picqer/php-barcode-generator`: ^2.2 (条形码生成)

## 注意事项

### 修改代码时
1. **不要修改** ThinkPHP 框架核心文件 (`source/thinkphp/`)
2. **不要修改** Composer 依赖文件 (`source/vendor/`)
3. **谨慎修改** 数据库配置文件 (`source/application/database.php`)
4. **备份数据** 在修改数据库结构前

### 性能优化
1. 缓存配置: 使用文件缓存 (可改为 Redis)
2. 数据库调试模式在生产环境应关闭
3. 大数据导出使用 `export_excel()` 函数（已优化内存）

### 安全建议
1. 生产环境关闭调试模式 (`app_debug => false`)
2. 修改默认加密密钥 (`en_key`)
3. 使用强密码配置数据库
4. 定期更新依赖包

## 特殊配置
- **默认小程序ID**: `wxapp_id => 10001`
- **分页配置**: 每页15条记录
- **时区**: PRC (中国)
- **模板引擎**: Think模板引擎，标签使用 `{` 和 `}`

## 前端相关
- H5应用使用 Vue.js 构建
- 编译后的文件在 `web/html5/static/`
- 入口文件: `web/html5/index.html`
- 静态资源: CSS, JS 已压缩和版本化

## 微信小程序开发规范

### 登录与认证流程
1. **登录方式**:
   - **微信小程序登录**: 调用 `POST /api/passport/loginMpWx`
     - 参数: `code` (wx.login获取), `partyData` (包含 encryptedData, iv)
     - 流程: 后端换取 OpenID -> 检查用户是否存在 -> (存在:登录) / (不存在:判断是否强制绑定手机)
     - **注意**: 如果返回 `{isBindMobile: true}`，前端必须跳转到手机绑定页面。
   - **手机号登录**: 调用 `POST /api/passport/loginMpWxMobile`
   - **Zalo小程序登录**: 调用 `POST /api/passport/loginbyzalo` (参数: `accesstoken`)

2. **鉴权机制**:
   - **Token**: 登录成功后返回 `token`。
   - **有效期**: Token 有效期为 **30天** (服务端缓存)。
   - **请求头**: 所有需要鉴权的接口必须在 Header 中携带 `token`。
   - **Token生成算法**: `md5(storeId + timestamp + userId + guid + salt)`

3. **支付流程**:
   - 结算台: `Checkout` 模型处理订单结算。
   - 支付参数: `buyNow` 或 `cart` 接口返回 `payment` 字段，直接用于 `wx.requestPayment`。

### 小程序特定配置
- **API 路由**: 统一使用 `/index.php?s=/api/{controller}/{action}` 格式。
- **AppID 配置**: 在后台管理系统中配置，模型层 `Wxapp` 会自动屏蔽 `app_id` 和 `app_secret` 输出。
- **强制绑定**: 可在后台设置 `isForceBindMpweixin` 控制是否强制新用户绑定手机号。

## 与 AI 协作建议
1. **修改 PHP 代码时**: 遵循 ThinkPHP 5.0 规范和项目现有代码风格
2. **数据库操作**: 使用 ThinkPHP ORM，不要直接写原生SQL（除非必要）
3. **新增功能**: 先了解现有类似功能的实现方式
4. **调试问题**: 检查日志文件 `source/runtime/log/`
5. **API开发**: 参考 `source/application/api/controller/` 下的现有控制器
