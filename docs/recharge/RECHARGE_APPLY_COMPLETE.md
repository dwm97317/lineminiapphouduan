# Recharge Apply Integration - Complete Fix

## Overview
Successfully integrated the `/mine/recharge` frontend page with the backend Certificate (汇款凭证) system.

## Issues Fixed

### 1. Method Not Found Error
**Error**: `方法不存在:app\api\controller\Recharge->Apply()`
**Fix**: Added `apply()` method to `Recharge` controller

### 2. Undefined Function Error
**Error**: `Call to undefined function app\api\controller\public_path()`
**Fix**: Changed to `ROOT_PATH . 'web/'` for file paths

### 3. Static Property Error
**Error**: `Access to undeclared static property: app\api\controller\Recharge::$wxapp_id`
**Fix**: Changed `self::$wxapp_id` to `$this->wxapp_id` (instance property)

### 4. Database Field Error - upload_file
**Error**: `数据表字段不存在:[file_path]`
**Fix**: Updated to use correct `yoshop_upload_file` table fields:
- `file_name` (not `file_path`) - stores the full path
- `extension` (not `file_ext`)
- `file_url` - for domain
- `file_type` - for type ('image')
- `is_user` - set to 1

### 5. DateTime Format Error
**Error**: `SQLSTATE[22007]: Invalid datetime format: 1292 Incorrect datetime value: '1768471922' for column 'create_time'`
**Root Cause**: ThinkPHP's Model class has automatic timestamp handling enabled by default, which uses Unix timestamps (integers), but the database expects DATETIME format (strings like '2026-01-15 10:00:00')

**Fix**: Disabled automatic timestamp handling in models:
- `Certificate` API model: Added `protected $autoWriteTimestamp = false;`
- `CertificateImage` model: Added `protected $autoWriteTimestamp = false;`
- Removed manual `create_time` and `update_time` from data arrays
- Let database handle timestamps with DEFAULT CURRENT_TIMESTAMP

## Implementation Details

### Frontend
- **Page**: `/mine/recharge` (`src/pages/Mine/Recharge.jsx`)
- **Fields**: 
  - Transfer Date (วันที่โอน)
  - Transfer Time (เวลาโอน)
  - Amount (จำนวนเงิน)
  - Screenshots (สกรีนช็อตการโอนเงิน) - multiple images
  - Remarks (หมายเหตุ) - optional

### Backend API
- **Endpoint**: `POST /api/recharge/apply`
- **Controller**: `Lineminiapp/source/application/api/controller/Recharge.php`
- **Method**: `apply()`

### Data Flow
1. Frontend uploads Base64 images
2. Backend decodes and saves images to `web/uploads/recharge/YYYYMMDD/`
3. Inserts file records into `yoshop_upload_file` table
4. Creates certificate record in `yoshop_certificate` table
5. Links images in `yoshop_certificate_image` table

### Database Tables
- **yoshop_certificate**: Main certificate record
  - `cert_order`: Order number (empty for recharge)
  - `cert_price`: Amount
  - `cert_bank`: Bank name (uses remarks or '线上转账')
  - `cert_type`: Currency type (2 = THB)
  - `cert_date`: Transfer date and time
  - `user_id`: User ID
  - `wxapp_id`: App ID
  - `create_time`: Auto-set by database
  - `update_time`: Auto-set by database

- **yoshop_certificate_image**: Image associations
  - `cert_id`: Certificate ID
  - `image_id`: File ID from upload_file table
  - `wxapp_id`: App ID
  - `create_time`: Auto-set by database

- **yoshop_upload_file**: File storage
  - `storage`: 'local'
  - `file_url`: Empty for local storage
  - `file_name`: Full path (e.g., 'uploads/recharge/20260115/abc123.jpg')
  - `file_size`: File size in bytes
  - `file_type`: 'image'
  - `extension`: File extension (jpg, png, etc.)
  - `is_user`: 1
  - `wxapp_id`: App ID
  - `create_time`: Auto-set by database

### Admin Review
- **URL**: `/store/setting.certificate/index`
- Admin can view, approve, or reject recharge applications

## Files Modified

1. `Lineminiapp/source/application/api/controller/Recharge.php`
   - Added `apply()` method
   - Handles Base64 image upload
   - Integrates with Certificate system

2. `Lineminiapp/source/application/api/model/Certificate.php`
   - Added `protected $autoWriteTimestamp = false;`
   - Removed manual timestamp handling from `add()` method
   - Removed manual timestamp from `saveAllImages()` method

3. `Lineminiapp/source/application/common/model/CertificateImage.php`
   - Added `protected $autoWriteTimestamp = false;`

## Testing
1. Navigate to `https://localhost:9000/mine/recharge`
2. Fill in transfer date, time, and amount
3. Upload screenshot(s)
4. Submit form
5. Verify success message
6. Check admin panel at `/store/setting.certificate/index`

## Key Learnings

### ThinkPHP Timestamp Handling
- ThinkPHP Model class has `autoWriteTimestamp` enabled by default
- Default behavior uses Unix timestamps (integers)
- MySQL DATETIME columns expect string format 'YYYY-MM-DD HH:MM:SS'
- Solution: Disable auto-timestamps and let database handle with DEFAULT CURRENT_TIMESTAMP

### File Upload Best Practices
- Use correct field names from database schema
- Check existing models for reference (e.g., Upload controller)
- Store full path in `file_name` for local storage
- Set `file_url` empty for local storage

### Integration Strategy
- Reuse existing systems (Certificate) instead of creating new tables
- Follow existing patterns in codebase
- Use instance properties (`$this->`) not static (`self::`) unless defined as static

## Status
✅ **COMPLETE** - All errors resolved, integration working correctly
