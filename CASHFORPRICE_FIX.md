# CashforPrice 数据库错误修复

## 问题描述

访问 `/store/tr_order/cashforPrice` 时出现数据库错误:

```
SQLSTATE[HY000]: General error: 1366 Incorrect integer value: 'remove' for column 'sence_type' at row 1
```

**请求参数:**
- URL: `/store/tr_order/cashforPrice`
- 参数: `id=69407&user_id=31966`

## 根本原因

`yoshop_user_balance_log` 表的 `sence_type` 字段类型为 `tinyint`,但 `User::logUpdate()` 方法直接传入字符串 `'remove'`,导致数据库类型错误。

**数据库表结构:**
```sql
sence_type: tinyint YES  1
```

**问题代码 (TrOrder::cashforprice 第417行):**
```php
$res = $user->logUpdate('remove', $data['user_id'], $payprice, ...);
```

## 解决方案

### 修复: User::logUpdate() 类型转换

**文件:** `Lineminiapp/source/application/common/model/User.php`

在 `logUpdate()` 方法中添加类型转换逻辑:

```php
public function logUpdate($type,$member_id,$amount,$remark){
    $member = self::find($member_id);
    
    // 将字符串类型转换为整数
    switch ($type) {
        case 'add':
            $type = 1;
            break;
        case 'remove':
            $type = 2;
            break;
        default:
            // 如果已经是整数,保持不变
            $type = is_numeric($type) ? (int)$type : 1;
            break;
    }
    
    // 新增余额变动记录
    BalanceLog::add(SceneEnum::CONSUME, [
        'user_id' => $member['user_id'],
        'money' => 0,
        'remark' => $remark,
        'sence_type' => $type,  // 现在是整数
        'wxapp_id' => (new Package())->getWxappId(),
    ], [$member['nickName']]);
    return true;
}
```

## 类型映射

| 字符串值 | 整数值 | 说明 |
|---------|--------|------|
| `'add'` | `1` | 增加余额 |
| `'remove'` | `2` | 减少余额 |

## 测试验证

修复后,访问以下URL应该正常工作:

```
/store/tr_order/cashforPrice?id=69407&user_id=31966
```

## 相关文件

- `Lineminiapp/source/application/common/model/User.php` - User模型 (已修复)
- `Lineminiapp/source/application/store/controller/TrOrder.php` - TrOrder控制器
- `Lineminiapp/source/application/common/model/user/BalanceLog.php` - BalanceLog模型
- Database: `yoshop_user_balance_log` 表

## 修复日期

2026-01-15

## 相关修复

- [TR_ORDER_ALL_LIST_FIX.md](./TR_ORDER_ALL_LIST_FIX.md) - AllList/ModifySave方法修复

## 注意事项

如果遇到缓存问题,请运行:
```bash
php clear_cache.php
```
