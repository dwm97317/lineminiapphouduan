# Bot Commands Reference - Multi-Account Management

## 📋 Available Commands

### 1. 「Xem tài khoản đã liên kết」- View Linked Accounts

**Command**: User sends "Xem tài khoản đã liên kết" or "查看已关联账户"

**Bot Action**: Call `GET /api/bot/account/list-linked`

**Request**:
```http
GET /api/bot/account/list-linked?customer_id=CUST_123&wxapp_id=10001
Headers: X-Bot-API-Key: your_api_key
```

**Response Example**:
```json
{
  "code": 1,
  "msg": "找到 2 个关联账户",
  "data": {
    "list": [
      {
        "id": 1,
        "platform_type": "FACEBOOK",
        "user_info": {
          "nickname": "N***",
          "avatar": "https://...",
          "mobile_masked": "123****4567"
        },
        "binding_time": "2026-03-20 10:30:00"
      }
    ],
    "total": 2,
    "max_allowed": 10,
    "remaining": 8
  }
}
```

**Bot Reply**:
```
📋 Tài khoản đã liên kết (2/10):

1. Facebook - N***
   📱 SĐT: 123****4567
   ⏰ Liên kết: 20/03/2026 10:30

2. Instagram - J***
   📱 SĐT: 987****6543
   ⏰ Liên kết: 20/03/2026 11:00

Bạn còn có thể liên kết thêm 8 tài khoản.
```

---

### 2. 「Bổ sung mã bưu kiện」- Add Package Code

**Command**: User sends "Bổ sung mã bưu kiện" or "补充包裹代码"

**Bot Action**: Show list of pending packages waiting to be added

**API**: `GET /api/bot/package/waiting-list`

**Request**:
```http
GET /api/bot/package/waiting-list?customer_id=CUST_123&wxapp_id=10001
Headers: X-Bot-API-Key: your_api_key
```

**Response Example**:
```json
{
  "code": 1,
  "msg": "找到 3 个待入库包裹",
  "data": {
    "list": [
      {
        "package_id": 123,
        "package_code": "PKG001",
        "weight": 1.5,
        "volume": 0.02,
        "created_time": "2026-03-20 10:00:00",
        "remark": "Fragile items"
      }
    ],
    "total": 3
  }
}
```

**Bot Reply**:
```
📦 Đơn chờ nhập kho (3 đơn):

1. PKG001
   ⚖️ Cân nặng: 1.5kg
   📐 Thể tích: 0.02m³
   📝 Ghi chú: Hàng dễ vỡ
   ⏰ Tạo lúc: 20/03/2026 10:00

2. PKG002
   ⚖️ Cân nặng: 2.3kg
   ⏰ Tạo lúc: 20/03/2026 11:00

3. PKG003
   ⚖️ Cân nặng: 0.8kg
   ⏰ Tạo lúc: 20/03/2026 12:00

Nhập mã bưu kiện để bổ sung vào hệ thống.
```

---

### 3. 「Đơn hàng của tôi」- My Orders

**Command**: User sends "Đơn hàng của tôi" or "我的订单"

**Bot Action**: Show order history

**API**: `GET /api/bot/order/history`

**Request**:
```http
GET /api/bot/order/history?customer_id=CUST_123&limit=20&wxapp_id=10001
Headers: X-Bot-API-Key: your_api_key
```

**Response Example**:
```json
{
  "code": 1,
  "msg": "找到 5 个订单",
  "data": {
    "list": [
      {
        "order_id": 456,
        "order_sn": "OR202603200001",
        "order_status": 3,
        "order_status_text": "待收货",
        "express_num": "SF1234567890",
        "payment": 150.50,
        "created_time": "2026-03-15 10:00:00",
        "shipping_time": "2026-03-16 14:00:00"
      }
    ],
    "total": 5,
    "limit": 20
  }
}
```

