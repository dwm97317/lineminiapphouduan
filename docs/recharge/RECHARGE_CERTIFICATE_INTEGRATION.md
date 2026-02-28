# Recharge Certificate Integration

## Overview
充值功能已集成到现有的汇款凭证（Certificate）系统，管理员可以在后台统一管理所有充值申请。

## System Integration

### Frontend
- **Route:** `/mine/recharge`
- **Component:** `zalo_mini_app-master/src/pages/Mine/Recharge.jsx`
- **API Endpoint:** `POST /api/recharge/apply`

### Backend
- **Controller:** `source/application/api/controller/Recharge.php`
- **Model:** `source/application/api/model/Certificate.php`
- **Admin Panel:** `/store/setting.certificate/index`

## Data Flow

```
Frontend (Recharge Page)
    ↓
API: /api/recharge/apply
    ↓
Recharge Controller
    ↓
Certificate Model
    ↓
Database Tables:
  - yoshop_certificate (凭证主表)
  - yoshop_certificate_image (凭证图片关联表)
  - yoshop_upload_file (文件表)
```

## Database Tables

### yoshop_certificate (汇款凭证表)
| Field | Type | Description |
|-------|------|-------------|
| cert_id | int(11) | 凭证ID (主键) |
| user_id | int(11) | 用户ID |
| cert_order | varchar(255) | 订单号 (充值时为空) |
| cert_price | decimal(10,2) | 金额 |
| cert_bank | varchar(255) | 银行名称/备注 |
| cert_type | tinyint(1) | 币种: 0=人民币, 1=新币, 2=美元, 3=欧元, 4=日元, 5=韩元, 6=其他 |
| cert_date | datetime | 转账日期时间 |
| status | tinyint(1) | 状态: 1=待审核, 2=审核通过, 3=信息有误 |
| wxapp_id | int(11) | 小程序ID |
| create_time | int(11) | 创建时间 |
| update_time | int(11) | 更新时间 |

### yoshop_certificate_image (凭证图片关联表)
| Field | Type | Description |
|-------|------|-------------|
| id | int(11) | 主键 |
| cert_id | int(11) | 凭证ID |
| image_id | int(11) | 图片ID (关联upload_file表) |
| wxapp_id | int(11) | 小程序ID |
| create_time | int(11) | 创建时间 |

### yoshop_upload_file (文件表)
| Field | Type | Description |
|-------|------|-------------|
| file_id | int(11) | 文件ID (主键) |
| storage | varchar(20) | 存储方式: local |
| file_name | varchar(255) | 文件名 |
| file_path | varchar(255) | 文件路径 |
| file_size | int(11) | 文件大小 |
| file_ext | varchar(20) | 文件扩展名 |
| wxapp_id | int(11) | 小程序ID |
| create_time | int(11) | 创建时间 |

## API Specification

### Request
```http
POST /api/recharge/apply?wxapp_id=10001
Content-Type: application/json

{
  "token": "user_token",
  "transfer_date": "2026-01-15",
  "transfer_time": "14:30",
  "amount": 100.50,
  "screenshots": [
    "data:image/png;base64,iVBORw0KGgo...",
    "data:image/jpeg;base64,/9j/4AAQSkZJ..."
  ],
  "remarks": "ธนาคารกสิกรไทย"
}
```

### Response (Success)
```json
{
  "code": 1,
  "msg": "提交成功",
  "data": {
    "message": "充值申请提交成功，请等待审核"
  }
}
```

### Response (Error)
```json
{
  "code": 0,
  "msg": "请选择转账日期"
}
```

## Data Mapping

| Frontend Field | Certificate Field | Value |
|----------------|-------------------|-------|
| transfer_date + transfer_time | cert_date | "2026-01-15 14:30" |
| amount | cert_price | 100.50 |
| remarks | cert_bank | "ธนาคารกสิกรไทย" |
| - | cert_type | 2 (泰铢) |
| - | cert_order | "" (空，充值不关联订单) |
| - | status | 1 (待审核) |
| screenshots | image_id[] | [123, 124, 125] |

## Currency Types (cert_type)

| Value | Currency | Thai |
|-------|----------|------|
| 0 | 人民币 | หยวน |
| 1 | 新币 | ดอลลาร์สิงคโปร์ |
| 2 | 美元 | ดอลลาร์สหรัฐ |
| 3 | 欧元 | ยูโร |
| 4 | 日元 | เยน |
| 5 | 韩元 | วอน |
| 6 | 其他 | อื่นๆ |

**Note:** Currently hardcoded to `2` (美元/USD) for Thai Baht. Consider updating to match actual currency.

## Status Flow

```
1. 用户提交充值申请
   ↓
   status = 1 (待审核)
   
2. 管理员审核
   ↓
   status = 2 (审核通过) → 充值到账
   或
   status = 3 (信息有误) → 用户需重新提交
```

## Admin Panel

### Access
```
http://localhost:8080/store/setting.certificate/index
```

### Features
- ✅ View all recharge applications
- ✅ Filter by status
- ✅ View user information
- ✅ View transfer screenshots
- ✅ Approve/Reject applications
- ✅ Add admin remarks
- ✅ Delete applications

### Actions
1. **Approve (审核通过)**
   - Update status to 2
   - Credit user balance (需要额外实现)
   
2. **Reject (信息有误)**
   - Update status to 3
   - Add admin remark explaining issue

