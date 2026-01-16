# 如何获取用户 Token

## 🎯 推荐方法：从浏览器获取（最简单）

### 步骤1: 登录前端应用
1. 打开浏览器访问前端应用
2. 使用 LINE 登录或其他方式登录系统

### 步骤2: 打开开发者工具
- **Chrome/Edge**: 按 `F12` 或 `Ctrl+Shift+I`
- **Firefox**: 按 `F12`
- **Safari**: `Cmd+Option+I` (Mac)

### 步骤3: 查看网络请求
1. 切换到 **Network (网络)** 标签
2. 刷新页面或进行任何操作
3. 在请求列表中找到任何 API 请求（如 `/api/user/index`）
4. 点击该请求

### 步骤4: 查看请求头
1. 在右侧面板中找到 **Headers (请求头)** 标签
2. 向下滚动找到 **Request Headers (请求标头)**
3. 找到 `token` 字段，复制其值

**示例**:
```
Request Headers:
  token: eyJ1c2VyX2lkIjoxLCJ3eGFwcF9pZCI6MTAwMDEsInRpbWVzdGFtcCI6MTczNzAxNDQwMH0=
```

### 步骤5: 使用 Token
将复制的 token 粘贴到测试页面的 Token 输入框中。

---

## 🔧 方法2: 从 localStorage 获取

### 在浏览器控制台执行：

```javascript
// 查看所有存储的数据
console.log(localStorage);

// 查看 token
console.log(localStorage.getItem('token'));

// 或者查看所有可能的 key
Object.keys(localStorage).forEach(key => {
    console.log(key + ':', localStorage.getItem(key));
});
```

---

## 💻 方法3: 使用 PHP 脚本生成

```bash
cd Lineminiapp
php get_user_token.php
```

这个脚本会：
1. 列出所有用户
2. 让你选择一个用户
3. 生成该用户的 Token
4. 保存到 `test_token.txt` 文件
5. 可选：立即验证 Token 是否有效

---

## 🔍 方法4: 从数据库查询现有 Token

如果系统将 token 存储在数据库中：

```sql
-- 查询用户表中的 token 字段（如果有）
SELECT user_id, nickName, token 
FROM yoshop_user 
WHERE user_id = 1;

-- 或者查询 session 表
SELECT * FROM yoshop_session 
WHERE user_id = 1 
ORDER BY create_time DESC 
LIMIT 1;
```

---

## 📱 方法5: 从前端代码查看 Token 存储位置

查看前端代码中 token 的存储方式：

```javascript
// 查看 src/utils/request.js 或类似文件
// 通常会有类似的代码：

// 从 localStorage 获取
const token = localStorage.getItem('token');

// 从 sessionStorage 获取
const token = sessionStorage.getItem('token');

// 从 cookie 获取
const token = document.cookie.split('; ')
    .find(row => row.startsWith('token='))
    ?.split('=')[1];
```

---

## ⚡ 快速测试（无需 Token）

如果你只是想快速测试接口，可以：

### 方法A: 直接在前端应用中测试
1. 登录前端应用
2. 打开浏览器控制台
3. 执行以下代码：

```javascript
// 测试获取优惠券列表
fetch('/api/coupon/lists')
    .then(res => res.json())
    .then(data => console.log(data));

// 测试领取优惠券
const formData = new FormData();
formData.append('coupon_id', 1);
fetch('/api/coupon/receive', {
    method: 'POST',
    body: formData
})
    .then(res => res.json())
    .then(data => console.log(data));
```

### 方法B: 修改测试页面自动获取 Token
测试页面 `test_coupon_api.html` 已经支持自动使用当前登录状态，只需：
1. 先登录前端应用
2. 然后访问测试页面
3. 留空 Token 输入框即可

---

## 🎓 Token 工作原理

### 前端发送请求时：
```javascript
fetch('/api/coupon/lists', {
    headers: {
        'token': 'YOUR_TOKEN_HERE'
    }
})
```

### 后端验证 Token：
```php
// 在 Controller 基类中
protected function getUser($isForce = true)
{
    $token = request()->header('token');
    // 解析 token 获取用户信息
    // 返回用户对象
}
```

---

## 🔐 安全提示

1. **不要分享你的 Token**: Token 相当于你的登录凭证
2. **Token 有效期**: 通常 token 会过期，需要重新登录获取
3. **测试环境**: 建议在测试环境使用测试账号的 token
4. **生产环境**: 不要在生产环境随意测试

---

## 📝 常见问题

### Q: Token 在哪里存储？
A: 通常存储在：
- localStorage (最常见)
- sessionStorage
- Cookie
- 内存中（Recoil/Redux state）

### Q: Token 格式是什么？
A: 常见格式：
- JWT: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...`
- Base64: `eyJ1c2VyX2lkIjoxLCJ3eGFwcF9pZCI6MTAwMDF9`
- 简单字符串: `abc123def456`

### Q: Token 过期了怎么办？
A: 重新登录前端应用，获取新的 token

### Q: 测试时提示 "token 无效"？
A: 检查：
1. Token 是否完整复制
2. Token 是否过期
3. 用户是否存在
4. 后端 token 验证逻辑

---

## 🚀 推荐测试流程

1. **最简单**: 登录前端 → 访问测试页面（自动使用登录状态）
2. **需要 Token**: 登录前端 → F12 查看网络请求 → 复制 token
3. **脚本生成**: 运行 `php get_user_token.php`
