# 账户绑定功能实现文档 (FB/IG Bot)

## 📋 功能概述

实现 FB/IG Bot 中用户输入 Customer ID 后，系统自动验证并与运单系统账户关联的功能。

### 核心流程
1. 用户在 FB/IG Bot 中输入 Customer ID
2. Bot 调用后端 API `/api/v1/account/bind` 进行验证和绑定
3. 后端调用 Bot API `GET /api/bot/customer/verify` 验证 Customer ID
4. 验证成功后保存到 `platform_account` 表
5. Bot 返回确认消息，显示匿名化的用户名

---

## 🗄️ 数据库设计

### 表结构：`yoshop_platform_account`

| 字段名 | 类型 | 说明 | 默认值 |
|--------|------|------|--------|
| id | int(11) UNSIGNED | 主键 ID | AUTO_INCREMENT |
| user_id | int(11) UNSIGNED | 用户 ID (关联 yoshop_user) | NOT NULL |
| platform_type | varchar(20) | 平台类型：FACEBOOK/INSTAGRAM | 'FACEBOOK' |
| customer_id | varchar(100) | Bot Customer ID | NOT NULL |
| customer_name | varchar(255) | 客户名称 | NULL |
| is_anonymized | tinyint(1) UNSIGNED | 是否匿名化显示 | 1 |
| binding_time | timestamp | 绑定时间 | CURRENT_TIMESTAMP |
| last_verify_time | timestamp | 最后验证时间 | NULL |
| status | tinyint(1) UNSIGNED | 状态：1=有效，0=无效 | 1 |
| wxapp_id | int(11) UNSIGNED | 小程序/商户 ID | 10001 |
| create_time | timestamp | 创建时间 | CURRENT_TIMESTAMP |
| update_time | timestamp | 更新时间 | CURRENT_TIMESTAMP |

### 索引设计
- **主键**: `id`
- **唯一索引**: 
  - `customer_id + wxapp_id` (同一 Customer ID 不能重复绑定)
  - `user_id + platform_type + wxapp_id` (每个平台只能绑定一个账户)
- **普通索引**: `user_id`, `platform_type`, `wxapp_id`

### SQL 迁移文件
位置：`bot/database/migrations/001_create_platform_account.sql`

执行方式：
```bash
mysql -u username -p database_name < bot/database/migrations/001_create_platform_account.sql
```

---

## 🔧 技术实现

### 1. Model 层：PlatformAccount

**文件位置**: `source/application/api/model/PlatformAccount.php`

#### 主要方法

```php
// 通过 Customer ID 获取绑定记录
PlatformAccount::getByCustomerId($customerId, $wxappId);

// 通过 User ID 获取绑定记录
PlatformAccount::getByUserId($userId, $wxappId);

// 检查 Customer ID 是否已被绑定
PlatformAccount::isCustomerBound($customerId, $wxappId);

// 检查用户是否已绑定某个平台的账户
PlatformAccount::isUserBound($userId, $platformType, $wxappId);

// 创建新的绑定关系
PlatformAccount::createBinding($data);

// 更新验证时间
PlatformAccount::updateVerifyTime($id);

// 匿名化显示客户名称
PlatformAccount::anonymizeName($name);
```

---

### 2. Service 层：CustomerVerify

**文件位置**: `source/application/api/service/bot/CustomerVerify.php`

#### 核心方法

```php
/**
 * 验证 Customer ID
 * 调用 Bot API: GET /api/bot/customer/verify
 * 
 * @param string $customerId
 * @param string $platformType
 * @return array ['success' => bool, 'message' => string, 'data' => array]
 */
CustomerVerifyService::verifyCustomerId($customerId, $platformType);
```

#### Bot API 响应格式

**成功响应**:
```json
{
  "success": true,
  "message": "验证成功",
  "data": {
    "customer_id": "CUST_123456",
    "customer_name": "John Doe",
    "platform": "facebook",
    "is_valid": true
  }
}
```

