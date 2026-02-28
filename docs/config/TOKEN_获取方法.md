# Token 获取方法（3种方式）

## ✅ 方法1: 自动获取（最简单，推荐）

测试页面已经支持自动从 localStorage 获取 token！

### 步骤：
1. **先登录前端应用**
   - 访问: `http://localhost:3000` (或你的前端地址)
   - 使用 LINE 登录

2. **然后访问测试页面**
   - 访问: `http://localhost/test_coupon_api.html`
   - 页面会自动使用你的登录状态
   - Token 输入框留空即可

3. **开始测试**
   - 点击"获取列表"按钮
   - 如果能看到优惠券列表，说明 token 有效 ✅

---

## 🔍 方法2: 手动从浏览器获取

如果自动获取失败，可以手动复制 token：

### 步骤：
1. **登录前端应用**
   - 访问前端应用并登录

2. **打开开发者工具**
   - 按 `F12` 键

3. **查看 localStorage**
   - 切换到 **Console (控制台)** 标签
   - 输入并执行：
   ```javascript
   localStorage.getItem('token')
   ```
   - 复制输出的 token 值

4. **使用 Token**
   - 粘贴到测试页面的 Token 输入框
   - 或者在 PHP 脚本中使用

### 示例：
```javascript
// 在浏览器控制台执行
localStorage.getItem('token')
// 输出: "eyJ1c2VyX2lkIjoxLCJ3eGFwcF9pZCI6MTAwMDF9..."
```

---

## 💻 方法3: 使用 PHP 脚本生成

如果前端应用无法访问，可以用脚本生成测试 token：

### 步骤：
```bash
cd Lineminiapp
php get_user_token.php
```

### 脚本功能：
1. 列出所有用户
2. 选择一个用户
3. 生成该用户的 token
4. 保存到 `test_token.txt`
5. 可选：立即验证 token

---

## 🎯 快速测试流程

### 完整流程：
```
1. 登录前端应用
   ↓
2. 访问测试页面 (自动获取 token)
   ↓
3. 点击"运行完整测试"
   ↓
4. 查看测试结果
```

### 如果遇到问题：
- ❌ 提示"未检测到 Token" → 先登录前端应用
- ❌ 提示"Token 无效" → Token 可能过期，重新登录
- ❌ 无法访问前端 → 使用方法3生成 token

---

## 📋 Token 存储位置

根据前端代码 (`src/utils/request.js`)：

```javascript
// Token 存储在 localStorage
const token = localStorage.getItem("token");

// Token 通过 URL 参数传递给后端
config.params["token"] = token;
```

**存储位置**: `localStorage.token`
**传递方式**: URL 参数 `?token=xxx`

---

## 🔧 调试技巧

### 检查 Token 是否存在：
```javascript
// 在浏览器控制台执行
console.log('Token:', localStorage.getItem('token'));
console.log('Token 长度:', localStorage.getItem('token')?.length);
```

### 查看所有 localStorage 数据：
```javascript
// 在浏览器控制台执行
Object.keys(localStorage).forEach(key => {
    console.log(key + ':', localStorage.getItem(key));
});
```

### 测试 Token 是否有效：
```javascript
// 在浏览器控制台执行
fetch('/api/coupon/lists?token=' + localStorage.getItem('token'))
    .then(res => res.json())
    .then(data => console.log('API 响应:', data));
```

---

## ⚠️ 常见问题

### Q: 测试页面提示"未检测到 Token"
**A**: 先登录前端应用，然后刷新测试页面

### Q: Token 过期了怎么办？
**A**: 重新登录前端应用即可获取新 token

### Q: 可以在不同浏览器使用同一个 Token 吗？
**A**: 可以，但建议在同一浏览器中测试

### Q: Token 的有效期是多久？
**A**: 取决于后端配置，通常是 7-30 天

---

## 🎉 推荐方式

**最简单**: 方法1（自动获取）
- ✅ 无需手动操作
- ✅ 自动同步登录状态
- ✅ 最接近真实使用场景

**最灵活**: 方法2（手动获取）
- ✅ 可以跨浏览器使用
- ✅ 可以长期保存
- ✅ 适合脚本测试

**最独立**: 方法3（脚本生成）
- ✅ 不依赖前端应用
- ✅ 可以批量生成
- ✅ 适合自动化测试
