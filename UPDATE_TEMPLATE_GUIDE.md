# LINE 入库通知模板更新指南

## 问题
代码已经更新支持尺寸和唛头字段，但 LINE 消息中看不到这些信息。

## 原因
数据库中的 Flex Message 模板还是旧版本，没有包含 `{{size}}` 和 `{{mark}}` 变量。

## 解决方案

### 方法1：通过后台界面更新（推荐）

1. 登录后台管理系统
2. 进入：设置 → LINE 消息配置
3. 找到"包裹入库通知"模板
4. 点击"编辑模板"
5. 在模板编辑器中，找到"重量：{{weight}}kg"这一行
6. 在它下面添加两行：
   ```
   尺寸：{{size}}
   唛头：{{mark}}
   ```
7. 在"模板变量"列表中添加：`size`, `mark`
8. 保存配置

### 方法2：使用 Flex Message Simulator（推荐）

1. 访问 LINE Flex Message Simulator: https://developers.line.biz/flex-simulator/
2. 复制以下完整模板：

```json
{
  "type": "bubble",
  "header": {
    "type": "box",
    "layout": "vertical",
    "contents": [
      {
        "type": "text",
        "text": "📦 包裹入库通知",
        "weight": "bold",
        "size": "lg",
        "color": "#1DB446"
      }
    ],
    "backgroundColor": "#F0FFF0"
  },
  "body": {
    "type": "box",
    "layout": "vertical",
    "contents": [
      {
        "type": "text",
        "text": "仓库：{{shop_name}}",
        "size": "sm",
        "wrap": true
      },
      {
        "type": "text",
        "text": "快递单号：{{express_num}}",
        "size": "sm",
        "wrap": true
      },
      {
        "type": "text",
        "text": "入库时间：{{entering_warehouse_time}}",
        "size": "sm",
        "wrap": true
      },
      {
        "type": "text",
        "text": "重量：{{weight}}kg",
        "size": "sm",
        "wrap": true
      },
      {
        "type": "text",
        "text": "尺寸：{{size}}",
        "size": "sm",
        "wrap": true
      },
      {
        "type": "text",
        "text": "唛头：{{mark}}",
        "size": "sm",
        "wrap": true
      },
      {
        "type": "separator",
        "margin": "md"
      },
      {
        "type": "text",
        "text": "{{remark}}",
        "size": "sm",
        "color": "#888888",
        "margin": "md",
        "wrap": true
      }
    ],
    "spacing": "sm"
  },
  "footer": {
    "type": "box",
    "layout": "vertical",
    "contents": [
      {
        "type": "button",
        "action": {
          "type": "uri",
          "label": "查看详情",
          "uri": "{{detail_url}}"
        },
        "style": "primary",
        "color": "#1DB446"
      }
    ]
  }
}
```

3. 在 Simulator 中预览效果
4. 复制 JSON 代码
5. 在后台 LINE 配置中，将此 JSON 粘贴到"Flex Message 模板"字段
6. 更新模板变量列表为：
   ```
   ["shop_name","express_num","entering_warehouse_time","weight","size","mark","remark","detail_url"]
   ```
7. 保存配置

### 方法3：直接修改数据库（不推荐，仅供参考）

如果后台界面无法访问，可以直接修改数据库：

```sql
-- 备份当前配置
SELECT * FROM yoshop_setting WHERE `key` = 'line_messaging' AND wxapp_id = 10001;

-- 注意：需要手动构建完整的 JSON，这里只是示例
-- 实际操作时需要完整的 JSON 字符串
```

## 验证更新

更新模板后，运行测试脚本验证：

```bash
php test_complete_notification.php
```

检查 LINE 消息是否包含：
- ✓ 尺寸信息（如果包裹有尺寸数据）
- ✓ 唛头信息（如果包裹有唛头数据）
- ✓ 包裹图片（如果包裹有图片）

## 注意事项

1. **空字段自动隐藏**
   - 如果尺寸为 0 或空，该行会自动隐藏
   - 如果唛头为空，该行会自动隐藏
   - 这是代码层面的处理，无需担心显示空白行

2. **模板变量必须匹配**
   - 确保模板中使用的变量（如 `{{size}}`）在变量列表中声明
   - 变量列表格式：`["var1","var2","var3"]`

3. **缓存清理**
   - 更新模板后，系统会自动清除缓存
   - 如果仍看到旧模板，可能需要等待几分钟

## 当前代码支持的字段

| 字段 | 变量名 | 数据来源 | 显示条件 |
|------|--------|---------|---------|
| 仓库 | `shop_name` | `shop_name` | 始终显示 |
| 快递单号 | `express_num` | `express_num` | 始终显示 |
| 入库时间 | `entering_warehouse_time` | `entering_warehouse_time` | 始终显示 |
| 重量 | `weight` | `weight` | 始终显示 |
| 尺寸 | `size` | `length x width x height` | 长宽高都>0时显示 |
| 唛头 | `mark` | `usermark` | 有值时显示 |
| 备注 | `remark` | `remark` | 始终显示 |
| 详情链接 | `detail_url` | LIFF URL | 始终显示 |

## 完成时间
2026-01-15