**失败响应**:
```json
{
  "success": false,
  "message": "Customer ID 不存在或已过期",
  "data": {}
}
```

#### 配置 Bot API 地址

在 `CustomerVerify.php` 中修改：
```php
const BOT_API_BASE_URL = 'http://your-bot-server.com/api/bot';
```

---

### 3. Controller 层：Account

**文件位置**: `source/application/api/controller/Account.php`

#### API Endpoints

##### 1. 绑定账户
```
POST /api/v1/account/bind
```

**请求参数**:
```json
{
  "wxapp_id": "10001",
  "token": "user_login_token",
  "customer_id": "CUST_123456",
  "platform_type": "FACEBOOK"
}
```

**成功响应**:
```json
{
  "code": 1,
  "msg": "绑定成功！已关联账户：J***",
  "data": {
    "customer_id": "CUST_123456",
    "platform_type": "FACEBOOK",
    "customer_name_anonymized": "J***",
    "binding_time": "2026-03-20 10:30:00"
  }
}
```

**错误响应**:
```json
{
  "code": 0,
  "msg": "该 Customer ID 已被其他账户绑定",
  "data": {}
}
```

---

##### 2. 查询绑定列表
```
GET /api/v1/account/bindings
```

**请求参数**:
```json
{
  "wxapp_id": "10001",
  "token": "user_login_token"
}
```

**响应**:
```json
{
  "code": 1,
  "msg": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "platform_type": "FACEBOOK",
        "customer_id": "CUST_123456",
        "customer_name_anonymized": "J***",
        "binding_time": "2026-03-20 10:30:00",
        "last_verify_time": "2026-03-20 10:30:00",
        "status": 1
      }
    ],
    "total": 1
  }
}
```

---

##### 3. 解绑账户
```
POST /api/v1/account/unbind
```

**请求参数**:
```json
{
  "wxapp_id": "10001",
  "token": "user_login_token",
  "id": 1
}
```

**响应**:
```json
{
  "code": 1,
  "msg": "解绑成功",
  "data": []
}
```

---

##### 4. 验证 Customer ID（独立接口）
```
POST /api/v1/account/verify-customer
```

**请求参数**:
```json
{
  "wxapp_id": "10001",
  "token": "user_login_token",
  "customer_id": "CUST_123456",
  "platform_type": "FACEBOOK"
}
```

**响应**:
```json
{
  "code": 1,
  "msg": "验证成功",
  "data": {
    "customer_id": "CUST_123456",
    "platform_type": "FACEBOOK",
    "customer_name_anonymized": "J***",
    "verified": true
  }
}
```

---

## 🎯 业务逻辑详解

### 1. 验证流程

```
用户输入 Customer ID
    ↓
前端发送请求到 /api/v1/account/bind
    ↓
验证必要参数 (customer_id, token)
    ↓
检查登录状态
    ↓
检查 Customer ID 是否已被绑定 → 已绑定 → 返回错误
    ↓ 未绑定
检查用户是否已绑定同平台账户 → 已绑定 → 返回错误
    ↓ 未绑定
调用 Bot API: GET /api/bot/customer/verify
    ↓
Bot API 验证失败 → 返回错误消息
    ↓ 验证成功
获取客户信息并匿名化处理
    ↓
保存到 platform_account 表
    ↓
返回成功响应（含匿名化用户名）
```

### 2. 匿名化规则

```php
// 示例
"John Doe" → "J***"
"李明" → "李***"
"A" → "A***"
"" → "***"
```

实现代码：
```php
public static function anonymizeName($name)
{
    if (empty($name)) {
        return '***';
    }
    
    $length = mb_strlen($name, 'UTF-8');
    if ($length <= 2) {
        return mb_substr($name, 0, 1, 'UTF-8') . '***';
    }
    
    return mb_substr($name, 0, 1, 'UTF-8') . '***';
}
```

---

## ⚠️ 错误处理

### 错误类型及消息

