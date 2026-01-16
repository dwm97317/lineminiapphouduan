# LINE Channel Access Token 获取指南（2024最新）

## 重要说明

**Channel Access Token 不在 LINE Official Account Manager 中！**

Access Token 需要在 **LINE Developers Console** 中获取，这是两个不同的系统：

| 系统 | 用途 | 网址 |
|------|------|------|
| **LINE Official Account Manager** | 管理官方账号、发送消息、查看统计 | https://manager.line.biz/ |
| **LINE Developers Console** | 开发者配置、获取 API 密钥 | https://developers.line.biz/console/ |

---

## 前提条件

### 1. 您需要有 Admin 或 Owner 权限

**Operator（操作员）无法访问 LINE Developers Console！**

如果您是 Operator：
- ❌ 无法登录 LINE Developers Console
- ❌ 无法查看或生成 Access Token
- ✅ 只能在 LINE Official Account Manager 中操作

**解决方案**：请联系管理员获取配置信息，或请求提升权限。

### 2. 必须先启用 Messaging API

在 LINE Official Account Manager 中：
1. 选择您的 Official Account
2. 进入"设置" → "Messaging API"
3. 点击"启用 Messaging API"

启用后，系统会自动在 LINE Developers Console 中创建一个 Messaging API Channel。

---

## 获取 Channel Access Token 的步骤

### 步骤 1: 登录 LINE Developers Console

访问：https://developers.line.biz/console/

使用与 LINE Official Account Manager **相同的账号**登录。

⚠️ **注意**：如果您是 Operator，这一步会失败，因为您没有权限。

### 步骤 2: 选择 Provider 和 Channel

1. 在 LINE Developers Console 首页，选择您的 **Provider**
   - Provider 是在启用 Messaging API 时自动创建的

2. 在 Provider 页面，选择您的 **Messaging API Channel**
   - Channel 名称通常与您的 Official Account 名称相同

### 步骤 3: 进入 Messaging API 标签页

在 Channel 页面，点击顶部的 **"Messaging API"** 标签页。

### 步骤 4: 生成 Channel Access Token

在 Messaging API 标签页中，向下滚动找到：

**"Channel access token (long-lived)"** 部分

您会看到两个选项：

#### 选项 A: 生成新的 Token（首次使用）

如果显示 **"Issue"** 按钮：
1. 点击 **"Issue"** 按钮
2. 系统会生成一个长期有效的 Access Token
3. **立即复制并保存**（只显示一次！）

#### 选项 B: 重新生成 Token（已有 Token）

如果已经有 Token，会显示部分 Token 内容（如 `eyJhbGc...`）：
1. 点击 **"Reissue"** 按钮重新生成
2. ⚠️ 旧 Token 会立即失效
3. 复制新 Token 并保存

---

## Channel Access Token 类型说明

根据 LINE 官方文档，有 4 种类型的 Access Token：

| 类型 | 有效期 | 每个 Channel 可发行数量 | 用途 |
|------|--------|----------------------|------|
| **Long-lived** | 永久有效 | 1 个 | **推荐用于生产环境** |
| **Short-lived** | 30 天 | 30 个 | 临时使用 |
| **User-specified expiration (v2.1)** | 最多 30 天 | 30 个 | 自定义有效期 |
| **Stateless** | 15 分钟 | 无限制 | 短期临时使用 |

### 推荐使用：Long-lived Channel Access Token

对于我们的消息通知系统，**强烈推荐使用 Long-lived Token**：

✅ **优点**：
- 永久有效，不会过期
- 只需配置一次
- 可以随时撤销和重新生成

❌ **缺点**：
- 每个 Channel 只能有 1 个
- 重新生成会使旧 Token 失效

---

## 完整配置信息清单

您需要从 LINE Developers Console 获取以下信息：

### 1. Channel ID
- **位置**：LINE Developers Console → Channel → **Basic settings** 标签页
- **格式**：数字，例如 `1234567890`
- **权限**：Admin/Owner 可见

### 2. Channel Secret
- **位置**：LINE Developers Console → Channel → **Basic settings** 标签页
- **格式**：32位字符串，例如 `abc123def456...`
- **权限**：Admin/Owner 可见

