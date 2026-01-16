# 客户联系配置 - 语法错误修复

## 问题描述

**错误信息**:
```
PHP Fatal error: Cannot redeclare app\api\controller\Page::CustomerContact() 
in source/application/api/controller/Page.php on line 1050
```

## 问题原因

在 Windows 系统和某些 PHP 配置下，**PHP 方法名是不区分大小写的**。

原始代码：
```php
// 主方法
public function customerContact() {
    // ...
}

// 别名方法
public function CustomerContact() {  // ❌ 错误：PHP 认为这是重复定义
    return $this->customerContact();
}
```

PHP 将 `customerContact()` 和 `CustomerContact()` 视为同一个方法，导致"重复定义"错误。

## 解决方案

采用下划线命名作为主方法，驼峰命名作为别名：

```php
// 主方法 - 使用下划线命名
public function customer_contact() {
    $wxapp_id = $this->request->param('wxapp_id', 10001);
    $config = SettingModel::getItem('customer_contact', $wxapp_id);
    
    if (empty($config)) {
        $config = [
            'hotline_th' => '',
            'line_support' => '',
            'wechat' => ''
        ];
    }
    
    return $this->renderSuccess($config);
}

// 别名方法 - 驼峰命名（ThinkPHP 路由兼容）
public function customerContact() {  // ✅ 正确：不同的方法名
    return $this->customer_contact();
}
```

## 修复文件

**文件**: `Lineminiapp/source/application/api/controller/Page.php`

**修改内容**:
- 主方法改名为 `customer_contact()`（下划线命名）
- 别名方法保持 `customerContact()`（驼峰命名）

## 验证

```bash
cd Lineminiapp
php -l source/application/api/controller/Page.php
# 输出: No syntax errors detected
```

## API 访问方式

两种 URL 格式都支持：

1. **下划线格式**（推荐）:
   ```
   GET /api/page/customer_contact?wxapp_id=10001
   ```

2. **驼峰格式**（ThinkPHP 自动转换）:
   ```
   GET /api/page/customerContact?wxapp_id=10001
   ```

## 类似案例

项目中其他方法也采用相同模式：

```php
// goods_line 和 goodsLine
public function goods_line() { /* ... */ }
public function goodsLine() { return $this->goods_line(); }
```

## 注意事项

1. **PHP 方法名不区分大小写**：在 Windows 和某些配置下，`myMethod()` 和 `MyMethod()` 被视为同一方法
2. **ThinkPHP 路由转换**：URL 中的下划线会自动转换为驼峰命名
3. **最佳实践**：主方法使用下划线，别名使用驼峰，确保兼容性

## 修复时间

2026-01-16

## 状态

✅ 已修复并验证
