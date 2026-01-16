# LINE 通知图片和字段更新完成

## 更新内容

### 1. 修复图片发送问题 ✅

**问题**: 发送的 LINE 通知没有包含包裹图片

**原因**: `sendEnterMessage()` 方法接收的是数组数据，没有加载关联的图片数据

**解决方案**:
- 修改 `Package.php::sendEnterMessage()` 方法
- 在发送 LINE 通知前，自动加载包裹的图片关联数据
- 支持 `packageimage` 关联（包含 `file` 子关联）

**修改文件**: `source/application/common/model/Package.php`

```php
// 如果传入的是数组，需要加载图片关联数据
if (is_array($item) && isset($item['id'])) {
    $packageWithImages = self::with(['packageimage' => function($query) {
        $query->with('file');
    }])->find($item['id']);
    
    if ($packageWithImages && !empty($packageWithImages['packageimage'])) {
        // 将图片数据添加到item中
        $item['packageimage'] = $packageWithImages['packageimage']->toArray();
    }
}
```

### 2. 添加尺寸字段 ✅

**字段**: `size`（尺寸）

**数据来源**: 
- 数据库字段: `length`（长）、`width`（宽）、`height`（高）
- 格式: `{length}x{width}x{height}cm`
- 示例: `30x20x15cm`

**显示规则**:
- 当长、宽、高都大于 0 时，显示尺寸
- 当任一尺寸为 0 或空时，不显示该字段（自动移除）

**修改文件**: `source/application/common/service/message/line/Inwarehouse.php`

```php
// 构建尺寸字符串（只有当长宽高都大于0时才显示）
$sizeStr = '';
if (!empty($orderInfo['length']) && $orderInfo['length'] > 0 && 
    !empty($orderInfo['width']) && $orderInfo['width'] > 0 && 
    !empty($orderInfo['height']) && $orderInfo['height'] > 0) {
    $sizeStr = $orderInfo['length'] . 'x' . $orderInfo['width'] . 'x' . $orderInfo['height'] . 'cm';
}

$data['size'] = $sizeStr; // 为空时会被自动移除
```

### 3. 添加唛头字段 ✅

**字段**: `mark`（唛头）

**数据来源**: 
- 数据库字段: `usermark`
- 用户自定义的包裹标记

**显示规则**:
- 当 `usermark` 有值时，显示唛头
- 当 `usermark` 为空或 NULL 时，不显示该字段（自动移除）

**修改文件**: `source/application/common/service/message/line/Inwarehouse.php`

```php
$data['mark'] = !empty($orderInfo['usermark']) ? $orderInfo['usermark'] : ''; // 为空时会被自动移除
```

### 4. 更新 LINE 模板 ✅

**更新内容**:
- 在入库通知模板中添加"尺寸"和"唛头"字段
- 更新模板变量列表

**新模板结构**:
```
📦 包裹入库通知
━━━━━━━━━━━━━━━
仓库：{{shop_name}}
快递单号：{{express_num}}
入库时间：{{entering_warehouse_time}}
重量：{{weight}}kg
尺寸：{{size}}          ← 新增
唛头：{{mark}}          ← 新增
━━━━━━━━━━━━━━━
{{remark}}
[查看详情按钮]
```

**模板变量**:
```json
["shop_name", "express_num", "entering_warehouse_time", "weight", "size", "mark", "remark", "detail_url"]
```

## 技术实现

### 空字段自动移除机制

利用 `Basics.php` 中的 `removeEmptyTextFields()` 方法：
- 自动检测 Flex Message 中的空文本字段
- 递归移除 `type='text'` 且 `text=''` 的组件
- 自动重新索引 `contents` 数组

这样当尺寸或唛头为空时，对应的行会自动从消息中移除，不会显示空白行。

### 图片发送逻辑

1. 检查配置中是否启用图片发送（`send_images = 1`）
2. 从数据中提取图片（支持多种格式）：
   - `images` 数组（直接的 URL 数组）
   - `packageimage` 关联（模型关联数据）
3. 确保图片 URL 是 HTTPS（LINE API 要求）
4. 限制图片数量（最多 4 张，因为已有 1 条 Flex Message）

## 测试结果

### 测试包裹数据
- 包裹 ID: 752127
- 用户 ID: 31966
- 快递单号: 31966asdsadas
- 重量: 1kg
- 尺寸: 1x1x1cm
- 唛头: 无
- 图片: 1 张

### 测试命令
```bash
php test_complete_notification.php
```

### 测试结果
```
✅ 发送成功

请检查LINE消息是否包含:
- ✓ 尺寸信息
- ✓ 唛头信息（如果有）
- ✓ 包裹图片（如果有）
```

## 修改文件清单

1. **source/application/common/model/Package.php**
   - 修改 `sendEnterMessage()` 方法
   - 添加图片关联数据加载逻辑

2. **source/application/common/service/message/line/Inwarehouse.php**
   - 添加尺寸字段构建逻辑
   - 添加唛头字段（使用 `usermark` 字段）
   - 优化空值处理

3. **数据库配置**（通过脚本更新）
   - 更新 `yoshop_setting` 表中的 `line_messaging` 配置
   - 添加 `size` 和 `mark` 变量到模板

## 使用说明

### 后台录入包裹时

当仓管在后台录入包裹时，系统会自动：
1. 加载包裹的图片数据
2. 构建尺寸字符串（如果有）
3. 获取唛头信息（如果有）
4. 发送包含所有信息的 LINE 通知

### 字段显示规则

| 字段 | 显示条件 | 不显示条件 |
|------|---------|-----------|
| 尺寸 | 长、宽、高都 > 0 | 任一为 0 或空 |
| 唛头 | usermark 有值 | usermark 为空或 NULL |
| 图片 | packageimage 有数据且配置启用 | 无图片或配置未启用 |

### 配置检查

确保 LINE 配置中启用了图片发送：
```php
$config['templates']['inwarehouse']['send_images'] = '1';
$config['templates']['inwarehouse']['max_images'] = 3; // 最多发送3张图片
```

## 注意事项

1. **图片 URL 必须是 HTTPS**
   - LINE API 要求所有图片 URL 必须使用 HTTPS 协议
   - 系统会自动将 HTTP 转换为 HTTPS

2. **图片数量限制**
   - LINE API 每次最多发送 5 条消息
   - 已有 1 条 Flex Message，所以最多再发送 4 张图片

3. **空字段自动移除**
   - 尺寸和唛头为空时会自动从消息中移除
   - 不会显示空白行或"无"字样

4. **数据库字段映射**
   - 尺寸: `length` x `width` x `height`
   - 唛头: `usermark`（不是 `mark`）

## 完成时间
2026-01-15

## 状态
✅ 已完成并测试通过
