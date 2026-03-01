# 前端JavaScript错误修复

## 问题描述

用户上传Excel文件后，前端出现JavaScript错误：
```
Uncaught TypeError: Cannot read properties of undefined (reading 'total_rows')
at renderPreview (index.php?s=/store/payment.import/index:482:43)
```

## 根本原因

前端和后端的数据流程不匹配：

1. **后端upload接口返回的数据结构**：
```php
[
    'file_path' => $filePath,
    'file_name' => $fileName,
    'parsed_data' => [
        'sheets' => [...],
        'total_rows' => N
    ]
]
```

2. **前端期望的数据结构**：
前端直接尝试渲染`parsed_data`，但这只包含原始解析数据，不包含匹配结果和统计信息。

3. **正确的流程应该是**：
   - upload接口：解析Excel文件，返回原始数据
   - preview接口：匹配订单，生成完整的预览数据（包含statistics、sheets、rows_by_color）
   - 前端：调用preview接口后再渲染

## 修复方案

### 1. 修改前端工作流程

将原来的单步流程改为两步：

**修改前**：
```javascript
// upload成功后直接渲染
success: function(res) {
    var parsedData = res.data.parsed_data;
    renderPreview(parsedData);  // 错误：parsedData没有statistics等字段
}
```

**修改后**：
```javascript
// upload成功后调用preview接口
success: function(res) {
    var parsedData = res.data.parsed_data;
    generatePreview(parsedData, res.data.file_path);
}

// 新增generatePreview函数
function generatePreview(parsedData, filePathValue) {
    $.ajax({
        url: '<?= url("payment.import/preview") ?>',
        type: 'POST',
        data: {
            parsed_data: JSON.stringify(parsedData)
        },
        success: function(res) {
            previewData = res.data.preview_data;
            filePath = filePathValue;
            renderPreview(previewData);  // 正确：previewData包含完整结构
        }
    });
}
```

### 2. 修复用户修正数据结构

后端期望的字段名与前端使用的不一致：

**修改前**：
```javascript
var userCorrections = {
    unknown_colors: {},
    multiple_matches: {}
};
```

**修改后**：
```javascript
var userCorrections = {
    color_corrections: {},
    order_selections: {}
};
```

### 3. 添加filePath全局变量

为了在confirm和cancel时能够传递文件路径：

```javascript
var previewData = null;
var filePath = null;  // 新增
var userCorrections = {...};
```

### 4. 修复后端Sheet统计

前端期望`white_count`字段，但后端没有提供：

**修改前**：
```php
$sheetStat = [
    'name' => $sheetName,
    'total_rows' => 0,
    'blue_count' => 0,
    'pink_count' => 0,
    'green_count' => 0,
    'unknown_count' => 0
];
```

**修改后**：
```php
$sheetStat = [
    'name' => $sheetName,
    'total_rows' => 0,
    'blue_count' => 0,
    'pink_count' => 0,
    'green_count' => 0,
    'white_count' => 0,  // 新增
    'unknown_count' => 0
];
```

## 修改的文件

1. `source/application/store/view/payment/import/index.php`
   - 修改upload成功回调，调用preview接口
   - 新增generatePreview函数
   - 修改userCorrections数据结构
   - 添加filePath全局变量
   - 修改confirm和cancel函数传递file_path参数

2. `source/application/store/service/payment/PaymentImportService.php`
   - 在generatePreview方法中添加white_count字段

## 测试验证

修复后的完整流程：

1. 用户选择Excel文件并点击"开始解析"
2. 前端调用upload接口上传文件
3. 后端解析Excel，返回原始数据（parsed_data）
4. 前端自动调用preview接口
5. 后端匹配订单，生成完整预览数据
6. 前端渲染预览界面（统计信息、Sheet统计、订单列表等）
7. 用户修正未知颜色和多重匹配
8. 用户点击"确认导入"
9. 前端调用confirm接口，传递preview_data、user_corrections、file_path
10. 后端执行导入，更新数据库
11. 前端显示导入报告

## 预期结果

- 不再出现JavaScript错误
- 预览界面正常显示统计信息和订单列表
- 用户可以正常完成导入流程
