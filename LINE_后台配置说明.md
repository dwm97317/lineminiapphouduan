# LINE 后台配置说明

## 📍 配置位置

LINE 的 LIFF ID 和相关配置在商户后台的以下位置：

### 访问路径
```
商户后台 → 设置 → LINE 配置
或直接访问：/store/setting.line_config/index
```

### 菜单位置
在商户后台左侧菜单中：
1. 点击 **"设置"** 菜单（齿轮图标）
2. 在展开的子菜单中找到 **"LINE 配置"**（位于"支付设置"和"小程序导航"之间）
3. 点击进入 LINE 配置页面

> **注意**: 如果看不到 "LINE 配置" 菜单项，请清除后台缓存后重新登录。

---

## 🔧 配置页面说明

配置页面分为 **3 个标签页**：

### 1️⃣ 基础配置 (LIFF)

这是最重要的配置，包含 LINE Mini App 的核心设置：

| 配置项 | 字段名 | 说明 | 必填 |
|--------|--------|------|------|
| **是否启用 LINE 登录** | `line_config[is_enable]` | 启用/禁用 LINE 登录功能 | ✅ 是 |
| **LINE Channel ID** | `line_config[channel_id]` | LINE Login Channel 的 Channel ID | ✅ 是 |
| **LINE Channel Secret** | `line_config[channel_secret]` | LINE Login Channel 的 Channel Secret | ✅ 是 |
| **LIFF ID** | `line_config[liff_id]` | LINE LIFF 应用的 ID（格式：1234567890-abcdefgh） | ✅ 是 |
| **Google Maps API Key** | `line_config[google_maps_key]` | 用于地址定位和搜索辅助功能 | ⚠️ 推荐 |
| **Bot Link** | `line_config[bot_link]` | 用户登录时是否提示关注 LINE 官方账号<br>- Off: 关闭<br>- Normal: 正常<br>- Aggressive: 激进 | ❌ 否 |

### 2️⃣ 消息通知 (Messaging API)

用于配置 LINE 消息推送功能：

| 配置项 | 字段名 | 说明 |
|--------|--------|------|
| **是否启用消息通知** | `line_messaging[is_enable]` | 开启/关闭消息推送功能 |
| **Channel Access Token** | `line_messaging[access_token]` | Messaging API 的 Channel Access Token |
| **包裹入库提醒** | `line_messaging[template][enter]` | 可用变量: ${code}, ${warehouse} |
| **订单发货提醒** | `line_messaging[template][delivery]` | 可用变量: ${order_no}, ${express_no} |

### 3️⃣ 支付设置 (LINE Pay)

用于配置 LINE Pay 支付功能：

| 配置项 | 字段名 | 说明 |
|--------|--------|------|
| **是否启用 LINE Pay** | `line_pay[is_enable]` | 启用/禁用 LINE Pay 支付 |
| **Channel ID** | `line_pay[channel_id]` | LINE Pay 的 Channel ID |
| **Channel Secret** | `line_pay[channel_secret]` | LINE Pay 的 Channel Secret |
| **测试模式** | `line_pay[is_test]` | 开启 (Sandbox) / 关闭 (Production) |

---

## 🚀 快速配置步骤

### 第一步：获取 LINE 开发者信息

1. 访问 [LINE Developers Console](https://developers.line.biz/console/)
2. 创建 Provider（如果还没有）
3. 创建 LINE Login Channel
4. 在 LIFF 标签页创建 LIFF App
5. 记录以下信息：
   - **Channel ID**
   - **Channel Secret**
   - **LIFF ID**（格式：1234567890-abcdefgh）

### 第二步：获取 Google Maps API Key

1. 访问 [Google Cloud Console](https://console.cloud.google.com/)
2. 创建项目
3. 启用以下 API：
   - Maps JavaScript API
   - Geocoding API
   - Places API
4. 创建 API Key 并设置限制

### 第三步：在商户后台配置

1. 登录商户后台
2. 进入 **设置 → LINE 配置**
3. 在 **基础配置 (LIFF)** 标签页填写：
   ```
   是否启用 LINE 登录: 启用
   LINE Channel ID: [从 LINE Developers 获取]
   LINE Channel Secret: [从 LINE Developers 获取]
   LIFF ID: [从 LINE Developers 获取]
   Google Maps API Key: [从 Google Cloud 获取]
   Bot Link: Off (或根据需要选择)
   ```
4. 点击 **保存 LIFF 配置**

### 第四步：验证配置

前端应用会通过以下 API 获取配置：
```
GET /index.php?s=api/LineApp/base&wxapp_id=10001
```

返回示例：
```json
{
  "code": 1,
  "data": {
    "config": {
      "is_enable": 1,
      "liff_id": "1234567890-abcdefgh",
      "channel_id": "1234567890",
      "liff_size": "full",
      "scopes": ["profile", "openid"],
      "google_maps_key": "AIzaSy...",
      "bot_link": "Off"
    }
  }
}
```

---

## 📂 相关文件位置

### 后台文件
- **配置页面视图**: `source/application/store/view/setting/line_config/index.php`
- **配置控制器**: `source/application/store/controller/setting/LineConfig.php`
- **配置模型**: `source/application/common/model/Setting.php`
- **API 控制器**: `source/application/api/controller/LineApp.php`
- **API 模型**: `source/application/api/model/LineApp.php`

### 数据库
配置存储在 `setting` 表中：
- `line_config`: LIFF 基础配置
- `line_messaging`: 消息通知配置
- `line_pay`: 支付配置

---

## 🔍 常见问题

### Q1: 找不到 LINE 配置菜单？
**A**: 
1. 确保已经修改了菜单配置文件 `source/application/store/extra/menus.php`
2. 清除后台缓存：进入 **设置 → 其他 → 清理缓存**
3. 退出后台并重新登录
4. 检查当前账号是否有权限访问 "设置" 模块

### Q2: 保存后前端获取不到配置？
**A**: 
1. 检查 `wxapp_id` 是否正确（默认为 10001）
2. 检查数据库 `setting` 表中是否有 `line_config` 记录
3. 清除后台缓存

### Q3: LIFF ID 格式是什么？
**A**: LIFF ID 格式为：`数字-字母` 组合，例如：`1234567890-abcdefgh`

### Q4: Google Maps API Key 是必须的吗？
**A**: 如果需要使用地址自动完成和地理编码功能，则必须配置。否则地址功能将无法正常工作。

### Q5: 如何测试配置是否正确？
**A**: 
1. 访问 API 端点：`/index.php?s=api/LineApp/base&wxapp_id=10001`
2. 检查返回的 JSON 数据是否包含正确的配置信息
3. 在 LINE 应用中打开 LIFF URL 测试登录功能

---

## 📞 技术支持

如有配置问题，请联系：
- **技术支持**: support@vhuongtra.com
- **文档**: 查看 `/openspec` 目录
- **部署指南**: 参考 `DEPLOYMENT.md`

---

## 📝 更新日志

| 日期 | 版本 | 更新内容 |
|------|------|----------|
| 2025-01-10 | 1.0.0 | 初始版本 - LINE 配置说明文档 |

---

<div align="center">

**LINE Mini App 配置指南**

*Vhuong Tra Parcel Integration - Thailand Market*

</div>
