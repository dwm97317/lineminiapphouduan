# 客户联系配置 - SettingEnum 修复

## 问题描述

**错误信息**:
```
Setting.php line 128 未定义数组索引: customer_contact
```

## 问题原因

在 `Setting` 模型的 `edit()` 方法中（第 128 行），代码尝试访问：

```php
'describe' => SettingEnum::data()[$key]['describe']
```

但 `customer_contact` 这个 key 在 `SettingEnum` 枚举类中不存在，导致数组索引未定义错误。

## 解决方案

在 `SettingEnum` 中添加 `customer_contact` 的定义。

### 修改文件

**文件**: `Lineminiapp/source/application/common/enum/Setting.php`

### 修改内容

1. **添加常量定义**:
```php
// LINE小程序设置
const LINE_CONFIG = 'line_config';
// LINE 消息通知
const LINE_MESSAGING = 'line_messaging';
// LINE Pay 支付
const LINE_PAY = 'line_pay';
// 客户联系配置
const CUSTOMER_CONTACT = 'customer_contact';  // ✅ 新增
```

2. **在 data() 方法中添加配置**:
```php
self::LINE_PAY => [
    'value' => self::LINE_PAY,
    'describe' => 'LINE Pay支付设置',
],
self::CUSTOMER_CONTACT => [  // ✅ 新增
    'value' => self::CUSTOMER_CONTACT,
    'describe' => '客户联系配置',
],
```

## 完整的数据流程

现在保存客户联系配置时的完整流程：

1. **前端提交数据**:
   ```
   POST /store/setting.line_config/index
   customer_contact[hotline_th]=+66 12345678
   customer_contact[line_support]=766eifnw
   customer_contact[wechat]=dwm97317
   ```

2. **LineConfig 控制器处理**:
   ```php
   $data = $this->postData();
   $key = key($data);  // 'customer_contact'
   
   // 验证数据
   $this->validateCustomerContact($data[$key]);
   
   // 调用 Setting 模型保存
   $model->edit($key, $data[$key]);
   ```

3. **Setting 模型保存**:
   ```php
   public function edit($key, $values) {
       // ...
       return $model->save([
           'key' => $key,  // 'customer_contact'
           'describe' => SettingEnum::data()[$key]['describe'],  // ✅ 现在可以找到了
           'values' => $values,
           'wxapp_id' => self::$wxapp_id,
       ]);
   }
   ```

4. **数据库存储**:
   ```sql
   INSERT INTO yoshop_setting (key, describe, values, wxapp_id)
   VALUES ('customer_contact', '客户联系配置', '{"hotline_th":"+66 12345678",...}', 10001)
   ```

## 验证

现在可以正常保存客户联系配置：

1. 访问后台 LINE 设置 → 客户联系 Tab
2. 填写客服信息
3. 点击保存
4. ✅ 保存成功，数据写入数据库

## 相关文件

1. `Lineminiapp/source/application/common/enum/Setting.php` - ✅ 已修复
2. `Lineminiapp/source/application/store/model/Setting.php` - 使用 SettingEnum
3. `Lineminiapp/source/application/store/controller/setting/LineConfig.php` - 调用 edit 方法

## 修复时间

2026-01-16

## 状态

✅ 已修复并验证
