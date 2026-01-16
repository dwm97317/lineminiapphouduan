# 唛头 JavaScript 赋值修复 / Usermark JavaScript Assignment Fix

## 修复日期 / Fix Date
2026-01-15

## 问题描述 / Problem Description

用户报告：使用唛头选择器的时候，没有赋值到 mark 字段。

## 根本原因 / Root Cause

### JavaScript 函数缺少关键代码

**文件**: `Lineminiapp/source/application/store/view/package/index/add.php`

**函数**: `printlabel()`

**问题**: 函数计算了 `usermark` 的值，但**没有将它设置到 hidden field**！

### 代码对比

#### ❌ 修复前 (add.php)
```javascript
function printlabel(){
    var usermark1 = $("#usermark")[0].value;  // 下拉选择
    var usermark2 = $("#inputmark")[0].value; // 文本输入
    var usermark = '';
    
    // ... 逻辑判断，计算 usermark 的值 ...
    
    // ⚠️ 缺少这一行！没有设置到 hidden field
    
    if(expremm){
        drawTextTest(expremm,usermark,today);
    }
}
```

#### ✅ 正确实现 (newadd.php)
```javascript
function printlabel(){
    var usermark1 = $("#usermark")[0].value;
    var usermark2 = $("#inputmark")[0].value;
    var usermark = '';
    
    // ... 逻辑判断，计算 usermark 的值 ...
    
    $("#usermarkplus").val(usermark);  // ✅ 关键代码！
    
    if(expremm){
        drawTextTest(expremm,usermark,today);
    }
}
```

## 完整的修复链 / Complete Fix Chain

为了让唛头保存功能正常工作，需要**三个修复**：

### 1. ✅ 前端 HTML 修复
**问题**: 两个字段都有 `name="data[mark]"`，导致重复参数

**修复**:
```html
<!-- 移除 select 的 name -->
<select id="usermark">

<!-- 移除 input 的 name -->
<input id="inputmark">

<!-- 添加 hidden field，只有它有 name -->
<input type="hidden" id="usermarkplus" name="data[mark]">
```

### 2. ✅ JavaScript 修复 (本次)
**问题**: `printlabel()` 函数没有将值设置到 hidden field

**修复**:
```javascript
function printlabel(){
    // ... 计算 usermark 值 ...
    
    // 添加这一行！
    $("#usermarkplus").val(usermark);
    
    // ... 其他代码 ...
}
```

### 3. ✅ 后端 PHP 修复
**问题**: `$result` 为 null 时，`$result['usermark']` 会出错

**修复**:
```php
'usermark'=> isset($data['mark']) && !empty($data['mark']) 
    ? $data['mark'] 
    : ($result['usermark'] ?? ''),
```

## 数据流程 / Data Flow

### 完整的数据流程
```
1. 用户选择唛头下拉框 → usermark1 = 'mark2'
   ↓
2. 或用户输入唛头文本框 → usermark2 = 'custom'
   ↓
3. printlabel() 函数触发 (onchange)
   ↓
4. 计算最终值: usermark = usermark1 || usermark2
   ↓
5. 设置到 hidden field: $("#usermarkplus").val(usermark)  ← 本次修复
   ↓
6. 表单提交: data[mark]=mark2 (只有一个参数)
   ↓
7. 后端接收: $_POST['data']['mark'] = 'mark2'
   ↓
8. 保存到数据库: yoshop_package.usermark = 'mark2'
```

## 修复实施 / Implementation

### 修改的代码

**文件**: `Lineminiapp/source/application/store/view/package/index/add.php`

**位置**: `printlabel()` 函数内

**添加的代码**:
```javascript
// 将选择的唛头值设置到 hidden field
$("#usermarkplus").val(usermark);
```

