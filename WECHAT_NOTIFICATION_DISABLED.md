# 微信通知已禁用

## 问题描述

在执行 `/store/tr_order/deliverySave` (发货保存)操作时,出现微信公众号API错误:

```
access_token获取失败，错误信息：
{
  "errcode": 40164,
  "errmsg": "invalid ip 171.224.177.166 ipv6 ::ffff:171.224.177.166, not in whitelist rid: 6968e171-00afe91f-7aaa04c1"
}
```

**错误原因:** 服务器IP `171.224.177.166` 不在微信公众号的IP白名单中。

## 根本原因

在 `Inpack::modify()` 方法中,有两处会触发微信公众号通知:

### 1. 查验通知 (第442-448行)

当 `verify=1` (完成查验)时:

```php
if($tplmsgsetting['is_oldtps']==1){
    $res =$this->sendEnterMessage([$pack],'payment');
}else{
    Message::send('package.sendpack',$pack);
}
```

### 2. 发货通知 (第545-549行)

当 `type='delivery'` (发货)时:

```php
if($tplmsgsetting['is_oldtps']==1){
    $res =$this->sendEnterMessage([$pack],'payment');
}else{
    Message::send('package.sendpack',$pack);
}
```

## 解决方案

### 方案 1: 禁用微信通知 (已实施)

**文件:** `Lineminiapp/source/application/store/model/Inpack.php`

注释掉所有微信通知代码:

```php
// 查验通知 (第442-448行)
// 注释掉微信通知,避免access_token错误
// if($tplmsgsetting['is_oldtps']==1){
//     $res =$this->sendEnterMessage([$pack],'payment');
// }else{
//     Message::send('package.sendpack',$pack);
// }

// 只发送LINE通知
// TODO: 添加LINE查验通知

// 发货通知 (第545-549行)
// 注释掉微信通知,避免access_token错误
// if($tplmsgsetting['is_oldtps']==1){
//     $res =$this->sendEnterMessage([$pack],'payment');
// }else{
//     Message::send('package.sendpack',$pack);
// }

// 只发送LINE通知
// TODO: 添加LINE发货通知
```

### 方案 2: 添加IP到微信白名单 (未实施)

如果需要保留微信通知功能:

1. 登录微信公众平台
2. 进入 "开发" -> "基本配置"
3. 在 "IP白名单" 中添加: `171.224.177.166`

**注意:** 由于项目已迁移到LINE平台,不建议使用此方案。

## 影响范围

### 已禁用的通知

| 操作 | 原通知方式 | 当前状态 |
|------|-----------|---------|
| 完成查验 | 微信模板消息 | ❌ 已禁用 |
| 订单发货 | 微信模板消息 | ❌ 已禁用 |

### 仍然生效的功能

| 功能 | 状态 |
|------|------|
| 物流信息记录 | ✅ 正常 |
| 邮件通知 | ✅ 正常 |
| 订单状态更新 | ✅ 正常 |
| 17track物流注册 | ✅ 正常 |

## 后续工作

### 1. 添加LINE通知 (推荐)

在注释掉微信通知的位置,添加LINE通知:

```php
// 查验通知
use app\common\service\message\line\Inwarehouse;

$lineNotice = new Inwarehouse();
$lineNotice->send($pack['member_id'], [
    'order_sn' => $pack['order_sn'],
    'status' => 'verified',
    'remark' => $noticesetting['enter']['describe']
]);

// 发货通知
$lineNotice->send($pack['member_id'], [
    'order_sn' => $pack['order_sn'],
    'status' => 'shipped',
    't_order_sn' => $pack['t_order_sn'],
    'remark' => '包裹已经发货'
]);
```

### 2. 配置LINE通知模板

在LINE后台配置相应的通知模板:
- 查验完成通知
- 订单发货通知

### 3. 测试验证

测试以下场景:
- 完成查验操作
- 订单发货操作
- 确认LINE通知正常发送
- 确认不再出现微信API错误

## 测试步骤

### 1. 测试发货操作

```
URL: /store/tr_order/deliverySave
参数:
  delivery[transfer]=1
  delivery[tt_number]=001
  delivery[t_order_sn]=1231233
  delivery[type]=delivery
  delivery[id]=69407
```

**预期结果:**
- ✅ 订单状态更新为"已发货"
- ✅ 物流信息记录成功
- ✅ 不再出现微信API错误
- ⚠️ 暂无LINE通知(待实现)

### 2. 测试查验操作

```
URL: /store/tr_order/modify_save
参数:
  data[id]=69407
  data[verify]=1
```

**预期结果:**
- ✅ 订单状态更新为"待支付"
- ✅ 物流信息记录成功
- ✅ 不再出现微信API错误
- ⚠️ 暂无LINE通知(待实现)

## 相关文件

- `Lineminiapp/source/application/store/model/Inpack.php` - 集运订单模型 (已修改)
- `Lineminiapp/source/application/store/controller/TrOrder.php` - 订单控制器
- `Lineminiapp/source/application/common/service/message/line/Inwarehouse.php` - LINE通知服务

## 修改日期

2026-01-15

## 相关文档

- [LINE_NOTIFICATION_INTEGRATION_GUIDE.md](./LINE_NOTIFICATION_INTEGRATION_GUIDE.md) - LINE通知集成指南
- [LINE_CONFIG_DEPLOYMENT_GUIDE.md](./LINE_CONFIG_DEPLOYMENT_GUIDE.md) - LINE配置部署指南
