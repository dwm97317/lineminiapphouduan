# Account Binding Flow - Visual Guide

## 📊 Complete System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER INTERACTION                         │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌──────────────────┐
                    │  FB/IG Messenger │
                    │   (User Input)   │
                    │  "CUST_123456"   │
                    └────────┬─────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                        BOT LAYER                                 │
└─────────────────────────────────────────────────────────────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │   Bot Server     │
                    │ (Node.js/Python) │
                    └────────┬─────────┘
                             │
                             │ HTTP POST
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    BACKEND API LAYER                             │
│              (ThinkPHP 5.0 - This Implementation)                │
└─────────────────────────────────────────────────────────────────┘
                             │
                             ▼
              ┌──────────────────────────────┐
              │  Account Controller          │
              │  /api/v1/account/bind        │
              └──────────────┬───────────────┘
                             │
              ┌──────────────┴───────────────┐
              │                              │
              ▼                              ▼
    ┌──────────────────┐          ┌──────────────────┐
    │ Validation       │          │ Bot API Client   │
    │ • Token check    │          │ • cURL request   │
    │ • Duplicate      │          │ • Timeout: 10s   │
    │ • Platform type  │          │ • Error handle   │
    └──────────────────┘          └────────┬─────────┘
                                           │
                                           │ GET
                                           ▼
                                  ┌──────────────────┐
                                  │   Bot API        │
                                  │ /customer/verify │
                                  └────────┬─────────┘
                                           │
                                           │ Response
                                           ▼
                                  ┌──────────────────┐
                                  │ Customer Data    │
                                  │ • customer_id    │
                                  │ • customer_name  │
                                  │ • is_valid       │
                                  └──────────────────┘
                             │
                             ▼
              ┌──────────────────────────────┐
              │  Service Layer               │
              │  CustomerVerify              │
              │  • Anonymize name            │
              │  • Format response           │
              └──────────────┬───────────────┘
                             │
                             ▼
              ┌──────────────────────────────┐
              │  Model Layer                 │
              │  PlatformAccount             │
              │  • Create binding            │
              │  • Save to DB                │
              └──────────────┬───────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                      DATABASE LAYER                              │
└─────────────────────────────────────────────────────────────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │ yoshop_platform_ │
                    │    account       │
                    │                  │
                    │ • id             │
                    │ • user_id        │
                    │ • customer_id    │
                    │ • platform_type  │
                    │ • customer_name  │
                    │ • binding_time   │
                    │ • status         │
                    └────────┬─────────┘
                             │
                             │ Success
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                      RESPONSE LAYER                              │
└─────────────────────────────────────────────────────────────────┘
                             │
                             ▼
              ┌──────────────────────────────┐
              │  JSON Response               │
              │  {                           │
              │    code: 1,                  │
              │    msg: "绑定成功！          │
              │          已关联账户：J***",  │
              │    data: {                   │
              │      customer_id: "...",     │
              │      customer_name_          │
              │      anonymized: "J***",     │
              │      binding_time: "..."     │
              │    }                         │
              │  }                           │
              └──────────────┬───────────────┘
                             │
                             │ HTTP Response
                             ▼
                    ┌──────────────────┐
                    │   Bot Server     │
                    │  Process result  │
                    └────────┬─────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │ Send Message to  │
                    │    User          │
                    │ "✅ Liên kết     │
                    │  thành công!     │
                    │  Tài khoản đã    │
                    │  liên kết: J***" │
                    └──────────────────┘
```

---

## 🔄 Detailed Binding Flow

### Step-by-Step Process

```
┌────────┐
│ Step 1 │ User opens FB/IG Messenger
└────┬───┘
     │
     ▼
┌────────┐
│ Step 2 │ User types Customer ID: "CUST_123456"
└────┬───┘
     │
     ▼
┌────────┐
│ Step 3 │ Bot receives message
└────┬───┘
     │
     ▼
┌────────┐
│ Step 4 │ Bot validates format (optional)
└────┬───┘
     │
     ▼
┌────────┐
│ Step 5 │ Bot calls Backend API
│        │ POST /api/v1/account/bind
│        │ Body: {
│        │   wxapp_id: "10001",
│        │   token: user_token,
│        │   customer_id: "CUST_123456",
│        │   platform_type: "FACEBOOK"
│        │ }
└────┬───┘
     │
     ▼
┌────────┐
│ Step 6 │ Backend validates token
└────┬───┘
     │
     ▼
┌────────┐
│ Step 7 │ Check if customer_id already bound
│        │ → If YES: Return error
└────┬───┘
     │ NO
     ▼
