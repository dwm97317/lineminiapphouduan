# Hướng Dẫn Liên Kết Tài Khoản FB/IG Bot

## 🎯 Luồng liên kết

### Các bước thực hiện

1. **Người dùng nhập Customer ID** trong bot FB/IG
   ```
   User: CUST_123456
   ```

2. **Bot gọi API backend**
   ```
   POST /api/v1/account/bind
   ```

3. **Backend xác thực Customer ID**
   - Gọi Bot API: `GET /api/bot/customer/verify`
   - Kiểm tra Customer ID có tồn tại không
   - Kiểm tra chưa bị liên kết với tài khoản khác

4. **Lưu vào database**
   - Bảng: `yoshop_platform_account`
   - Lưu thông tin: user_id, customer_id, platform_type, ...

5. **Bot phản hồi xác nhận**
   ```
   ✅ Liên kết thành công!
   
   Tài khoản đã关联：N***
   
   Bây giờ bạn có thể sử dụng tất cả chức năng!
   ```

---

## 📡 API Endpoints

### 1. Liên kết tài khoản
```
POST /api/v1/account/bind
```

**Request:**
```json
{
  "wxapp_id": "10001",
  "token": "user_token",
  "customer_id": "CUST_123456",
  "platform_type": "FACEBOOK"
}
```

**Response (thành công):**
```json
{
  "code": 1,
  "msg": "Liên kết thành công！Đã关联 tài khoản：N***",
  "data": {
    "customer_id": "CUST_123456",
    "platform_type": "FACEBOOK",
    "customer_name_anonymized": "N***",
    "binding_time": "2026-03-20 10:30:00"
  }
}
```

**Response (thất bại):**
```json
{
  "code": 0,
  "msg": "Customer ID này đã được liên kết với tài khoản khác",
  "data": {}
}
```

---

### 2. Xem danh sách đã liên kết
```
GET /api/v1/account/bindings
```

**Response:**
```json
{
  "code": 1,
  "msg": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "platform_type": "FACEBOOK",
        "customer_id": "CUST_123456",
        "customer_name_anonymized": "N***",
        "binding_time": "2026-03-20 10:30:00",
        "status": 1
      }
    ],
    "total": 1
  }
}
```

---

### 3. Hủy liên kết
```
POST /api/v1/account/unbind
```

**Request:**
```json
{
  "wxapp_id": "10001",
  "token": "user_token",
  "id": 1
}
```

**Response:**
```json
{
  "code": 1,
  "msg": "Hủy liên kết thành công",
  "data": []
}
```

---

### 4. Xác minh Customer ID (không liên kết)
```
POST /api/v1/account/verify-customer
```

**Request:**
```json
{
  "wxapp_id": "10001",
  "token": "user_token",
  "customer_id": "CUST_123456"
}
```

**Response:**
```json
{
  "code": 1,
  "msg": "Xác minh thành công",
  "data": {
    "customer_id": "CUST_123456",
    "customer_name_anonymized": "N***",
    "verified": true
  }
}
```

---

## ⚠️ Xử lý lỗi

### Các lỗi thường gặp

| Lỗi | Nguyên nhân | Cách khắc phục |
|-----|-------------|----------------|
| "请输入 Customer ID" | Thiếu customer_id | Nhập Customer ID đầy đủ |
| "请先登录" | Chưa đăng nhập | Yêu cầu người dùng đăng nhập lại |
| "该 Customer ID 已被其他账户绑定" | ID đã được sử dụng | Thông báo người dùng liên kết ID khác |
| "您已绑定该平台的账户" | Đã liên kết platform này | Mỗi platform chỉ được liên kết 1 lần |
| "不支持的平台类型" | Platform không hợp lệ | Chỉ dùng FACEBOOK hoặc INSTAGRAM |
| "Bot 服务连接失败" | Lỗi kết nối Bot API | Kiểm tra Bot API server |

---

## 🔧 Tích hợp Bot (Ví dụ)

### Code mẫu Node.js

```javascript
const axios = require('axios');

async function handleLinkAccount(userId, customerId) {
  try {
    // Gọi API backend
    const response = await axios.post(
      'http://your-backend.com/api/v1/account/bind',
      {
        wxapp_id: '10001',
        token: userId,
        customer_id: customerId,
        platform_type: 'FACEBOOK'
      }
    );
    
    const result = response.data;
    
    if (result.code === 1) {
      // Thành công
      const anonymousName = result.data.customer_name_anonymized;
      
      // Gửi tin nhắn xác nhận
      await sendTextMessage(userId, 
        `✅ Liên kết thành công!\n\n` +
        `Tài khoản đã liên kết：${anonymousName}\n\n` +
        `Bây giờ bạn có thể:\n` +
        `• Xem đơn hàng\n` +
        `• Theo dõi vận chuyển\n` +
        `• Nhận thông báo\n\n` +
        `Nhập "help" để xem thêm chức năng.`
      );
    } else {
      // Thất bại
      await sendTextMessage(userId, 
        `❌ Liên kết thất bại\n\n` +
        `Lý do: ${result.msg}\n\n` +
        `Vui lòng kiểm tra Customer ID và thử lại.`
      );
    }
  } catch (error) {
    console.error('Lỗi:', error);
    await sendTextMessage(userId, 
      `❌ Có lỗi xảy ra\n\n` +
      `Vui lòng thử lại sau ít phút.`
    );
  }
}

// Xử lý khi user nhập Customer ID
bot.on('message', async (message) => {
  const text = message.text.trim();
  
  // Kiểm tra có phải Customer ID không (ví dụ: bắt đầu bằng CUST_)
  if (text.startsWith('CUST_')) {
    await handleLinkAccount(message.sender.id, text);
  }
});
```

---

## 📊 Database Schema

