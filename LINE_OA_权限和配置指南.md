# LINE Official Account 权限和配置指南

## 问题说明

如果您在 LINE Official Account Manager 中看到：
- ✅ 您是**操作员（Operator）**角色
- ❌ 无法查看 Channel Secret 和 Access Token
- ❌ Messaging API 相关设置显示为灰色或不可访问
- ⭐ 账号显示为灰色一星

这是因为您的权限不足，需要账号管理员（Admin）或所有者（Owner）来配置。

---

## LINE Official Account 角色权限

| 角色 | 权限说明 | 可以做什么 |
|------|---------|-----------|
| **Owner（所有者）** | 最高权限 | 所有操作，包括删除账号 |
| **Admin（管理员）** | 管理权限 | 查看和配置 API 密钥、管理成员 |
| **Operator（操作员）** | 操作权限 | 发送消息、查看统计，**无法查看密钥** |

---

## 解决方案

### 方案 1: 请求管理员提供配置信息（推荐）

联系您的 LINE Official Account 管理员或所有者，请他们提供以下信息：

#### 需要的配置信息

1. **Channel ID**
   - 位置：LINE Developers Console → 选择 Channel → Basic settings
   - 格式：数字，例如 `1234567890`

2. **Channel Secret**
   - 位置：LINE Developers Console → 选择 Channel → Basic settings
   - 格式：32位字符串，例如 `abc123def456...`

3. **Channel Access Token (Long-lived)**
   - 位置：LINE Developers Console → 选择 Channel → Messaging API
   - 需要点击"Issue"按钮生成
   - 格式：长字符串，例如 `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...`
   - ⚠️ **重要**：Token 只显示一次，请妥善保存

4. **LIFF URL**（如果已创建 LIFF 应用）
   - 位置：LINE Developers Console → 选择 Channel → LIFF
   - 格式：`https://liff.line.me/[LIFF_ID]`

#### 请求模板（发送给管理员）

```
您好，

我需要配置 LINE 消息通知功能，请提供以下信息：

1. Channel ID
2. Channel Secret
3. Channel Access Token (Long-lived)
4. LIFF URL（如果有）

这些信息可以在 LINE Developers Console 中找到：
https://developers.line.biz/console/

谢谢！
```

---

### 方案 2: 请求提升权限

如果您需要经常配置和管理 LINE API，可以请求管理员将您的角色提升为 **Admin**。

#### 提升权限步骤（管理员操作）

