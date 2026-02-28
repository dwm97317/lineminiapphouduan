# LINE 配置菜单修复说明

## 🔧 问题
商户后台菜单中看不到 "LINE 配置" 选项。

## ✅ 已修复
已在菜单配置文件中添加 LINE 配置菜单项。

## 📝 修改内容

### 修改文件
`Lineminiapp/source/application/store/extra/menus.php`

### 修改位置
在 `setting` 菜单的 `submenu` 中，在 "支付设置" 之后添加了：

```php
[
    'name' => 'LINE 配置',
    'index' => 'setting.line_config/index',
    'uris' => [
        'setting.line_config/index',
    ],
],
```

## 🎯 菜单位置
修复后，LINE 配置菜单将出现在：
```
设置
├── 系统设置
├── 自定义中心
├── 支付设置
├── LINE 配置  ← 新增
├── 小程序导航
└── ...
```

## 🚀 如何使用

### 1. 清除缓存
修改菜单配置后，需要清除后台缓存：
1. 登录商户后台
2. 进入 **设置 → 其他 → 清理缓存**
3. 点击清理缓存按钮

### 2. 重新登录
1. 退出当前账号
2. 重新登录商户后台

### 3. 访问 LINE 配置
1. 点击左侧菜单 **"设置"**（齿轮图标）
2. 在展开的子菜单中找到 **"LINE 配置"**
3. 点击进入配置页面

## 📋 配置页面功能

配置页面包含 3 个标签页：

### 1️⃣ 基础配置 (LIFF)
- 是否启用 LINE 登录
- LINE Channel ID
- LINE Channel Secret
- LIFF ID ⭐
- Google Maps API Key
- Bot Link 设置

### 2️⃣ 消息通知 (Messaging API)
- 是否启用消息通知
- Channel Access Token
- 包裹入库提醒模板
- 订单发货提醒模板

### 3️⃣ 支付设置 (LINE Pay)
- 是否启用 LINE Pay
- Channel ID
- Channel Secret
- 测试模式开关

## 🔍 验证修复

### 方法 1: 直接访问
在浏览器中访问：
```
https://your-domain.com/store/setting.line_config/index
```

### 方法 2: 检查菜单
1. 登录后台
2. 查看左侧 "设置" 菜单
3. 确认能看到 "LINE 配置" 选项

### 方法 3: 检查 API
访问 API 端点验证配置是否生效：
```
GET /index.php?s=api/LineApp/base&wxapp_id=10001
```

## 📞 技术支持

如果修复后仍然看不到菜单：
1. 检查浏览器控制台是否有 JavaScript 错误
2. 检查服务器 PHP 错误日志
3. 确认当前账号有 "设置" 模块的访问权限
4. 尝试使用管理员账号登录

---

**修复日期**: 2025-01-10  
**修复状态**: ✅ 完成
