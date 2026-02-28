# Recharge Apply Table Setup Guide

## Issue
```
SQLSTATE[42S02]: Base table or view not found: 1146 
Table 'xinsuju.yoshop_recharge_apply' doesn't exist
```

The recharge apply API is trying to insert data into a table that doesn't exist yet.

## Database Information
- **Database Name:** `xinsuju`
- **Table Prefix:** `yoshop_`
- **Table Name:** `yoshop_recharge_apply`
- **Host:** 103.119.1.84
- **Port:** 3306

## Quick Setup (Recommended)

### Method 1: Use PHP Script (Easiest)
1. Open in browser:
   ```
   http://localhost:8080/create_recharge_table.php
   ```

2. The script will:
   - ✅ Connect to database
   - ✅ Check if table exists
   - ✅ Create table if needed
   - ✅ Display table structure
   - ✅ Show indexes

3. Click "测试充值API" button to test

### Method 2: Use SQL File
1. Execute the SQL file:
   ```bash
   mysql -h 103.119.1.84 -u xinsuju -p xinsuju < create_recharge_apply_table.sql
   ```

2. Enter password when prompted: `cJGzwZTDCLHzWXN4`

### Method 3: phpMyAdmin
1. Login to phpMyAdmin
2. Select database: `xinsuju`
3. Go to SQL tab
4. Copy and paste SQL from `create_recharge_apply_table.sql`
5. Click "Go"

## Table Structure

```sql
CREATE TABLE `yoshop_recharge_apply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '申请ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `transfer_date` varchar(20) NOT NULL DEFAULT '' COMMENT '转账日期',
  `transfer_time` varchar(20) NOT NULL DEFAULT '' COMMENT '转账时间',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `screenshots` text COMMENT '转账截图(JSON数组)',
  `remarks` varchar(500) DEFAULT '' COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态: 0=待审核, 1=已通过, 2=已拒绝',
  `admin_remark` varchar(500) DEFAULT '' COMMENT '管理员备注',
  `reviewed_by` int(11) unsigned DEFAULT NULL COMMENT '审核人ID',
  `reviewed_time` int(11) unsigned DEFAULT NULL COMMENT '审核时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='转账充值申请表';
```

## Field Descriptions

| Field | Type | Description |
|-------|------|-------------|
| `id` | int(11) | Primary key, auto increment |
| `user_id` | int(11) | User ID from yoshop_user table |
| `transfer_date` | varchar(20) | Transfer date (YYYY-MM-DD) |
| `transfer_time` | varchar(20) | Transfer time (HH:MM) |
| `amount` | decimal(10,2) | Recharge amount in THB |
| `screenshots` | text | JSON array of screenshot paths |
| `remarks` | varchar(500) | User remarks (optional) |
| `status` | tinyint(1) | 0=Pending, 1=Approved, 2=Rejected |
| `admin_remark` | varchar(500) | Admin review notes |
| `reviewed_by` | int(11) | Admin user ID who reviewed |
| `reviewed_time` | int(11) | Unix timestamp of review |
| `create_time` | int(11) | Unix timestamp of creation |
| `update_time` | int(11) | Unix timestamp of last update |

## Indexes

| Index Name | Column | Type |
|------------|--------|------|
| PRIMARY | id | Unique |
| user_id | user_id | Index |
| status | status | Index |
| create_time | create_time | Index |

## Verification

After creating the table, verify it exists:

```sql
-- Check table exists
SHOW TABLES LIKE 'yoshop_recharge_apply';

-- View table structure
DESCRIBE yoshop_recharge_apply;

-- View indexes
SHOW INDEX FROM yoshop_recharge_apply;
```

## Testing

### 1. Test Table Creation
```
http://localhost:8080/create_recharge_table.php
```

### 2. Test API Endpoint
```
http://localhost:8080/test_recharge_apply.php
```

### 3. Test from Frontend
1. Navigate to: `http://localhost:9000/mine/recharge`
2. Fill in the form
3. Upload screenshots
4. Submit

### 4. Check Database
```sql
-- View all applications
SELECT * FROM yoshop_recharge_apply ORDER BY id DESC;

-- View pending applications
SELECT * FROM yoshop_recharge_apply WHERE status = 0;

-- View by user
SELECT * FROM yoshop_recharge_apply WHERE user_id = 123;
```

## Troubleshooting

### Error: Table already exists
- This is fine, the table is already created
- You can proceed to test the API

### Error: Access denied
- Check database credentials in `source/application/database.php`
- Verify user has CREATE TABLE permission

### Error: Connection refused
- Check database host and port
- Verify database server is running
- Check firewall settings

## Next Steps

1. ✅ Create the table using one of the methods above
2. ✅ Test the API endpoint
3. ✅ Test from frontend
4. 🔄 Create admin panel to review applications
5. 🔄 Add balance crediting logic on approval

## Files

| File | Purpose |
|------|---------|
| `create_recharge_table.php` | PHP script to create table (recommended) |
| `create_recharge_apply_table.sql` | SQL file for manual execution |
| `test_recharge_apply.php` | Test script for API |
| `test_recharge_path.php` | Test file upload paths |

## Related Documentation
- `RECHARGE_APPLY_FIX.md` - API implementation details
- `zalo_mini_app-master/src/pages/Mine/Recharge.jsx` - Frontend code
- `source/application/api/controller/Recharge.php` - Backend code