| 错误场景 | 错误消息 | HTTP Code |
|----------|----------|-----------|
| 缺少 Customer ID | "请输入 Customer ID" | 200 |
| 未登录 | "请先登录" | 200 |
| 不支持的平台类型 | "不支持的平台类型" | 200 |
| Customer ID 已被绑定 | "该 Customer ID 已被其他账户绑定" | 200 |
| 用户已绑定该平台 | "您已绑定该平台的账户，一个平台只能绑定一个 Customer ID" | 200 |
| Bot API 验证失败 | Bot API 返回的错误消息 | 200 |
| Bot 服务连接失败 | "Bot 服务连接失败，请稍后重试" | 200 |
| 数据库操作失败 | "绑定失败：{错误详情}" | 200 |

---

## 🧪 测试指南

### 1. 单元测试文件

**文件位置**: `test_account_bind.php`

**配置项**:
```php
$BASE_URL = 'http://localhost/web/index.php'; // 修改为实际地址
$WXAPP_ID = '10001';
$TOKEN = 'YOUR_TEST_TOKEN'; // 替换为实际 token
```

**运行测试**:
```bash
php test_account_bind.php
```

### 2. 测试用例

#### Test 1: 绑定有效的 Customer ID
- **输入**: customer_id='CUST_TEST_001', platform_type='FACEBOOK'
- **预期**: 绑定成功，返回匿名化用户名

#### Test 2: Customer ID 为空
- **输入**: customer_id=''
- **预期**: 返回错误 "请输入 Customer ID"

#### Test 3: 未登录状态
- **输入**: token=''
- **预期**: 返回错误 "请先登录"

#### Test 4: 查询绑定列表
- **预期**: 返回已绑定的账户列表

#### Test 5: 验证 Customer ID（不绑定）
- **预期**: 验证成功，返回匿名化用户名

---

## 📝 使用示例

### Bot 端集成示例 (Node.js)

```javascript
// Bot 收到用户的 Customer ID 后
async function handleCustomerIdInput(userId, customerId) {
  try {
    // 调用后端绑定 API
    const response = await axios.post('http://your-backend.com/api/v1/account/bind', {
      wxapp_id: '10001',
      token: userId, // 使用用户 ID 作为 token
      customer_id: customerId,
      platform_type: 'FACEBOOK'
    });
    
    const result = response.data;
    
    if (result.code === 1) {
      // 绑定成功
      const anonymizedName = result.data.customer_name_anonymized;
      
      // 发送确认消息给用户
      await sendTextMessage(userId, 
        `✅ 绑定成功！\n\n您的账户已关联：${anonymizedName}\n\n现在您可以使用所有功能了！`
      );
    } else {
      // 绑定失败
      await sendTextMessage(userId, 
        `❌ 绑定失败\n\n原因：${result.msg}\n\n请检查您的 Customer ID 是否正确。`
      );
    }
  } catch (error) {
    console.error('绑定失败:', error);
    await sendTextMessage(userId, 
      `❌ 系统错误\n\n请稍后重试或联系客服。`
    );
  }
}
```

### 前端集成示例 (Vue.js)

```vue
<template>
  <div class="account-bind">
    <input v-model="customerId" placeholder="请输入 Customer ID" />
    <select v-model="platformType">
      <option value="FACEBOOK">Facebook</option>
      <option value="INSTAGRAM">Instagram</option>
    </select>
    <button @click="handleBind">绑定账户</button>
    
    <div v-if="bindResult" class="result">
      <p>{{ bindResult.message }}</p>
      <p v-if="bindResult.success">已关联账户：{{ bindResult.anonymizedName }}</p>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      customerId: '',
      platformType: 'FACEBOOK',
      bindResult: null
    };
  },
  methods: {
    async handleBind() {
      try {
        const response = await this.$http.post('/api/account/bind', {
          customer_id: this.customerId,
          platform_type: this.platformType
        });
        
        const result = response.data;
        
        if (result.code === 1) {
          this.bindResult = {
            success: true,
            message: result.msg,
            anonymizedName: result.data.customer_name_anonymized
          };
        } else {
          this.bindResult = {
            success: false,
            message: result.msg
          };
        }
      } catch (error) {
        this.bindResult = {
          success: false,
          message: '绑定失败，请稍后重试'
        };
      }
    }
  }
};
</script>
```

