# LINE 消息图片发送功能实现说明

## 功能概述

为 LINE 消息通知系统添加了图片发送功能，允许在发送消息时附带相关图片（如包裹入库照片、发货标签等）。

## 实现内容

### 1. 核心功能增强

#### LineMessage 类 (`source/application/common/library/line/LineMessage.php`)
- ✅ 添加 `sendImageMessage()` 方法：发送单张图片
- ✅ 添加 `sendMultipleMessages()` 方法：发送多条消息（Flex Message + 图片）

#### Basics 基类 (`source/application/common/service/message/line/Basics.php`)
- ✅ 增强 `sendLineFlexMsg()` 方法：支持在 Flex Message 后附加图片消息
- ✅ 添加 `getMessageImages()` 方法：从数据中提取图片URL
- ✅ 添加 `ensureHttpsUrl()` 方法：确保图片URL为HTTPS（LINE API要求）

#### Inwarehouse 消息类 (`source/application/common/service/message/line/Inwarehouse.php`)
- ✅ 更新数据传递：支持传递 `images` 或 `packageimage` 字段

### 2. 配置界面增强

在 `source/application/store/view/setting/line_config/index.php` 中为每个消息模板添加：

#### 新增配置项：
1. **发送关联图片** (`send_images`)
   - 类型：复选框
   - 说明：启用后将在消息后附带发送关联的包裹图片

2. **最大图片数量** (`max_images`)
   - 类型：下拉选择（1-4张）
   - 默认值：3张
   - 说明：LINE API限制每次最多5条消息（1条Flex + 4张图片）

### 3. 图片数据格式支持

系统支持多种图片数据格式：

```php
// 格式1: 直接的图片URL数组
$data['images'] = [
    'https://example.com/image1.jpg',
    'https://example.com/image2.jpg'
];

// 格式2: PackageImage 模型数组
$data['packageimage'] = [
    ['file' => ['file_path' => 'https://example.com/image1.jpg']],
    ['file' => ['file_path' => 'https://example.com/image2.jpg']]
];

// 格式3: 单个图片URL
$data['image_url'] = 'https://example.com/image.jpg';
```

## 使用方法

### 1. 在后台配置

1. 访问：`http://localhost:8080/index.php?s=/store/setting.line_config/index`
2. 切换到"消息通知 (Messaging API)"标签
3. 找到需要发送图片的消息模板（如"📦 包裹入库通知"）
4. 勾选"启用图片发送"
5. 选择"最大图片数量"（1-4张）
6. 点击"提交保存"

### 2. 在代码中使用

#### 发送入库通知时附带图片：

```php
use app\common\service\message\line\Inwarehouse;

$messageService = new Inwarehouse();
$messageService->send([
    'wxapp_id' => 10001,
    'member_id' => 123,
    'shop_name' => '泰国仓库',
    'express_num' => 'SF1234567890',
    'entering_warehouse_time' => date('Y-m-d H:i:s'),
    'weight' => 1.5,
    'remark' => '包裹已入库',
    'id' => 999,
    // 添加图片数据
    'images' => [
        'https://example.com/package/photo1.jpg',
        'https://example.com/package/photo2.jpg'
    ]
]);
```

#### 使用 PackageImage 模型数据：

```php
$package = PackageModel::with(['packageimage.file'])->find($packageId);

$messageService->send([
    // ... 其他数据
    'packageimage' => $package->packageimage->toArray()
]);
```

## 测试功能

### 测试消息发送

1. 在配置页面找到消息模板
2. 点击"发送测试消息"按钮
3. 输入测试用户的 LINE User ID（如：`Ud4e37d68c438cc70350957039add98d8`）
4. 系统会发送包含测试图片的消息

测试数据已包含示例图片：
- 📦 包裹入库通知：2张测试图片
- 🚚 发货通知：1张测试图片
- 📋 打包完成通知：3张测试图片
- 🏪 到仓通知：1张测试图片

## 技术细节

### LINE API 限制

1. **消息数量限制**：每次推送最多5条消息
   - 1条 Flex Message + 最多4张图片
   
2. **图片URL要求**：
   - 必须是 HTTPS 协议
   - 图片必须可公开访问
   - 建议图片大小：宽度800-1024px

3. **图片格式**：支持 JPEG、PNG

### URL处理

系统自动处理URL：
- 相对路径自动添加域名
- HTTP自动转换为HTTPS
- 确保符合LINE API要求

### 错误处理

- 图片URL无效时自动跳过
- 图片数量超限时自动截取
- 发送失败时记录详细日志

## 配置示例

### 包裹入库通知配置

```
✅ 启用
📦 包裹入库通知
替代文本: 包裹入库通知
消息标题: 📦 包裹入库通知
优先级: 高
发送延迟: 0秒
主题颜色: #1DB446
按钮文本: 查看详情
✅ 启用图片发送
最大图片数量: 3张
```

### 发货通知配置

```
✅ 启用
🚚 发货通知
替代文本: 发货通知
消息标题: 🚚 发货通知
优先级: 高
发送延迟: 0秒
主题颜色: #0066CC
按钮文本: 追踪物流
✅ 启用图片发送
最大图片数量: 2张
```

## 日志记录

所有图片发送操作都会记录到系统日志：

```php
[
    'describe' => 'LINE消息发送',
    'wxapp_id' => 10001,
    'line_user_id' => 'Ud4e37d68...',
    'message_type' => 'inwarehouse',
    'result' => 'success',
    'image_count' => 2,
    'time' => '2024-01-15 10:30:00'
]
```

## 注意事项

1. **图片URL必须是HTTPS**：LINE API强制要求
2. **图片必须可公开访问**：LINE服务器需要能够访问图片
3. **控制图片数量**：避免发送过多消息导致用户体验不佳
4. **图片大小优化**：建议压缩图片以提高加载速度
5. **测试环境**：确保测试环境的图片URL可被LINE服务器访问

## 后续优化建议

1. **图片压缩**：自动压缩大图片
2. **CDN加速**：使用CDN提高图片加载速度
3. **图片选择**：允许管理员选择发送哪些图片
4. **图片顺序**：支持自定义图片发送顺序
5. **图片水印**：自动添加品牌水印

## 相关文件

- `source/application/common/library/line/LineMessage.php` - LINE API客户端
- `source/application/common/service/message/line/Basics.php` - 消息服务基类
- `source/application/common/service/message/line/Inwarehouse.php` - 入库消息
- `source/application/store/view/setting/line_config/index.php` - 配置界面
- `source/application/store/controller/setting/LineConfig.php` - 配置控制器

## 更新日期

2024-01-15
