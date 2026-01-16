# 唛头保存功能修复文档

## 问题描述

后台录入包裹时，可以选择唛头绑定到包裹中，但是在包裹管理中看不到唛头，数据库中 `usermark` 字段也为空。

## 问题原因

在 `Package.php` 模型的 `post()` 方法中，没有处理前端传递的 `mark` 字段，导致唛头数据没有保存到数据库。

### 前端传递的字段

前端页面 `newadd.php` 中，唛头字段的 name 属性为：
```html
<input type="hidden" id="usermarkplus" name="data[mark]" />
```

### 后端接收的字段

控制器接收到的数据结构：
```php
$data = $this->postData('data');
// $data['mark'] 包含唛头值
```

## 修复方案

### 修改文件

`Lineminiapp/source/application/store/model/Package.php`

### 修改内容

在 `post()` 方法的 `$post` 数组中添加 `usermark` 字段：

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
    'usermark' => isset($data['mark'])?$data['mark']:'',  // 新增这一行
    'member_name' => isset($this->userName)?$this->userName:'',
    'wxapp_id' =>self::$wxapp_id,
    'is_take'=> empty($data['user_id'])?1:2,
    'source' => 2,
    'created_time' => getTime(),
    'updated_time' => getTime(),
    'entering_warehouse_time' => getTime(),
];
```

## 数据流程

1. **前端选择唛头**
   - 用户在后台录入页面选择用户
   - 系统通过 AJAX 加载该用户的唛头列表
   - 用户选择唛头或手动输入唛头
   - 唛头值保存在隐藏字段 `data[mark]` 中

2. **后端接收数据**
   - 控制器 `Index.php` 的 `post()` 方法接收 `$this->postData('data')`
   - 数据包含 `mark` 字段

3. **模型保存数据**
   - `Package.php` 的 `post()` 方法处理数据
   - 将 `$data['mark']` 映射到 `usermark` 字段
   - 保存到数据库 `yoshop_package` 表

4. **LINE 通知使用**
   - `Inwarehouse.php` 读取 `usermark` 字段
   - 发送 LINE 通知时显示唛头信息

## 测试步骤

### 1. 准备测试数据

查询用户的唛头：
```bash
php check_user_usermark.php
```

### 2. 后台录入测试

1. 登录后台管理系统
2. 进入【包裹管理】→【后台录入】
3. 填写包裹信息：
   - 选择用户：31966
   - 快递单号：TEST123456
   - 选择唛头：ddddiw（或其他已存在的唛头）
   - 填写其他必填信息
4. 点击保存

### 3. 验证结果

查询数据库：
```sql
SELECT id, express_num, member_id, usermark, created_time 
FROM yoshop_package 
WHERE express_num = 'TEST123456';
```

预期结果：
- `usermark` 字段应该包含选择的唛头值
- 在包裹管理列表中应该显示唛头信息

### 4. LINE 通知验证

录入包裹后，用户应该在 LINE 上收到通知，通知中应该包含唛头信息。

## 相关文件

1. **前端页面**
   - `source/application/store/view/package/index/newadd.php` - 后台录入页面
   - `source/application/store/view/package/index/index.php` - 包裹列表页面

2. **控制器**
   - `source/application/store/controller/package/Index.php` - 包裹控制器

3. **模型**
   - `source/application/store/model/Package.php` - 包裹模型（已修复）

4. **LINE 通知**
   - `source/application/common/service/message/line/Inwarehouse.php` - 入库通知

## 数据库字段

**表名**: `yoshop_package`

**字段**: `usermark`
- 类型: VARCHAR
- 说明: 存储包裹的唛头信息
- 用途: 用于包裹识别和 LINE 通知显示

## 注意事项

1. **唛头来源**
   - 唛头必须从用户管理中获取
   - 不能随意编造唛头
   - 用户的唛头存储在 `yoshop_user_mark` 表中

2. **前端逻辑**
   - 选择用户后，通过 AJAX 加载该用户的唛头列表
   - 可以选择已有唛头或手动输入新唛头
   - 手动输入的唛头会自动添加到用户的唛头列表

3. **显示规则**
   - 唛头为空时，LINE 通知中不显示该字段
   - 包裹列表中只有启用唛头功能时才显示

## 完成时间

2026-01-15

## 状态

✅ 代码已修复
⏳ 等待实际测试验证