┌────────┐
│ Step 8 │ Check if user already has binding on this platform
│        │ → If YES: Return error
└────┬───┘
     │ NO
     ▼
┌────────┐
│ Step 9 │ Call Bot API for verification
│        │ GET /api/bot/customer/verify?
│        │   customer_id=CUST_123456&
│        │   platform=facebook
└────┬───┘
     │
     ▼
┌────────┐
│ Step 10│ Bot API validates Customer ID
│        │ → Checks database
│        │ → Returns customer info
└────┬───┘
     │
     ▼
┌────────┐
│ Step 11│ Backend receives Bot API response
│        │ {
│        │   success: true,
│        │   data: {
│        │     customer_id: "CUST_123456",
│        │     customer_name: "John Doe"
│        │   }
│        │ }
└────┬───┘
     │
     ▼
┌────────┐
│ Step 12│ Anonymize customer name
│        │ "John Doe" → "J***"
└────┬───┘
     │
     ▼
┌────────┐
│ Step 13│ Save to database
│        │ INSERT INTO yoshop_platform_account (
│        │   user_id,
│        │   platform_type,
│        │   customer_id,
│        │   customer_name,
│        │   is_anonymized,
│        │   wxapp_id
│        │ ) VALUES (...)
└────┬───┘
     │
     ▼
┌────────┐
│ Step 14│ Return success response
│        │ {
│        │   code: 1,
│        │   msg: "绑定成功！已关联账户：J***",
│        │   data: {
│        │     customer_id: "CUST_123456",
│        │     customer_name_anonymized: "J***",
│        │     binding_time: "2026-03-20 10:30:00"
│        │   }
│        │ }
└────┬───┘
     │
     ▼
┌────────┐
│ Step 15│ Bot receives response
└────┬───┘
     │
     ▼
┌────────┐
│ Step 16│ Bot sends confirmation message to user
│        │ "✅ Liên kết thành công!
│        │  Tài khoản đã liên kết: J***
│        │  Bây giờ bạn có thể sử dụng tất cả chức năng!"
└────┬───┘
     │
     ▼
┌────────┐
│ Step 17│ User sees confirmation and can now use all features
└────────┘
```

---

## ⚠️ Error Flow Scenarios

### Scenario 1: Invalid Customer ID

```
User Input → Bot → Backend API
                     ↓
              Call Bot API /customer/verify
                     ↓
              Bot API returns: success=false
                     ↓
              Backend returns to Bot:
              {
                code: 0,
                msg: "Customer ID không tồn tại"
              }
                     ↓
              Bot informs user
              "❌ Customer ID không hợp lệ"
```

### Scenario 2: Duplicate Binding

```
User Input → Bot → Backend API
                     ↓
              Check if customer_id exists in DB
                     ↓
              Found existing binding!
                     ↓
              Return error immediately
              {
                code: 0,
                msg: "该 Customer ID 已被其他账户绑定"
              }
                     ↓
              Bot informs user
              "❌ Customer ID này đã được liên kết"
```

### Scenario 3: Network Error

```
User Input → Bot → Backend API
                     ↓
              Call Bot API /customer/verify
                     ↓
              Connection timeout (>10s)
                     ↓
              Log error, return:
              {
                code: 0,
                msg: "Bot 服务连接失败，请稍后重试"
              }
                     ↓
              Bot informs user
              "❌ Có lỗi xảy ra, vui lòng thử lại sau"
```

---

## 🗄️ Database Entity Relationship

```
┌──────────────────┐         ┌──────────────────────┐
│   yoshop_user    │         │ yoshop_platform_     │
│                  │         │      account         │
│ ┌──────────────┐ │         │ ┌──────────────────┐ │
│ │ user_id (PK) │◄┼─────────┼─│ user_id (FK)     │ │
│ └──────────────┘ │    1:N  │ └──────────────────┘ │
│ │ username     │ │         │ │ id (PK)          │ │
│ │ mobile       │ │         │ │ customer_id      │ │
│ │ email        │ │         │ │ platform_type    │ │
│ └──────────────┘ │         │ │ customer_name    │ │
└──────────────────┘         │ │ binding_time     │ │
                             │ │ status           │ │
                             │ │ wxapp_id         │ │
                             │ └──────────────────┘ │
                             └──────────────────────┘
                                      │
                                      │ N:1
                                      ▼
                             ┌──────────────────────┐
                             │   Bot Customer DB    │
                             │ ┌──────────────────┐ │
                             │ │ customer_id (PK) │ │
                             │ │ customer_name    │ │
                             │ │ platform         │ │
                             │ │ ...              │ │
                             │ └──────────────────┘ │
                             └──────────────────────┘