### 3. Channel Access Token (Long-lived)
- **位置**：LINE Developers Console → Channel → **Messaging API** 标签页
- **格式**：长字符串，例如 `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...`
- **权限**：Admin/Owner 可见
- **注意**：生成后只显示一次，必须立即保存

### 4. LIFF URL（可选）
- **位置**：LINE Developers Console → Channel → **LIFF** 标签页
- **格式**：`https://liff.line.me/[LIFF_ID]`
- **说明**：如果需要深层链接功能，需要先创建 LIFF 应用

---

## 截图参考位置

### 在 LINE Developers Console 中的位置

```
LINE Developers Console
└── 选择 Provider
    └── 选择 Messaging API Channel
        ├── Basic settings 标签页
        │   ├── Channel ID ← 在这里
        │   └── Channel Secret ← 在这里
        │
        ├── Messaging API 标签页
        │   └── Channel access token (long-lived) ← 在这里
        │       └── [Issue] 或 [Reissue] 按钮
        │
        └── LIFF 标签页
            └── LIFF apps 列表
                └── Endpoint URL ← LIFF URL 在这里
```

---

## 常见问题

### Q1: 我是 Operator，无法登录 LINE Developers Console

**答**：这是正常的。Operator 角色没有开发者权限。

**解决方案**：
1. 联系您的 Admin 或 Owner
2. 请他们提供配置信息
3. 或请求将您的角色提升为 Admin

### Q2: 我在 LINE Official Account Manager 中找不到 Access Token

**答**：Access Token **不在** LINE Official Account Manager 中！

必须去 **LINE Developers Console** 获取。

### Q3: 我点击"Issue"后没有保存 Token，现在看不到了

**答**：Token 只显示一次。如果没保存，需要重新生成：

1. 点击 **"Reissue"** 按钮
2. 旧 Token 会失效
3. 复制新 Token 并妥善保存

### Q4: 重新生成 Token 会影响现有服务吗？

**答**：会！重新生成后：
- ✅ 新 Token 立即生效
- ❌ 旧 Token 立即失效
- ⚠️ 使用旧 Token 的服务会停止工作

**建议**：
1. 先准备好更新配置
2. 生成新 Token
3. 立即更新系统配置
4. 测试确认新 Token 工作正常

### Q5: Long-lived Token 真的永久有效吗？

**答**：是的，除非：
- 手动撤销（Revoke）
- 重新生成（Reissue）
- Channel 被删除

正常情况下，Long-lived Token 不会过期。

---

## 安全建议

### 1. 保护 Access Token

⚠️ **Access Token 等同于密码！**

- ❌ 不要在前端代码中暴露
- ❌ 不要提交到 Git 仓库
- ❌ 不要在日志中打印完整 Token
- ✅ 使用环境变量或加密存储
- ✅ 定期检查是否泄露

### 2. 如果 Token 泄露

立即执行以下操作：
1. 登录 LINE Developers Console
2. 进入 Messaging API 标签页
3. 点击 **"Reissue"** 重新生成 Token
4. 更新系统配置
5. 检查是否有异常消息发送

### 3. 权限管理

- 只给需要的人 Admin 权限
- 定期审查账号权限
- 离职人员及时移除权限

---

## 配置到系统

获得所有信息后，在后台配置：

```
后台路径: /store/setting.line_config/index

消息通知标签页:
├── 启用消息通知: ✅ 启用
├── Channel ID: [从 Basic settings 获取]
├── Channel Secret: [从 Basic settings 获取]
├── Access Token: [从 Messaging API 标签页获取]
└── LIFF URL: [从 LIFF 标签页获取，可选]
```

---

## 官方文档链接

- LINE Developers Console: https://developers.line.biz/console/
- Channel Access Token 文档: https://developers.line.biz/en/docs/basics/channel-access-token/
- Messaging API 入门: https://developers.line.biz/en/docs/messaging-api/getting-started/

---

## 总结

1. **Access Token 在 LINE Developers Console，不在 LINE Official Account Manager**
2. **需要 Admin 或 Owner 权限才能访问**
3. **Operator 无法获取，需要联系管理员**
4. **推荐使用 Long-lived Token（永久有效）**
5. **Token 只显示一次，必须立即保存**
6. **重新生成会使旧 Token 失效**

如果您是 Operator，请将此文档发送给您的管理员，请他们帮助获取配置信息。