---

## 🔐 安全考虑

### 1. Token 验证
- 所有 API 都需要用户提供有效的 token
- Token 有效期 30 天（跟随现有系统）

### 2. 数据隔离
- 通过 `wxapp_id` 实现多租户数据隔离
- 每个商户的数据互不影响

### 3. 防止重复绑定
- 数据库唯一索引保证 Customer ID 不重复
- 业务逻辑检查防止用户重复绑定同平台

### 4. 日志记录
- 所有验证请求都记录到日志
- 便于问题排查和审计

---

## 📊 监控和日志

### 日志记录点

1. **Bot API 调用失败**
   ```php
   \think\Log::record('[Bot Customer Verify] Error: ...', 'error');
   ```

2. **HTTP 请求错误**
   ```php
   \think\Log::record('[Bot HTTP Request] cURL Error: ...', 'error');
   ```

3. **绑定成功/失败**
   - 记录用户 ID、Customer ID、平台类型

### 监控指标

- 绑定成功率
- Bot API 响应时间
- 错误类型分布

---

## 🚀 部署步骤

### 1. 数据库迁移
```bash
mysql -u username -p database_name < bot/database/migrations/001_create_platform_account.sql
```

### 2. 配置 Bot API 地址
编辑 `source/application/api/service/bot/CustomerVerify.php`:
```php
const BOT_API_BASE_URL = 'http://your-bot-server.com/api/bot';
```

### 3. 清除缓存
```bash
php clear_cache.php
```

### 4. 测试验证
```bash
php test_account_bind.php
```

---

## 📞 常见问题

### Q1: Bot API 地址在哪里配置？
A: 在 `CustomerVerify.php` 的 `BOT_API_BASE_URL` 常量中配置。

### Q2: 如何支持更多平台（如 Zalo、Telegram）？
A: 在 `$allowedPlatforms` 数组中添加新平台即可：
```php
$allowedPlatforms = ['FACEBOOK', 'INSTAGRAM', 'ZALO', 'TELEGRAM'];
```

### Q3: 匿名化规则可以自定义吗？
A: 可以修改 `PlatformAccount::anonymizeName()` 方法来自定义匿名化规则。

### Q4: 如何处理 Bot API 超时？
A: 调整 `sendGetRequest()` 方法中的 `$timeout` 参数（默认 10 秒）。

---

## 📋 待办事项

- [ ] 配置生产环境 Bot API 地址
- [ ] 添加 Bot API 重试机制
- [ ] 实现绑定到期提醒功能
- [ ] 添加批量导入 Customer ID 功能
- [ ] 优化匿名化显示规则

---

## 📄 相关文件清单

### 新增文件
1. `bot/database/migrations/001_create_platform_account.sql` - 数据库迁移
2. `source/application/api/model/PlatformAccount.php` - Model 层
3. `source/application/api/service/bot/CustomerVerify.php` - Service 层
4. `source/application/api/controller/Account.php` - Controller 层
5. `test_account_bind.php` - 测试脚本
6. `ACCOUNT_BIND_IMPLEMENTATION.md` - 本文档

### 依赖文件
- `source/application/common/model/BaseModel.php` - 基础 Model
- `source/application/api/controller/Controller.php` - 基础 Controller

---

## ✅ 验收标准

- [x] 用户可以成功绑定有效的 Customer ID
- [x] 绑定后显示匿名化的用户名
- [x] Customer ID 不能重复绑定
- [x] 一个平台只能绑定一个 Customer ID
- [x] 未登录用户无法绑定
- [x] Bot API 验证失败时返回友好错误提示
- [x] 支持查询已绑定的账户列表
- [x] 支持解绑账户
- [x] 所有 API 都有完善的错误处理

---

**版本**: v1.0  
**最后更新**: 2026-03-20  
**维护者**: Development Team
