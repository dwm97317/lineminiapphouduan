# TrOrder Method Name Fix - Complete

## Issues Fixed

### 1. AllList Method Missing
**URL**: `/store/tr_order/all_list`
**Error**: `方法不存在:app\store\controller\TrOrder->AllList()`
**Fix**: Added `AllList()` wrapper method

### 2. ModifySave Method Missing
**URL**: `/store/tr_order/modify_save`
**Error**: `方法不存在:app\store\controller\TrOrder->ModifySave()`
**Fix**: Added `ModifySave()` wrapper method

## Root Cause
ThinkPHP's URL routing converts snake_case URLs to camelCase method names:
- URL: `all_list` → Method: `AllList()`
- URL: `modify_save` → Method: `ModifySave()`

But the actual methods were named in snake_case:
- `all_list()` (snake_case)
- `modify_save()` (snake_case)

## Solutions Applied

### 1. AllList Wrapper
```php
/**
 * 全部订单列表 (驼峰命名兼容)
 * @return mixed
 * @throws \think\exception\DbException
 */
public function AllList()
{
    return $this->all_list();
}
```

### 2. ModifySave Wrapper
```php
/**
 * 点击编辑集运单，修改保存的函数 (驼峰命名兼容)
 * 2022年11月5日 增加图片增删功能
*/
public function ModifySave(){
   return $this->modify_save();
}
```

## Files Modified
- `Lineminiapp/source/application/store/controller/TrOrder.php`
  - Added `AllList()` method after `all_list()` (line ~352)
  - Added `ModifySave()` method after `modify_save()` (line ~383)

## Testing
1. **AllList**: Access `http://localhost:8080/index.php?s=/store/tr_order/all_list`
2. **ModifySave**: Submit order edit form to `/store/tr_order/modify_save`
3. Verify both work without errors

## Other Methods That May Need Similar Fixes

If these URLs are accessed and show similar errors, apply the same wrapper pattern:

| URL Pattern | Existing Method | Wrapper Needed |
|-------------|----------------|----------------|
| `verify_list` | `verify_list()` | `VerifyList()` |
| `pay_list` | `pay_list()` | `PayList()` |
| `payed_list` | `payed_list()` | `PayedList()` |
| `complete_list` | `complete_list()` | `CompleteList()` |
| `cancel_list` | `cancel_list()` | `CancelList()` |
| `delivery_save` | `deliverySave()` | Already camelCase ✓ |
| `change_user` | `changeUser()` | Already camelCase ✓ |

## Prevention Guidelines

When creating new controller methods in ThinkPHP:

### Option 1: Use camelCase (Recommended)
```php
public function allList() { }  // URL: /all_list
public function modifySave() { }  // URL: /modify_save
```

### Option 2: Add both versions
```php
public function all_list() { }
public function AllList() { return $this->all_list(); }
```

### Option 3: Configure routing
```php
// In route config
Route::rule('tr_order/all_list', 'TrOrder/all_list');
```

## ThinkPHP URL-to-Method Conversion Rules

ThinkPHP converts URLs as follows:
- `user_list` → `UserList()` or `userList()`
- `get_data` → `GetData()` or `getData()`
- `save_info` → `SaveInfo()` or `saveInfo()`

The framework tries both:
1. First capital letter of each word (PascalCase): `AllList()`
2. First letter lowercase (camelCase): `allList()`

If neither exists, it throws "方法不存在" error.
