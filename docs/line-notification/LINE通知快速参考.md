# LINE 通知快速参考

## 🚀 快速验证

```bash
cd Lineminiapp
php verify_line_notification_setup.php
```

---

## 📱 通知类型

| 通知 | 触发条件 | 订单状态 |
|------|----------|----------|
| 📦 入库通知 | 查验完成 | status = 2 |
| 🚚 发货通知 | 订单发货 | status = 6 |

---

## ⚙️ 配置检查

### 必需配置 ✅
- [x] LINE 消息通知已启用
- [x] Channel ID: 2008892817
- [x] Access Token: 已设置
- [x] LIFF URL: 已配置
- [x] 入库模板已启用
- [x] 发货模板已启用

### 用户状态 ✅
- 已绑定用户: 2 人

---

## 🧪 测试步骤

### 测试入库通知
1. 后台 → 集运订单管理
2. 选择订单 → 勾选"查验完成"
3. 保存 → 检查 LINE 通知

### 测试发货通知
1. 后台 → 集运订单管理
2. 选择订单 → 点击"发货"
3. 填写物流 → 保存 → 检查 LINE 通知

---

## 🔍 问题排查

### 未收到通知？

**检查清单**:
- [ ] 用户是否添加 LINE OA 为好友？
- [ ] 后台 LINE 配置是否正确？
- [ ] 消息模板是否已启用？
- [ ] 查看日志: `runtime/log/[年月]/[日期].log`

### 常见错误

| 错误信息 | 解决方法 |
|----------|----------|
| "全局未启用" | 后台启用 LINE 配置 |
| "模板未启用" | 后台启用对应模板 |
| "用户未添加好友" | 提示用户添加 LINE OA |

---

## 📂 关键文件

### 代码
- `source/application/store/model/Inpack.php` (已修改)
- `source/application/common/service/message/line/Inwarehouse.php`
- `source/application/common/service/message/line/Sendpack.php`

### 文档
- `LINE通知集成完成总结.md` - 完整说明
- `LINE_NOTIFICATION_COMPLETE.md` - 技术文档
- `LINE通知快速参考.md` - 本文档

---

## 💡 提示

- ✅ 通知失败不影响订单处理
- ✅ 所有错误都会记录到日志
- ✅ 支持发送包裹图片（入库通知）
- ✅ 自动验证好友关系

---

**状态**: 🎉 已完成
**日期**: 2026-01-15