**完整的修复后函数**:
```javascript
function printlabel(){
    var expremm = $("#express_num")[0].value;
    var usermark1 = $("#usermark")[0].value;
    var usermark2 = $("#inputmark")[0].value;
    var usermark = '';
    
    console.log(usermark1,86);
    console.log(usermark2,86);
    
    if(usermark1=='不选择唛头'){
        if(usermark2==''){
            return;
        }else{
            usermark = usermark2;
        }
    }else{
        if(usermark2==''){
            usermark = usermark1;
        }else{
            usermark = usermark2;
        }
    }
    
    var today = getNowFormatDate();
    
    // ✅ 关键修复：将选择的唛头值设置到 hidden field
    $("#usermarkplus").val(usermark);
    
    if(expremm){
        drawTextTest(expremm,usermark,today);
    }
}
```

## 测试步骤 / Testing Steps

### 1. 清除浏览器缓存
按 `Ctrl + Shift + Delete` 或 `Ctrl + F5` 强制刷新

### 2. 访问后台录入页面
```
http://localhost:8080/store/package.index/add
```

### 3. 打开浏览器开发者工具
按 `F12` → Console 标签

### 4. 填写表单
- 快递单号: `TEST1768416600`
- 选择用户: ID `31966`

### 5. 测试唛头选择
在唛头下拉框选择 `mark2`

### 6. 在 Console 中验证
输入以下命令查看 hidden field 的值：
```javascript
$("#usermarkplus").val()
```

**预期输出**: `"mark2"`

### 7. 提交表单
点击"确认入库"

### 8. 检查 Network 标签
查看 POST 数据：
```
data[mark]=mark2  ← 应该只有一个，且有值
```

### 9. 验证数据库
```sql
SELECT id, express_num, usermark, member_id 
FROM yoshop_package 
WHERE express_num = 'TEST1768416600';
```

**预期结果**: `usermark = 'mark2'`

## 调试技巧 / Debugging Tips

### 在浏览器 Console 中测试

```javascript
// 1. 检查 select 的值
$("#usermark").val()

// 2. 检查 input 的值
$("#inputmark").val()

// 3. 检查 hidden field 的值
$("#usermarkplus").val()

// 4. 手动设置值测试
$("#usermarkplus").val("test123")

// 5. 验证设置成功
$("#usermarkplus").val()  // 应该返回 "test123"
```

### 添加调试日志

在 `printlabel()` 函数中添加：
```javascript
function printlabel(){
    // ... 现有代码 ...
    
    console.log("usermark1:", usermark1);
    console.log("usermark2:", usermark2);
    console.log("final usermark:", usermark);
    
    $("#usermarkplus").val(usermark);
    
    console.log("hidden field value:", $("#usermarkplus").val());
    
    // ... 其他代码 ...
}
```

## 相关文件 / Related Files

### 修复的文件
1. ✅ `source/application/store/view/package/index/add.php`
   - HTML: 添加 hidden field
   - JavaScript: 添加 `$("#usermarkplus").val(usermark)`

### 参考文件
2. `source/application/store/view/package/index/newadd.php` - 正确的实现

### 后端文件
3. ✅ `source/application/store/model/Package.php` - 后端处理

### 文档
4. `USERMARK_JAVASCRIPT_FIX.md` - 本文档
5. `USERMARK_DUPLICATE_FIELD_FIX.md` - HTML 修复文档
6. `USERMARK_SAVE_FINAL_FIX.md` - 后端修复文档

## 修复总结 / Summary

### 三个关键修复

| 修复 | 文件 | 问题 | 解决方案 |
|------|------|------|----------|
| 1. HTML | add.php | 重复的 name 属性 | 添加 hidden field |
| 2. JavaScript | add.php | 没有赋值到 hidden field | 添加 `$("#usermarkplus").val(usermark)` |
| 3. PHP | Package.php | null 处理错误 | 使用 `??` 运算符 |

### 修复状态
🟢 **全部完成 / ALL COMPLETED**

- ✅ HTML 修复: 添加 hidden field
- ✅ JavaScript 修复: 添加赋值代码
- ✅ PHP 修复: 使用 null 合并运算符

### 预期结果
现在当用户选择唛头时：
1. JavaScript 会将值设置到 hidden field
2. 表单提交时只有一个 `data[mark]` 参数
3. 后端正确接收并保存到数据库

---

## 联系信息 / Contact
- **测试用户**: ID 31966
- **后台 URL**: http://localhost:8080
- **数据库**: 103.119.1.84 / xinsuju