**Bot Reply**:
```
📦 Đơn hàng của bạn (5 đơn):

1. OR202603200001 - Đang giao hàng
   🚚 Vận đơn: SF1234567890
   💰 Thanh toán: 150.50 ₫
   📅 Đặt hàng: 15/03/2026
   🚀 Giao hàng: 16/03/2026

2. OR202603150002 - Đã hoàn thành
   🚚 Vận đơn: VN12345678
   💰 Thanh toán: 280.00 ₫
   📅 Đặt hàng: 10/03/2026

Nhập "chi tiết <mã đơn>" để xem thông tin chi tiết.
```

---

## 🔧 Implementation Flow

### Bot Message Handler (Node.js Example)

```javascript
const axios = require('axios');

const API_BASE = 'http://your-backend.com';
const API_KEY = 'your_bot_api_key';
const WXAPP_ID = '10001';

bot.on('message', async (message) => {
  const text = message.text.trim().toLowerCase();
  const customerId = message.sender.id; // Or from user profile
  
  try {
    if (text.includes('xem tài khoản') || text.includes('查看已关联')) {
      await handleListLinkedAccounts(message, customerId);
    } 
    else if (text.includes('bổ sung') || text.includes('补充包裹')) {
      await handleWaitingPackages(message, customerId);
    }
    else if (text.includes('đơn hàng') || text.includes('订单')) {
      await handleOrderHistory(message, customerId);
    }
  } catch (error) {
    console.error('Error:', error);
    await bot.sendMessage(message.sender.id, '❌ Có lỗi xảy ra. Vui lòng thử lại sau.');
  }
});

async function handleListLinkedAccounts(message, customerId) {
  const response = await axios.get(`${API_BASE}/api/bot/account/list-linked`, {
    params: {
      customer_id: customerId,
      wxapp_id: WXAPP_ID
    },
    headers: { 'X-Bot-API-Key': API_KEY }
  });
  
  const data = response.data.data;
  
  if (data.list.length === 0) {
    await bot.sendMessage(message.sender.id, '📋 Chưa có tài khoản nào liên kết.\n\nNhập Customer ID để liên kết ngay!');
    return;
  }
  
  let reply = `📋 Tài khoản đã liên kết (${data.total}/${data.max_allowed}):\n\n`;
  
  data.list.forEach((acc, index) => {
    reply += `${index + 1}. ${acc.platform_name} - ${acc.user_info.nickname}\n`;
    if (acc.user_info.mobile_masked) {
      reply += `   📱 SĐT: ${acc.user_info.mobile_masked}\n`;
    }
    reply += `   ⏰ Liên kết: ${formatDate(acc.binding_time)}\n\n`;
  });
  
  if (data.remaining > 0) {
    reply += `\nBạn còn có thể liên kết thêm ${data.remaining} tài khoản.`;
  }
  
  await bot.sendMessage(message.sender.id, reply);
}

async function handleWaitingPackages(message, customerId) {
  const response = await axios.get(`${API_BASE}/api/bot/package/waiting-list`, {
    params: {
      customer_id: customerId,
      wxapp_id: WXAPP_ID
    },
    headers: { 'X-Bot-API-Key': API_KEY }
  });
  
  const data = response.data.data;
  
  if (data.list.length === 0) {
    await bot.sendMessage(message.sender.id, '📦 Không có đơn chờ nào.\n\nTất cả包裹 đã được nhập kho!');
    return;
  }
  
  let reply = `📦 Đơn chờ nhập kho (${data.total} đơn):\n\n`;
  
  data.list.forEach((pkg, index) => {
    reply += `${index + 1}. ${pkg.package_code}\n`;
    reply += `   ⚖️ Cân nặng: ${pkg.weight}kg\n`;
    if (pkg.volume) {
      reply += `   📐 Thể tích: ${pkg.volume}m³\n`;
    }
    if (pkg.remark) {
      reply += `   📝 Ghi chú: ${pkg.remark}\n`;
    }
    reply += `   ⏰ Tạo lúc: ${formatDate(pkg.created_time)}\n\n`;
  });
  
  reply += '\nNhập mã bưu kiện để bổ sung vào hệ thống.';
  
  await bot.sendMessage(message.sender.id, reply);
}

async function handleOrderHistory(message, customerId) {
  const response = await axios.get(`${API_BASE}/api/bot/order/history`, {
    params: {
      customer_id: customerId,
      limit: 20,
      wxapp_id: WXAPP_ID
    },
    headers: { 'X-Bot-API-Key': API_KEY }
  });
  
  const data = response.data.data;
  
  if (data.list.length === 0) {
    await bot.sendMessage(message.sender.id, '📦 Chưa có đơn hàng nào.\n\nMua sắm ngay để xem lịch sử đơn hàng!');
    return;
  }
  
  let reply = `📦 Đơn hàng của bạn (${data.total} đơn):\n\n`;
  
  data.list.forEach((order, index) => {
    reply += `${index + 1}. ${order.order_sn} - ${order.order_status_text}\n`;
    if (order.express_num) {
      reply += `   🚚 Vận đơn: ${order.express_num}\n`;
    }
    reply += `   💰 Thanh toán: ${formatMoney(order.payment)} ₫\n`;
    reply += `   📅 Đặt hàng: ${formatDate(order.created_time)}\n\n`;
  });
  
  reply += '\nNhập "chi tiết <mã đơn>" để xem thông tin chi tiết.';
  
  await bot.sendMessage(message.sender.id, reply);
}

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function formatMoney(amount) {
  return parseFloat(amount).toLocaleString('vi-VN', { minimumFractionDigits: 2 });
}
```

