# 转单功能调试说明

## 已添加调试代码

我已经在以下文件中添加了详细的调试日志：

1. **TrOrder::deliverySave()** - 记录表单提交的原始数据
2. **Inpack::modify()** - 记录数据处理的每个步骤

## 测试步骤

### 1. 清空旧日志（如果存在）
```bash
del D:\2025profile\Lineminiapp\debug_transfer_log.txt
```

### 2. 执行转单操作
1. 访问订单列表: `http://你的域名/store/tr_order/all_list`
2. 找到一个已发货的订单
3. 点击"转单"按钮
4. 选择承运商（外部或自有物流）
5. 输入新的国际单号
6. 点击"提交"

### 3. 查看调试日志
```bash
type D:\2025profile\Lineminiapp\debug_transfer_log.txt
```

或者在文件管理器中打开：
```
D:\2025profile\Lineminiapp\debug_transfer_log.txt
```

## 日志内容说明

日志会记录以下信息：

### 第一部分：表单提交数据
```
=== 转单请求 2026-01-15 XX:XX:XX ===
提交的数据:
Array
(
    [id] => 订单ID
    [type] => change
    [transfer] => 1 或 0
    [tt_number] => 承运商代码（如果选择外部承运商）
    [t_number] => 物流ID（如果选择自有物流）
    [t_order_sn] => 新的国际单号
)
```

### 第二部分：数据接收和过滤
```
=== Inpack::modify() 接收数据 ===
（显示接收到的完整数据）

过滤后的 $update 数组:
（显示经过字段白名单过滤后的数据）
```

### 第三部分：转单逻辑执行
```
=== 进入转单模式 ===
data['type'] = change
data['transfer'] = 1
data['tt_number'] = dhl
data['t_order_sn'] = TEST123456789

查询外部承运商: dhl
找到承运商: DHL (dhl)

最终更新数组 $upd:
Array
(
    [t2_number] => dhl
    [t2_name] => DHL
    [t2_order_sn] => TEST123456789
    [updated_time] => 时间戳
    [status] => 6
)
```

### 第四部分：数据库更新
```
=== 执行数据库更新 ===
inpack_id = 订单ID
更新数据:
（显示最终写入数据库的数据）
```

## 问题诊断

根据日志内容，可以判断问题出在哪里：

### 情况1：字段丢失
如果"过滤后的 $update 数组"中没有 `tt_number` 和 `transfer`：
- **问题**: 字段白名单修改未生效
- **解决**: 确认 `Inpack.php` 第377行的修改已保存

### 情况2：未进入转单模式
如果日志中没有"进入转单模式"：
- **问题**: `type` 字段未传递或值不正确
- **解决**: 检查表单是否正确设置 `delivery[type]=change`

### 情况3：承运商查询失败
如果显示"未找到承运商"：
- **问题**: 承运商代码不存在或数据库查询失败
- **解决**: 检查 `yoshop_express` 表或 `yoshop_ditch` 表

### 情况4：更新数据为空
如果"最终更新数组"中 `t2_name` 为空：
- **问题**: 承运商查询返回空结果
- **解决**: 检查承运商代码是否正确

## 完成测试后

### 移除调试代码
测试完成后，可以移除调试代码以提高性能：

1. 在 `TrOrder.php` 的 `deliverySave()` 方法中删除调试代码
2. 在 `Inpack.php` 的 `modify()` 方法中删除所有 `file_put_contents` 调用

### 或者保留调试功能
如果想保留调试功能，可以添加开关：

```php
// 在配置文件中添加
'debug_transfer' => true,

// 在代码中使用
if (config('debug_transfer')) {
    file_put_contents($log_file, ...);
}
```

## 下一步

1. 执行上述测试步骤
2. 将生成的 `debug_transfer_log.txt` 文件内容发给我
3. 我会根据日志内容分析具体问题
