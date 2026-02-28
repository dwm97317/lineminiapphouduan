# Recharge Certificate Image Upload Fix

## Issue
When submitting recharge applications, the system showed a database error:
```
SQLSTATE[01000]: Warning: 1265 Data truncated for column 'create_time' at row 1
```

## Root Cause
The database uses **different field types** for timestamps in different tables:

| Table | Field | Type | Required Format |
|-------|-------|------|----------------|
| `yoshop_certificate` | `create_time` | **INT** | Unix timestamp (`time()`) |
| `yoshop_certificate` | `update_time` | **INT** | Unix timestamp (`time()`) |
| `yoshop_certificate_image` | `create_time` | **DATETIME** | Date string (`Y-m-d H:i:s`) |

The code was using `time()` (Unix timestamp) for all tables, which caused the DATETIME field to receive values like `1768471922` instead of `2026-01-15 18:28:00`.

## Solution
Updated `Certificate::saveAllImages()` method to use the correct format:

```php
// Before (WRONG - causes data truncation)
'create_time'=> time(), // Unix timestamp like 1768471922

// After (CORRECT - DATETIME format)
'create_time'=> date("Y-m-d H:i:s"), // Format: 2026-01-15 18:28:00
```

## Files Modified
- `Lineminiapp/source/application/api/model/Certificate.php`
  - Line 44: Changed `time()` to `date("Y-m-d H:i:s")` in `saveAllImages()` method

## Verification
Run diagnostic script to check field types:
```bash
php web/check_certificate_table_structure.php
```

## Testing
1. Submit recharge application at `/mine/recharge`
2. Check backend at `/store/setting.certificate/index`
3. Verify:
   - ✅ No database errors
   - ✅ Images display correctly
   - ✅ `create_time` shows correct date (not 1970-01-01)
   - ✅ Currency type shows "泰铢" (Thai Baht)

## Related Files
- Frontend: `zalo_mini_app-master/src/pages/Mine/Recharge.jsx`
- API Controller: `Lineminiapp/source/application/api/controller/Recharge.php`
- Model: `Lineminiapp/source/application/api/model/Certificate.php`
- Backend View: `Lineminiapp/source/application/store/view/setting/certificate/index.php`

## Previous Fixes
1. ✅ Method not found → Added `apply()` method
2. ✅ `public_path()` undefined → Changed to `ROOT_PATH . 'web/'`
3. ✅ Static property error → Changed to `$this->wxapp_id`
4. ✅ Database field error → Fixed field names
5. ✅ Image upload → Changed to cloud storage (Aliyun OSS)
6. ✅ Currency type → Added "泰铢" at index 2
7. ✅ **create_time format → Fixed DATETIME vs INT mismatch**