---

## ✅ Acceptance Criteria Checklist

- [x] **Một Customer ID có thể liên kết nhiều tài khoản**
  - Database constraint changed from UNIQUE to INDEX
  - Multiple users can bind same Customer ID
  - Maximum 10 accounts per Customer ID

- [x] **Vượt quá giới hạn → thông báo liên hệ hỗ trợ**
  - `PlatformAccount::isBindingLimitReached()` checks limit
  - Returns error with `need_support: true` flag
  - Clear message to contact support

- [x] **Danh sách tài khoản đã liên kết hiển thị đúng**
  - `GET /api/v1/account/list` endpoint
  - `GET /api/bot/account/list-linked` for bot
  - Shows all linked accounts with anonymized names

- [x] **Kích hoạt bằng từ khóa → danh sách đơn chờ bưu kiện hoạt động**
  - Bot command: "Bổ sung mã bưu kiện"
  - Calls `GET /api/bot/package/waiting-list`
  - Returns pending packages for all linked users

- [x] **Lịch sử đơn hàng hiển thị đúng**
  - Bot command: "Đơn hàng của tôi"
  - Calls `GET /api/bot/order/history`
  - Returns order history for all linked users

---

## 🎯 API Endpoints Summary

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/v1/account/bind` | POST | Bind Customer ID to user account |
| `/api/v1/account/list` | GET | List user's linked accounts |
| `/api/v1/account/unbind` | POST | Unlink account (with confirmation) |
| `/api/bot/account/list-linked` | GET | Bot: View all linked accounts for Customer ID |
| `/api/bot/package/waiting-list` | GET | Bot: View pending packages |
| `/api/bot/order/history` | GET | Bot: View order history |

---

## 🔐 Security Notes

1. **Confirmation Required**: Unbinding requires `confirm_code` parameter
2. **Authorization**: All endpoints check user ownership
3. **Multi-Tenant**: wxapp_id isolation enforced
4. **API Key**: Bot endpoints require valid API key
5. **Rate Limiting**: Consider implementing rate limiting for bot commands

---

**Version**: v1.0  
**Last Updated**: 2026-03-20  
**Feature**: Multi-Account Management Support