3. **Delete (删除)**
   - Remove certificate record
   - Remove associated images

## Image Processing

### Upload Flow
1. Frontend converts file to Base64
2. Backend receives Base64 string
3. Decode and validate image
4. Save to: `web/uploads/recharge/YYYYMMDD/`
5. Insert into `yoshop_upload_file` table
6. Get `file_id`
7. Link to certificate via `yoshop_certificate_image`

### Storage Path
```
web/uploads/recharge/20260115/
  ├── 678a9b1c2d3e4_0.jpg
  ├── 678a9b1c2d3e4_1.png
  └── 678a9b1c2d3e4_2.jpg
```

### Web Access
```
http://localhost:8080/uploads/recharge/20260115/678a9b1c2d3e4_0.jpg
```

## Testing

### 1. Test API Directly
```bash
# Open in browser
http://localhost:8080/test_recharge_apply.php
```

### 2. Test from Frontend
```bash
# Navigate to recharge page
http://localhost:9000/mine/recharge

# Fill form and submit
```

### 3. Check Admin Panel
```bash
# View applications
http://localhost:8080/store/setting.certificate/index
```

### 4. Verify Database
```sql
-- View recent certificates
SELECT 
  c.cert_id,
  c.user_id,
  c.cert_price,
  c.cert_bank,
  c.cert_date,
  c.status,
  COUNT(ci.image_id) as image_count
FROM yoshop_certificate c
LEFT JOIN yoshop_certificate_image ci ON c.cert_id = ci.cert_id
WHERE c.cert_order = ''
GROUP BY c.cert_id
ORDER BY c.cert_id DESC
LIMIT 10;

-- View certificate images
SELECT 
  ci.*,
  uf.file_path,
  uf.file_name
FROM yoshop_certificate_image ci
JOIN yoshop_upload_file uf ON ci.image_id = uf.file_id
WHERE ci.cert_id = 123;
```

## Advantages of Integration

### ✅ Unified Management
- All payment certificates in one place
- Consistent admin interface
- Shared image management

### ✅ No New Tables
- Uses existing database structure
- No migration needed
- Maintains data consistency

### ✅ Proven System
- Already tested and working
- Familiar to administrators
- Existing workflows apply

## Future Enhancements

### 1. Auto Balance Credit
Add logic to automatically credit user balance when status changes to 2:

```php
// In Certificate model edit() method
if ($cert_status == 2) {
    // Credit user balance
    $user = User::detail($this->user_id);
    $user->setInc('balance', $this->cert_price);
    
    // Log transaction
    // ...
}
```

### 2. Currency Selection
Allow users to select currency type in frontend:
- Add dropdown for currency selection
- Pass `coin_type` in API request
- Display in admin panel

### 3. Notification
Send notification when status changes:
- LINE message when approved
- LINE message when rejected
- Email notification (optional)

### 4. Receipt Generation
Generate PDF receipt after approval:
- Include certificate details
- Show transfer information
- Add QR code for verification

## Related Files

### Backend
- `source/application/api/controller/Recharge.php` - Recharge API
- `source/application/api/model/Certificate.php` - Certificate model
- `source/application/store/controller/setting/Certificate.php` - Admin controller
- `source/application/common/model/Certificate.php` - Base model

### Frontend
- `zalo_mini_app-master/src/pages/Mine/Recharge.jsx` - Recharge page
- `zalo_mini_app-master/src/components/ImageUploader/Index.jsx` - Image uploader

### Documentation
- `RECHARGE_APPLY_FIX.md` - Initial implementation
- `RECHARGE_TABLE_SETUP.md` - Database setup (deprecated)
- `RECHARGE_CERTIFICATE_INTEGRATION.md` - This file

## Migration Notes

### From Old System
If you previously created `yoshop_recharge_apply` table:
1. No need to drop it
2. New submissions will use Certificate system
3. Old data can be migrated if needed

### Migration Script (Optional)
```sql
-- Migrate old recharge_apply to certificate
INSERT INTO yoshop_certificate (
  user_id,
  cert_order,
  cert_price,
  cert_bank,
  cert_type,
  cert_date,
  status,
  wxapp_id,
  create_time,
  update_time
)
SELECT 
  user_id,
  '',
  amount,
  remarks,
  2,
  CONCAT(transfer_date, ' ', transfer_time),
  CASE status
    WHEN 0 THEN 1
    WHEN 1 THEN 2
    WHEN 2 THEN 3
  END,
  10001,
  create_time,
  update_time
FROM yoshop_recharge_apply;
```

## Troubleshooting

### Error: Certificate model not found
```bash
# Check file exists
ls source/application/api/model/Certificate.php
```

### Error: Image upload failed
```bash
# Check directory permissions
chmod 755 web/uploads/recharge/
```

### Error: Image not displaying in admin
```bash
# Check file path in database
SELECT file_path FROM yoshop_upload_file WHERE file_id = 123;

# Verify file exists
ls web/uploads/recharge/20260115/
```

## Summary

充值功能现已完全集成到现有的汇款凭证系统：
- ✅ 使用现有数据库表
- ✅ 管理员在统一后台管理
- ✅ 支持图片上传和预览
- ✅ 完整的审核流程
- ✅ 无需创建新表

管理员可以在 `/store/setting.certificate/index` 查看和处理所有充值申请。