1. 登录 [LINE Official Account Manager](https://manager.line.biz/)
2. 选择对应的 Official Account
3. 进入"设置" → "权限管理"
4. 找到您的账号
5. 将角色从"Operator"改为"Admin"

---

### 方案 3: 创建新的 Messaging API Channel（需要 Admin 权限）

如果当前 Official Account 没有启用 Messaging API，需要管理员创建：

#### 步骤（管理员操作）

1. **访问 LINE Developers Console**
   - https://developers.line.biz/console/

2. **选择或创建 Provider**
   - 如果没有 Provider，点击"Create"创建一个
   - Provider 名称可以是公司名或项目名

3. **创建 Messaging API Channel**
   - 在 Provider 页面，点击"Create a new channel"
   - 选择"Messaging API"
   - 填写必要信息：
     - Channel name: 频道名称
     - Channel description: 频道描述
     - Category: 选择业务类别
     - Subcategory: 选择子类别
   - 同意条款并创建

4. **获取配置信息**
   - 创建后会自动跳转到 Channel 页面
   - 在"Basic settings"标签页找到：
     - Channel ID
     - Channel Secret
   - 在"Messaging API"标签页：
     - 点击"Issue"生成 Channel Access Token
     - 复制并保存 Token（只显示一次）

5. **关联到 Official Account**
   - 在"Messaging API"标签页
   - 找到"LINE Official Account features"
   - 点击"Link"关联到现有的 Official Account

---

## 灰色一星账号说明

### 什么是灰色一星？

LINE Official Account 有不同的认证等级：

| 等级 | 标识 | 说明 |
|------|------|------|
| **未认证账号** | 灰色一星 ⭐ | 免费账号，功能受限 |
| **认证账号** | 蓝色盾牌 🛡️ | 经过 LINE 认证，功能完整 |
| **高级认证账号** | 绿色盾牌 🛡️ | 企业认证，最高权限 |

### 灰色一星的限制

1. **消息发送限制**
   - 每月免费消息数量有限（通常 500-1000 条）
   - 超出后需要付费

2. **功能限制**
   - 某些高级功能可能不可用
   - API 调用频率可能受限

3. **显示限制**
   - 用户搜索时排名较低
   - 无认证标识

### 是否需要升级？

**对于消息通知功能**：
- ✅ 灰色一星账号**可以使用** Messaging API
- ✅ 可以发送 Flex Message
- ✅ 可以使用本系统的所有功能
- ⚠️ 注意每月免费消息配额

**建议**：
- 如果消息量不大（每月 < 500 条），灰色一星足够
- 如果需要大量发送消息，考虑升级或购买消息包

---

## 配置步骤（获得信息后）

### 1. 登录后台管理系统
```
https://your-domain.com/store/setting.line_config/index
```

### 2. 切换到"消息通知"标签页

### 3. 填写配置信息

```
启用消息通知: ✅ 启用

Channel ID: [管理员提供的 Channel ID]
Channel Secret: [管理员提供的 Channel Secret]
Access Token: [管理员提供的 Access Token]
LIFF URL: [管理员提供的 LIFF URL]

API 设置（使用默认值）:
- API Base URL: https://api.line.me/v2/bot
- 超时时间: 30 秒
- 重试次数: 3 次
- 启用日志: 是
```

### 4. 启用需要的消息模板

勾选需要的消息类型：
- ✅ 📦 包裹入库通知
- ✅ 🚚 发货通知
- ✅ ✅ 支付成功通知
- ✅ 📋 打包完成通知
- ✅ 💰 付款单生成通知
- ✅ 🏪 到仓通知
- ✅ 📤 出库申请通知

### 5. 保存配置

点击"提交保存"按钮。

### 6. 测试消息发送

1. 在任意消息模板中点击"发送测试"
2. 输入测试用户的 LINE User ID
3. 检查 LINE 应用是否收到消息

---

## 常见问题

### Q1: 如何获取测试用户的 LINE User ID？

**方法 1**: 从数据库查询
```sql
SELECT line_user_id FROM yoshop_user WHERE user_id = [用户ID];
```

**方法 2**: 让用户登录系统后查看
- 用户登录后，系统会自动保存 LINE User ID

**方法 3**: 使用 LINE Developers Console
- 在"Messaging API"标签页
- 使用"Bot"功能发送测试消息
- 查看 Webhook 日志获取 User ID

### Q2: Access Token 过期了怎么办？

**Channel Access Token (Long-lived)** 不会过期，除非：
- 手动撤销
- 重新生成新的 Token

如果 Token 失效：
1. 联系管理员重新生成
2. 在后台更新新的 Token

### Q3: 消息发送失败怎么办？

检查以下几点：
1. ✅ 配置信息是否正确
2. ✅ 消息模板是否已启用
3. ✅ 用户是否已关注 Official Account
4. ✅ 用户的 LINE User ID 是否正确
5. ✅ 查看日志文件中的错误信息

### Q4: 如何查看消息发送日志？

```bash
# 查看今天的日志
tail -f runtime/log/$(date +%Y%m%d).log | grep "LINE消息"

# 查看最近的 LINE 相关日志
grep "LINE" runtime/log/*.log | tail -20
```

---

## 联系管理员模板

如果您需要联系管理员，可以使用以下模板：

```
主题：请求 LINE Messaging API 配置信息

您好，

我正在配置 LINE 消息通知功能，需要以下信息：

1. LINE Channel ID
2. LINE Channel Secret
3. LINE Channel Access Token (Long-lived)
4. LIFF URL（如果已创建）

这些信息可以在 LINE Developers Console 中找到：
https://developers.line.biz/console/

如果您不确定如何获取，我可以提供详细的操作指南。

另外，如果可能的话，希望能将我的账号权限提升为 Admin，
以便我可以直接管理 LINE API 配置。

谢谢！

[您的姓名]
```

---

## 相关链接

- LINE Developers Console: https://developers.line.biz/console/
- LINE Official Account Manager: https://manager.line.biz/
- Messaging API 文档: https://developers.line.biz/en/docs/messaging-api/
- Flex Message Simulator: https://developers.line.biz/flex-simulator/

---

## 总结

作为操作员，您无法直接查看 API 密钥，这是正常的权限限制。请联系您的 LINE Official Account 管理员获取必要的配置信息，或请求提升权限。

灰色一星账号完全可以使用 Messaging API 功能，只是每月有免费消息配额限制。对于大多数应用场景，这已经足够使用。

如有其他问题，请随时咨询！