### Bảng `yoshop_platform_account`

```sql
CREATE TABLE `yoshop_platform_account` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `platform_type` varchar(20) NOT NULL DEFAULT 'FACEBOOK',
  `customer_id` varchar(100) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `is_anonymized` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `binding_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_verify_time` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT '10001',
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_customer_id` (`customer_id`,`wxapp_id`),
  UNIQUE KEY `uk_user_platform` (`user_id`,`platform_type`,`wxapp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🎨 Quy tắc ẩn danh

### Hiển thị tên khách hàng

| Tên thật | Hiển thị |
|----------|----------|
| "John Doe" | "J***" |
| "李明" | "李***" |
| "Nguyễn Văn A" | "N***" |
| "A" | "A***" |
| "" (trống) | "***" |

### Code PHP
```php
public static function anonymizeName($name)
{
    if (empty($name)) {
        return '***';
    }
    
    $length = mb_strlen($name, 'UTF-8');
    if ($length <= 2) {
        return mb_substr($name, 0, 1, 'UTF-8') . '***';
    }
    
    return mb_substr($name, 0, 1, 'UTF-8') . '***';
}
```

---

## 🧪 Testing

### Chạy test script

```bash
# Cấu hình
vi test_account_bind.php

# Sửa các thông số:
$BASE_URL = 'http://localhost/web/index.php';
$WXAPP_ID = '10001';
$TOKEN = 'YOUR_TEST_TOKEN';

# Chạy test
php test_account_bind.php
```

### Kết quả test mong đợi

```
========================================
账户绑定 API 测试
========================================

Test 1: 绑定有效的 Customer ID
----------------------------------------
HTTP Code: 200
Response: {
    "code": 1,
    "msg": "绑定成功！已关联账户：J***",
    "data": {...}
}

Test 2: Customer ID 为空
----------------------------------------
HTTP Code: 200
Response: {
    "code": 0,
    "msg": "请输入 Customer ID"
}

...
```

---

## 🚀 Deploy

### Bước 1: Tạo bảng database
```bash
mysql -u username -p database_name < bot/database/migrations/001_create_platform_account.sql
```

### Bước 2: Cấu hình Bot API URL
Sửa file `source/application/api/service/bot/CustomerVerify.php`:
```php
const BOT_API_BASE_URL = 'http://your-bot-server.com/api/bot';
```

### Bước 3: Clear cache
```bash
php clear_cache.php
```

### Bước 4: Test
```bash
php test_account_bind.php
```

---

## 📱 Flow Chart

```
┌─────────────┐
│   Người dùng│
│  Nhập       │
│ Customer ID │
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│  FB/IG Bot      │
│  Nhận message   │
└──────┬──────────┘
       │
       ▼
┌─────────────────────────────┐
│ POST /api/v1/account/bind   │
│ Headers:                    │
│  - wxapp_id: 10001          │
│  - token: user_token        │
│ Body:                       │
│  - customer_id: CUST_123    │
│  - platform_type: FACEBOOK  │
└──────┬──────────────────────┘
       │
       ▼
┌─────────────────────────────┐
│ Backend Validation          │
│ ✓ Kiểm tra token            │
│ ✓ Kiểm tra customer_id      │
│ ✓ Kiểm tra duplicate        │
└──────┬──────────────────────┘
       │
       ▼
┌─────────────────────────────┐
│ GET /api/bot/customer/verify│
│ (Gọi Bot API để xác thực)   │
└──────┬──────────────────────┘
       │
       ▼
┌─────────────────────────────┐
│ Bot API Response            │
│ {                           │
│   success: true,            │
│   data: {                   │
│     customer_name: "John"   │
│   }                         │
│ }                           │
└──────┬──────────────────────┘
       │
       ▼
┌─────────────────────────────┐
│ Lưu vào DB                  │
│ INSERT INTO                 │
│ yoshop_platform_account     │
└──────┬──────────────────────┘
       │
       ▼
┌─────────────────────────────┐
│ Response cho Bot            │
│ {                           │
│   code: 1,                  │
│   msg: "Liên kết thành      │
│         công！Đã关联 tài     │
│         khoản：J***",        │
│   data: {...}               │
│ }                           │
└──────┬──────────────────────┘
       │
       ▼
┌─────────────────────────────┐
│ Bot gửi tin nhắn xác nhận   │
│ "✅ Liên kết thành công!    │
│  Tài khoản đã liên kết: J***│
│  ..."                       │
└─────────────────────────────┘
```

---

## 🔐 Bảo mật

### Token authentication
- Tất cả API đều cần token
- Token có thời hạn 30 ngày

### Data isolation
- Mỗi wxapp_id có dữ liệu riêng
- Không thể truy cập chéo giữa các merchant

### Prevent duplicate binding
- Unique index trong DB
- Business logic check trước khi lưu

### Logging
- Tất cả request đều được log
- Dễ dàng debug và audit

---

## 📞 Troubleshooting

### Q: Bot API connection failed?
**A:** Kiểm tra:
- Bot API URL có đúng không
- Firewall có block không
- Bot service có đang chạy không

### Q: Customer ID validation fails?
**A:** Kiểm tra:
- Customer ID có tồn tại trong Bot system
- Bot API response format có đúng

### Q: Anonymous name hiển thị sai?
**A:** Kiểm tra:
- Encoding UTF-8
- Hàm `mb_strlen()` và `mb_substr()`

---

## 📋 Checklist triển khai

- [ ] Chạy migration SQL
- [ ] Cấu hình Bot API URL
- [ ] Test API với Postman
- [ ] Test tích hợp với Bot
- [ ] Test các trường hợp lỗi
- [ ] Review logs
- [ ] Deploy production

---

**Version**: v1.0  
**Last Updated**: 2026-03-20  
**Contact**: Development Team
