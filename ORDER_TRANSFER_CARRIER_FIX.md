# 订单转单承运商更新修复

## 问题描述
用户在订单列表点击"转单"功能，更换承运商后提交失败，承运商名称无法正确更新到数据库。

## 根本原因

### 问题1: 字段白名单缺失 ⚠️ **主要问题**
在 `Inpack::modify()` 方法的第377行，定义了允许更新的字段白名单：

```php
$field = ['line_id','length','width','height','weight','verify','free','pack_free',
          'cale_weight','volume','other_free','remark','t_number','t_name','t_order_sn'];
```

**问题**: 白名单中缺少转单功能需要的关键字段：
- `tt_number`: 外部承运商代码
- `transfer`: 运输方式标识 (1=外部承运商, 0=自有物流)

**结果**: 表单提交的 `delivery[tt_number]` 和 `delivery[transfer]` 被过滤掉，导致后续逻辑无法获取这些值。

### 问题2: 转单模式下承运商查询逻辑缺失
在 `type='change'` (转单模式) 分支中，原代码直接使用 `$update['t_name']` 和 `$update['t_number']`，但这些值在转单模式下未定义：

**原代码问题 (lines 580-598)**:
```php
if($data['type']=='change'){
    $upd['t2_number'] = $update['t_number'];  // ❌ $update['t_number'] 未定义
    $upd['t2_name'] = $update['t_name'];      // ❌ $update['t_name'] 未定义
    $upd['t2_order_sn'] = $update['t_order_sn'];
    // ...
}
```

**问题分析**:
1. 承运商名称查询逻辑在 lines 530-560，只在 `!isset($data['type'])` 时执行
2. 转单模式 `type='change'` 时，跳过了承运商查询逻辑
3. 导致 `$update['t_name']` 和 `$update['t_number']` 未定义
4. 最终 `t2_name` 和 `t2_number` 字段为空值

## 修复方案

### 修复1: 添加缺失字段到白名单 ✅
**文件**: `Lineminiapp/source/application/store/model/Inpack.php` (line 377)

```php
// 修改前
$field = ['line_id','length','width','height','weight','verify','free','pack_free',
          'cale_weight','volume','other_free','remark','t_number','t_name','t_order_sn'];

// 修改后
$field = ['line_id','length','width','height','weight','verify','free','pack_free',
          'cale_weight','volume','other_free','remark','t_number','t_name','t_order_sn',
          'tt_number','transfer'];  // ✅ 添加转单需要的字段
```

### 修复2: 在转单模式中添加承运商查询逻辑 ✅
**文件**: `Lineminiapp/source/application/store/model/Inpack.php` (lines 580-598)

```php
if($data['type']=='change'){
    // 转单模式：需要查询承运商名称
    $carrier_name = '';
    $carrier_number = '';
    
    if($data['transfer']==1){
        // 外部承运商
        $express = (new Express())->where('express_code',$data['tt_number'])->find();
        if($express){
            $carrier_name = $express['express_name'];
            $carrier_number = $data['tt_number'];
        }
    }else{
        // 自有物流
        $ditchdetail = DitchModel::detail($data['t_number']);
        if($ditchdetail){
            $carrier_name = $ditchdetail['ditch_name'];
            $carrier_number = $ditchdetail['ditch_id'];
        }
    }
    
    $upd['t2_number'] = $carrier_number;
    $upd['t2_name'] = $carrier_name;
    $upd['t2_order_sn'] = $data['t_order_sn'];
    $upd['updated_time'] = $update['updated_time'];
    $upd['status'] = $update['status'];
    $update = $upd;
    // ...
}
```

## 数据流程

### 表单提交
**文件**: `source/application/store/view/tr_order/changesn.php`

```
用户选择:
- transfer=1 → 外部承运商 → delivery[tt_number] = express_code
- transfer=0 → 自有物流 → delivery[t_number] = ditch_id
- delivery[t_order_sn] = 新单号
- delivery[type] = 'change'
```

### 后端处理
**文件**: `source/application/store/controller/TrOrder.php`

```php
public function deliverySave(){
   $model = (new Inpack());
   if ($model->modify($this->postData('delivery'))){
       return $this->renderSuccess('操作成功');
   } 
   return $this->renderError($model->getError() ?: '操作失败');
}
```

### 数据库更新
**表**: `yoshop_inpack`

```
更新字段:
- t2_number: 承运商代码 (express_code 或 ditch_id)
- t2_name: 承运商名称 (express_name 或 ditch_name)
- t2_order_sn: 新的国际单号
- status: 6 (已发货)
```

## 测试步骤

### 快速验证修复
运行测试脚本验证字段传递：
```bash
cd D:\2025profile\Lineminiapp
php test_transfer_simple.php
```

**预期输出**:
```
✓ tt_number 字段存在，值为: dhl
✓ transfer 字段存在，值为: 1
✓ 可以查询承运商
```

### 完整功能测试

1. **进入订单列表**
   - 访问: `/store/tr_order/all_list`
   - 找到一个已发货的订单 (status=6)

2. **点击转单**
   - 点击订单的"转单"按钮
   - 进入转单页面: `/store/tr_order/changesn?id={inpack_id}`

3. **选择外部承运商**
   - 选择"运输商"
   - 从下拉列表选择承运商 (例如: DHL, FedEx)
   - 输入新的国际单号
   - 点击"提交"

4. **验证结果**
   ```sql
   SELECT id, order_sn, t2_number, t2_name, t2_order_sn 
   FROM yoshop_inpack 
   WHERE id = {inpack_id};
   ```
   
   **预期结果**:
   - `t2_number`: 承运商代码 (例如: 'dhl')
   - `t2_name`: 承运商名称 (例如: 'DHL')
   - `t2_order_sn`: 新的国际单号

5. **测试自有物流**
   - 重复步骤2
   - 选择"自有物流"
   - 从下拉列表选择自有物流渠道
   - 输入新的国际单号
   - 点击"提交"
   - 验证 `t2_name` 为自有物流名称

## 相关文件

| 文件 | 说明 |
|------|------|
| `source/application/store/model/Inpack.php` | 集运单模型，包含 `modify()` 方法 |
| `source/application/store/controller/TrOrder.php` | 订单控制器，包含 `changesn()` 和 `deliverySave()` |
| `source/application/store/view/tr_order/changesn.php` | 转单表单页面 |
| `source/application/common/model/Express.php` | 承运商模型 |
| `source/application/store/model/Ditch.php` | 自有物流模型 |

## 注意事项

1. **数据库字段**
   - `t_number`, `t_name`, `t_order_sn`: 首次发货信息
   - `t2_number`, `t2_name`, `t2_order_sn`: 转单后的新信息

2. **17track 注册**
   - 只有外部承运商 (`transfer=1`) 才会注册到 17track
   - 自有物流不支持 17track 查询

3. **物流日志**
   - 转单操作会自动添加物流日志
   - 日志内容: "包裹转单操作，新单号为{t2_order_sn}"

## 修复日期
2026-01-15

## 修复状态
✅ 已完成