```

---

## 🔧 Component Interaction

```
┌──────────────────────────────────────────────────────────────┐
│ Frontend (FB/IG Bot)                                         │
│                                                              │
│ ┌────────────────┐  ┌────────────────┐  ┌────────────────┐ │
│ │ Message Handler│  │ API Client     │  │ Response       │ │
│ │                │  │                │  │ Formatter      │ │
│ │ • Receive text │  │ • Axios/fetch  │  │ • Success msg  │ │
│ │ • Validate fmt │  │ • Error handle │  │ • Error msg    │ │
│ └────────────────┘  └────────────────┘  └────────────────┘ │
└──────────────────────────────────────────────────────────────┘
                            │
                            │ HTTPS
                            ▼
┌──────────────────────────────────────────────────────────────┐
│ Backend API (ThinkPHP)                                       │
│                                                              │
│ ┌────────────────┐  ┌────────────────┐  ┌────────────────┐ │
│ │ Account        │  │ CustomerVerify │  │ PlatformAccount│ │
│ │ Controller     │  │ Service        │  │ Model          │ │
│ │                │  │                │  │                │ │
│ │ • bind()       │  │ • verifyCust() │  │ • getByCustId  │ │
│ │ • bindings()   │  │ • httpGet()    │  │ • isBound()    │ │
│ │ • unbind()     │  │ • anonymize()  │  │ • create()     │ │
│ └────────────────┘  └────────────────┘  └────────────────┘ │
└──────────────────────────────────────────────────────────────┘
                            │
                            │ SQL
                            ▼
┌──────────────────────────────────────────────────────────────┐
│ Database (MySQL)                                             │
│                                                              │
│ ┌──────────────────────────────────────────────────────────┐│
│ │ yoshop_platform_account                                  ││
│ │                                                          ││
│ │ UNIQUE(customer_id, wxapp_id)                           ││
│ │ UNIQUE(user_id, platform_type, wxapp_id)                ││
│ │ INDEX(user_id), INDEX(platform_type)                    ││
│ └──────────────────────────────────────────────────────────┘│
└──────────────────────────────────────────────────────────────┘
```

---

## 📱 User Experience Flow

```
┌─────────────────────────────────────────────────────────┐
│ MESSENGER CHAT INTERFACE                                │
└─────────────────────────────────────────────────────────┘

👤 User: Hi

🤖 Bot: Chào bạn! Tôi có thể giúp gì cho bạn?

👤 User: CUST_123456

⏳ Bot: Đang xử lý...

[Backend processing...]

✅ Bot: Liên kết thành công!

   Tài khoản đã liên kết: J***
   
   Bây giờ bạn có thể:
   ✓ Xem đơn hàng
   ✓ Theo dõi vận chuyển  
   ✓ Nhận thông báo
   
   Nhập "help" để xem thêm chức năng.

👤 User: help

🤖 Bot: Các chức năng khả dụng:
   1. Xem đơn hàng - nhập "orders"
   2. Theo dõi vận chuyển - nhập "tracking"
   3. Liên hệ hỗ trợ - nhập "support"
   ...
```

---

## 🎯 Key Decision Points

```
                    User enters Customer ID
                              │
                              ▼
                     ┌────────────────┐
                     │ Valid format?  │
                     └───────┬────────┘
                             │
              ┌──────────────┴──────────────┐
              │ YES                         │ NO
              ▼                             ▼
     ┌────────────────┐           ┌────────────────┐
     │ Check login    │           │ Ask to login   │
     │ status         │           │ first          │
     └───────┬────────┘           └────────────────┘
             │
    ┌────────┴────────┐
    │ YES             │ NO
    ▼                 ▼
┌──────────┐    ┌──────────┐
│ Check    │    │ Return   │
│duplicate │    │ error    │
└────┬─────┘    └──────────┘
     │
┌────┴─────┐
│ NOT FOUND│ FOUND
▼          ▼
┌──────────────┐  ┌──────────────┐
│ Call Bot API │  │ Return error │
│ /verify      │  │ "Already     │
└────┬─────────┘  │ bound"       │
     │            └──────────────┘
     │
┌────┴─────┐
│ SUCCESS  │ FAIL
▼          ▼
┌──────────────┐  ┌──────────────┐
│ Anonymize    │  │ Return error │
│ name         │  │ from Bot API │
└────┬─────────┘  └──────────────┘
     │
     ▼
┌──────────────┐
│ Save to DB   │
└────┬─────────┘
     │
     ▼
┌──────────────┐
│ Return       │
│ success +    │
│ anon name    │
└──────────────┘
```

---

**This visual guide provides a complete overview of the account binding system!**
