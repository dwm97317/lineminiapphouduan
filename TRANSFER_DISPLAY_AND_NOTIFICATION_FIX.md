# 转单信息显示和通知修复

## 修复内容

### 1. 订单列表显示转单信息 ✅

**问题**: 订单列表中只显示转单单号，没有显示转单承运商名称

**修复**: 在订单列表中完整显示转单信息

**文件**: `source/application/store/view/tr_order/index.php`

**修改内容**:
```php
// 原来只显示转单单号
<?php if (!empty($item['t2_order_sn'])): ?> 
转单单号: <?= $item['t2_order_sn'] ?>
<?php endif ;?>

// 修改后显示完整转单信息（红色高亮）
<?php if (!empty($item['t2_order_sn'])): ?> 
<span style="color:#ff6666;">转单承运商: <?= $item['t2_name'] ?></span></br>
<span style="color:#ff6666;">转单单号: <?= $item['t2_order_sn'] ?></span></br>
<?php endif ;?>
```

**显示效果**:
```
转运信息:
  专属客服：张三
  承运商: CJ专线
  国际单号: 665456123
  转单承运商: Flash Express (TH)  ← 红色显示
  转单单号: 654a6sd45612          ← 红色显示
  线路: 泰国专线
  寄件仓库: 广州仓
  取件仓库: 曼谷仓
```

### 2. LINE发货通知使用转单信息 ✅

**问题**: 转单后发送的LINE通知仍然使用原始承运商和单号（`t_name`, `t_order_sn`），而不是转单后的信息（`t2_name`, `t2_order_sn`）

**修复**: 发货通知优先使用转单信息

**文件**: `source/application/common/service/message/line/Sendpack.php`

**修改逻辑**:
```php
// 判断是否有转单信息，优先使用转单信息
$trackingNumber = !empty($orderInfo['t2_order_sn']) 
    ? $orderInfo['t2_order_sn']   // 有转单单号，使用转单单号
    : ($orderInfo['t_order_sn'] ?? '');  // 没有转单，使用原始单号

$carrierName = !empty($orderInfo['t2_name']) 
    ? $orderInfo['t2_name']       // 有转单承运商，使用转单承运商
    : ($orderInfo['t_name'] ?? '');      // 没有转单，使用原始承运商

// 构建模板数据
$data = [
    'order_sn' => $orderInfo['order_sn'] ?? '',
    't_order_sn' => $trackingNumber,  // 使用转单单号或原始单号
    't_name' => $carrierName,         // 使用转单承运商或原始承运商
    // ...
];
```

**通知效果**:

**首次发货通知**:
```
📦 您的包裹已发货
订单号: 2026011543787
承运商: CJ专线
国际单号: 665456123
```

**转单后通知**:
```
📦 您的包裹已转单发货
订单号: 2026011543787
承运商: Flash Express (TH)  ← 使用转单承运商
国际单号: 654a6sd45612      ← 使用转单单号
```

## 数据库字段说明

| 字段 | 说明 | 使用场景 |
|------|------|----------|
| `t_number` | 首次发货承运商代码 | 首次发货 |
| `t_name` | 首次发货承运商名称 | 首次发货 |
| `t_order_sn` | 首次发货国际单号 | 首次发货 |
| `t2_number` | 转单后承运商代码 | 转单后 |
| `t2_name` | 转单后承运商名称 | 转单后 |
| `t2_order_sn` | 转单后国际单号 | 转单后 |
| `transfer` | 运输方式 (1=外部, 0=自有) | 转单标识 |

## 逻辑说明

### 订单列表显示逻辑
```
IF t2_order_sn 不为空 THEN
    显示首次发货信息 (t_name, t_order_sn)
    显示转单信息 (t2_name, t2_order_sn) - 红色高亮
ELSE
    只显示首次发货信息 (t_name, t_order_sn)
END IF
```

### LINE通知逻辑
```
IF t2_order_sn 不为空 THEN
    使用转单信息 (t2_name, t2_order_sn)
ELSE
    使用首次发货信息 (t_name, t_order_sn)
END IF
```

## 测试验证

### 1. 测试订单列表显示

**步骤**:
1. 访问订单列表: `/store/tr_order/all_list`
2. 找到已转单的订单 (ID: 69407)
3. 查看"转运信息"列

**预期结果**:
- 显示首次发货承运商: CJ专线
- 显示首次发货单号: 665456123
- 显示转单承运商（红色）: Flash Express (TH)
- 显示转单单号（红色）: 654a6sd45612

### 2. 测试LINE发货通知

**步骤**:
1. 对一个订单执行转单操作
2. 检查用户收到的LINE通知

**预期结果**:
- 通知中显示的承运商是转单后的承运商
- 通知中显示的单号是转单后的单号

### 3. 测试未转单订单

**步骤**:
1. 查看未转单的订单列表
2. 查看未转单订单的LINE通知

**预期结果**:
- 订单列表只显示首次发货信息
- LINE通知使用首次发货信息

## 相关文件

| 文件 | 修改内容 |
|------|----------|
| `source/application/store/view/tr_order/index.php` | 订单列表显示转单承运商 |
| `source/application/common/service/message/line/Sendpack.php` | LINE通知优先使用转单信息 |

## 注意事项

1. **向后兼容**: 修改保持向后兼容，未转单的订单不受影响
2. **红色高亮**: 转单信息使用红色显示，便于识别
3. **优先级**: LINE通知优先使用转单信息，如果没有转单则使用原始信息
4. **数据完整性**: 确保 `t2_name` 和 `t2_order_sn` 同时存在或同时为空

## 修复日期
2026-01-15

## 修复状态
✅ 已完成
