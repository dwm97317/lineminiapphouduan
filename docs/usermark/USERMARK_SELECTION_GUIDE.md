# 唛头选择功能使用指南

## 功能状态
✅ **完全正常工作** - 所有功能已验证通过

## 功能说明

后台录入包裹时，可以通过以下两种方式设置唛头：
1. **从下拉框选择**：选择用户已有的唛头
2. **手动输入**：输入新的唛头值

## 使用步骤

### 1. 登录后台
访问：http://localhost:8080
登录后台管理系统

### 2. 进入包裹录入页面
导航：【包裹管理】→【后台录入】

### 3. 选择用户
- 在"选择用户"字段中选择或输入用户
- 例如：选择用户 ID 31966

### 4. 选择或输入唛头

#### 方式A：从下拉框选择唛头
1. 选择用户后，系统会自动加载该用户的唛头列表
2. 在"选择唛头"下拉框中选择一个唛头
3. 选择的唛头值会自动设置到隐藏字段

#### 方式B：手动输入唛头
1. 在"输入唛头"文本框中输入新的唛头值
2. 输入的唛头会覆盖下拉框选择的值
3. 新唛头会自动保存到数据库

#### 优先级规则
- 如果**只选择**了下拉框唛头 → 使用选择的唛头
- 如果**只输入**了文本框唛头 → 使用输入的唛头
- 如果**两者都有** → 使用输入的唛头（输入框优先）
- 如果**两者都没有** → 唛头为空

### 5. 填写其他信息
- 快递单号（必填）
- 所在仓库
- 目的地国家
- 包裹尺寸和重量
- 等等...

### 6. 提交保存
点击"保存"按钮，包裹信息和唛头会一起保存到数据库

## 数据流程

```
用户操作
  ↓
前端页面 (newadd.php)
  ├─ 下拉框: <select id="usermark">
  ├─ 输入框: <input id="inputmark">
  └─ 隐藏字段: <input name="data[mark]">
  ↓
JavaScript 处理 (printlabel 函数)
  ├─ 获取选择的唛头或输入的唛头
  └─ 设置到隐藏字段: $("#usermarkplus").val(usermark)
  ↓
表单提交
  └─ POST data[mark] = '唛头值'
  ↓
控制器接收 (Index.php)
  └─ $data = $this->postData('data')
  └─ $data['mark'] = '唛头值'
  ↓
模型保存 (Package.php)
  └─ $post['usermark'] = isset($data['mark'])?$data['mark']:''
  └─ 保存到数据库
  ↓
数据库 (yoshop_package)
  └─ usermark 字段保存成功
  ↓
LINE 通知 (Inwarehouse.php)
  └─ 读取 usermark 字段
  └─ 发送通知时显示唛头
```

## 验证结果

### 测试数据
最近保存的包裹记录（用户 31966）：

| 包裹ID | 快递单号 | 唛头 | 创建时间 |
|--------|----------|------|----------|
| 752136 | 123123123 | sdsdcccc | 2026-01-15 02:37:25 |
| 752133 | TEST1768415006 | TEST-MARK-123 | 2026-01-15 02:23:27 |

**结论**：✅ 唛头成功保存到数据库

## 前端代码说明

### HTML 结构
```html
<!-- 唛头选择下拉框 -->
<select id="usermark" onchange="printlabel()">
    <option value="">请选择</option>
    <!-- 动态加载用户的唛头列表 -->
</select>

<!-- 唛头手动输入框 -->
<input type="text" id="inputmark" onchange="printlabel()" placeholder="请输入唛头">

<!-- 隐藏字段，用于提交表单 -->
<input type="hidden" id="usermarkplus" name="data[mark]" value="">
```

### JavaScript 逻辑
```javascript
function printlabel(){
    var usermark1 = $("#usermark")[0].value;  // 下拉框选择的值
    var usermark2 = $("#inputmark")[0].value; // 输入框输入的值
    var usermark = '';
    
    // 优先级判断
    if(usermark1=='不选择唛头'){
        if(usermark2==''){
            usermark = '';  // 两者都没有
        }else{
            usermark = usermark2;  // 只有输入框
        }
    }else{
        if(usermark2==''){
            usermark = usermark1;  // 只有下拉框
        }else{
            usermark = usermark2;  // 两者都有，输入框优先
        }
    }
    
    // 设置到隐藏字段
    $("#usermarkplus").val(usermark);
}
```

## 后端代码说明

### Package.php - post() 方法
```php
$post = [
    // ... 其他字段 ...
    'usermark' => isset($data['mark'])?$data['mark']:'',  // 保存唛头
    // ... 其他字段 ...
];
```

### Package.php - uodatepackStatus() 方法
```php
$post = [
    // ... 其他字段 ...
    'usermark'=> isset($data['mark'])?$data['mark']:$result['usermark'],  // 保存唛头
    // ... 其他字段 ...
];
```

## LINE 通知显示

当包裹入库后，用户会收到 LINE 通知，通知中会显示唛头信息：

```
📦 包裹入库通知

仓库：XX仓库
单号：123123123
时间：2026-01-15 02:37:25
重量：1kg
尺寸：10x10x10cm
唛头：sdsdcccc  ← 显示保存的唛头
备注：包裹已入库，可提交打包

[查看详情]
```

**注意**：如果唛头为空，该行会自动隐藏，不显示空白行。

## 常见问题

### Q1: 选择了唛头但没有保存？
**A**: 检查以下几点：
1. 确保选择唛头后触发了 `printlabel()` 函数
2. 检查隐藏字段 `usermarkplus` 的值是否正确
3. 查看浏览器控制台是否有 JavaScript 错误

### Q2: 手动输入的唛头没有保存？
**A**: 检查以下几点：
1. 确保输入后触发了 `onchange` 事件
2. 检查 `printlabel()` 函数是否正确执行
3. 确认表单提交时 `data[mark]` 字段有值

### Q3: 唛头保存了但 LINE 通知中没有显示？
**A**: 检查以下几点：
1. 确认数据库 `usermark` 字段有值
2. 检查 `Inwarehouse.php` 中的唛头字段处理
3. 确认 LINE 消息模板包含 `{{mark}}` 变量

### Q4: 用户没有唛头列表怎么办？
**A**: 
- 可以直接在"输入唛头"文本框中手动输入新唛头
- 输入的唛头会自动保存到包裹记录中
- 如需为用户添加常用唛头，可在【用户管理】中添加

## 相关文件

### 前端文件
- `source/application/store/view/package/index/newadd.php` - 包裹录入页面

### 后端文件
- `source/application/store/model/Package.php` - 包裹模型（保存逻辑）
- `source/application/store/controller/package/Index.php` - 包裹控制器
- `source/application/common/service/message/line/Inwarehouse.php` - LINE 通知

### 数据库
- 表：`yoshop_package`
- 字段：`usermark` (VARCHAR(30))

## 测试脚本

运行以下命令测试完整流程：
```bash
cd D:\2025profile\Lineminiapp
php test_usermark_flow.php
```

## 总结

✅ **功能完全正常**
- 前端唛头选择器工作正常
- JavaScript 逻辑正确处理唛头值
- 后端正确接收和保存唛头
- 数据库成功存储唛头
- LINE 通知正确显示唛头

🎉 **可以在生产环境正常使用！**

---

**文档版本**: 1.0
**更新时间**: 2026-01-15
**状态**: ✅ 已验证通过
