# 唛头保存功能修复完成

## 修复时间
2026-01-15

## 问题描述

后台录入包裹时，可以选择唛头绑定到包裹中，但是在包裹管理中看不到唛头，数据库中 `usermark` 字段也为空。

## 根本原因

在 `Package.php` 模型的 `post()` 方法中，缺少对前端传递的 `mark` 字段的处理，导致唛头数据没有保存到数据库的 `usermark` 字段。

## 修复内容

### 修改文件
`Lineminiapp/source/application/store/model/Package.php`

### 修改位置
`post()` 方法中的 `$post` 数组（约第 363 行）

### 修改代码

**添加的代码行：**
```php
'usermark' => isset($data['mark'])?$data['mark']:'',
```

**完整的修改后代码：**
```php
$post = [
    'order_sn' => createSn(),
    'status' => $status,
    'member_id' => $data['user_id']??0,
    'express_num' =>$data['express_num'],
    'storage_id' => $data['shop_id'],
    'country_id' => $data['country'],
    'width' => $data['width'],
    'length' => $data['length'],
    'height' => $data['height'],
    'weight' => $data['weigth'],
    'remark' => $data['remark'],
    'pack_attr' => $data['pack_attr'],
    'num'=>$data['num'],
    'goods_attr' => isset($data['goods_attr'])?json_encode($data['goods_attr']):'',
    'image' => json_encode($image),
    'price' => $data['price'],
    'usermark' => isset($data['mark'])?$data['mark']:'',  // ← 新增这一行
    'member_name' => isset($this->userName)?$this->userName:'',
    'wxapp_id' =>self::$wxapp_id,
    'is_take'=> empty($data['user_id'])?1:2,
    'source' => 2,
    'created_time' => getTime(),
    'updated_time' => getTime(),
    'entering_warehouse_time' => getTime(),
];
```

## 验证结果

### 1. 代码验证
✅ Package.php 模型已包含 usermark 字段处理代码

### 2. 数据库验证
✅ 测试包裹（ID: 752133）成功保存唛头 "TEST-MARK-123"

查询结果：
```
包裹ID | 快递单号              | 用户ID | 唛头            | 创建时间
752133 | TEST1768415006       | 31966  | TEST-MARK-123   | 2026-01-15 02:23:27
```

## 数据流程

### 完整的数据流程

1. **前端页面** (`newadd.php`)
   ```html
   <input type="hidden" id="usermarkplus" name="data[mark]" value="唛头值" />
   ```

2. **控制器接收** (`Index.php`)
   ```php
   $data = $this->postData('data');
   // $data['mark'] = '唛头值'
   ```

3. **模型保存** (`Package.php`)
   ```php
   $post['usermark'] = isset($data['mark'])?$data['mark']:'';
   // 保存到数据库 yoshop_package.usermark 字段
   ```

4. **LINE 通知使用** (`Inwarehouse.php`)
   ```php
   $data['mark'] = !empty($orderInfo['usermark']) ? $orderInfo['usermark'] : '';
   // 发送 LINE 通知时显示唛头
   ```

## 使用说明

### 后台录入包裹时使用唛头

1. 登录后台管理系统
2. 进入【包裹管理】→【后台录入】
3. 选择用户（例如：31966）
4. 系统会自动加载该用户的唛头列表
5. 从下拉框选择唛头，或在输入框手动输入唛头
6. 填写其他包裹信息
7. 点击保存

### 查看包裹唛头

1. **包裹管理列表**
   - 在包裹列表中，唛头会显示在包裹信息中
   - 前提：后台设置中启用了唛头显示功能

2. **LINE 通知**
   - 包裹入库后，用户会收到 LINE 通知
   - 通知中会显示唛头信息（如果有）

3. **数据库查询**
   ```sql
   SELECT id, express_num, member_id, usermark 
   FROM yoshop_package 
   WHERE member_id = 31966 
   ORDER BY id DESC 
   LIMIT 10;
   ```

## 相关功能

### 1. 唛头管理
- 位置：用户管理 → 用户详情 → 唛头管理
- 功能：为用户添加、编辑、删除唛头

### 2. 唛头显示设置
- 位置：设置 → 电脑端设置
- 字段：`is_usermark` - 是否启用唛头功能
- 字段：`is_force_usermark` - 是否必填

### 3. LINE 通知
- 唛头会自动显示在入库通知中
- 空唛头会自动隐藏，不显示空白行

## 测试用户信息

**用户ID**: 31966

**已有唛头**:
1. ddddiw - 淘宝使用
2. mark2 - 唛头测试2

## 相关文档

1. `USERMARK_SAVE_FIX.md` - 详细修复文档
2. `LINE_NOTIFICATION_COMPLETE.md` - LINE 通知功能文档
3. `LINE_NOTIFICATION_FINAL_STATUS.md` - LINE 通知状态文档

## 验证脚本

1. `check_user_usermark.php` - 查询用户唛头
2. `verify_usermark_fix.php` - 验证修复效果

## 注意事项

1. **唛头来源**
   - 唛头必须从用户的唛头列表中选择
   - 或手动输入新唛头（会自动添加到用户唛头列表）
   - 不能使用不属于该用户的唛头

2. **数据库字段**
   - 表名：`yoshop_package`
   - 字段：`usermark` (VARCHAR)
   - 用途：存储包裹的唛头标识

3. **显示规则**
   - 包裹列表：需要在后台设置中启用唛头显示
   - LINE 通知：空唛头自动隐藏
   - 导出Excel：包含唛头列

## 状态

✅ 代码修复完成
✅ 功能测试通过
✅ 数据库验证通过
✅ 可以正常使用

## 完成时间

2026-01-15 02:30
