# 账单功能组件说明

## 目录结构

```
components/
├── README.md                  # 本说明文档
├── statement_buttons.php      # 账单操作按钮组件
├── statement_filter.php       # 账单状态筛选器组件
├── statement_column.php       # 账单信息列组件
└── statement_script.php       # 账单功能JavaScript组件
```

## 组件说明

### 1. statement_buttons.php
**功能**: 账单操作按钮组

**包含内容**:
- 生成账单按钮（需要权限）
- 账单列表按钮

**使用位置**: 批量操作按钮区域

**引入方式**:
```php
<?php include __DIR__ . '/components/statement_buttons.php'; ?>
```

---

### 2. statement_filter.php
**功能**: 账单状态筛选器

**包含内容**:
- 账单状态下拉选择器
- 三个选项：未出账、已出账未支付、已支付

**使用位置**: 筛选工具栏区域

**引入方式**:
```php
<?php include __DIR__ . '/components/statement_filter.php'; ?>
```

**依赖变量**:
- `$request` - 请求对象，用于获取当前筛选值

---

### 3. statement_column.php
**功能**: 账单信息列

**包含内容**:
- 账单编号（可点击跳转）
- 支付状态徽章
- 未出账状态显示

**使用位置**: 表格数据行中

**引入方式**:
```php
<?php include __DIR__ . '/components/statement_column.php'; ?>
```

**依赖变量**:
- `$item` - 当前订单数据对象
- `$item['statement_id']` - 账单ID
- `$item['statement']['statement_no']` - 账单编号
- `$item['statement']['pay_status']` - 支付状态

---

### 4. statement_script.php
**功能**: 账单功能JavaScript代码

**包含内容**:
- 生成账单按钮点击事件
- 订单选择验证
- 客户一致性验证
- API调用逻辑

**使用位置**: 页面末尾（在主JavaScript代码之后）

**引入方式**:
```php
<?php include __DIR__ . '/components/statement_script.php'; ?>
```

**依赖**:
- jQuery
- layer.js（弹窗插件）
- checker对象（复选框管理器）

---

## 在主文件中的使用

### 完整集成示例

```php
<!-- 1. 在筛选工具栏中添加账单状态筛选 -->
<div class="am-form-group am-fl">
    <select name="source" ...>...</select>
</div>
<?php include __DIR__ . '/components/statement_filter.php'; ?>

<!-- 2. 在批量操作按钮区域添加账单按钮 -->
<button type="button" id="j-batch" ...>加入批次</button>
<?php include __DIR__ . '/components/statement_buttons.php'; ?>

<!-- 3. 在表格表头添加账单信息列 -->
<thead>
    <tr>
        <th>包裹信息</th>
        <th>账单信息</th>
        <th>状态</th>
    </tr>
</thead>

<!-- 4. 在表格数据行添加账单信息列 -->
<tbody>
    <?php foreach ($list as $item): ?>
    <tr>
        <td>包裹信息...</td>
        <?php include __DIR__ . '/components/statement_column.php'; ?>
        <td>状态...</td>
    </tr>
    <?php endforeach; ?>
</tbody>

<!-- 5. 在页面末尾引入JavaScript -->
<script>
    // 主页面JavaScript代码
    ...
</script>
<?php include __DIR__ . '/components/statement_script.php'; ?>
```

---

## 组件优势

1. **代码分离**: 将账单相关代码从主文件中分离，保持主文件简洁
2. **易于维护**: 修改账单功能只需修改组件文件
3. **可复用**: 组件可以在其他类似页面中复用
4. **独立测试**: 可以单独测试每个组件的功能
5. **版本控制**: 便于追踪账单功能的修改历史

---

## 注意事项

1. **路径问题**: 使用 `__DIR__` 确保相对路径正确
2. **变量作用域**: 组件中使用的变量必须在主文件中定义
3. **权限检查**: 按钮组件中包含权限检查，确保权限配置正确
4. **依赖关系**: JavaScript组件依赖jQuery和layer.js，确保已加载

---

## 移除组件

如果需要移除账单功能，只需删除以下引入语句：

```php
// 删除这些行
<?php include __DIR__ . '/components/statement_filter.php'; ?>
<?php include __DIR__ . '/components/statement_buttons.php'; ?>
<?php include __DIR__ . '/components/statement_column.php'; ?>
<?php include __DIR__ . '/components/statement_script.php'; ?>
```

同时删除表头中的"账单信息"列：
```php
<th>账单信息</th>
```

---

## 更新日志

- 2026-02-28: 创建组件，从主文件中分离账单功能代码
