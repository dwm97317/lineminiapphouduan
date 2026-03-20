# Quick Start: Meta API Integration

Hướng dẫn nhanh để bắt đầu với Facebook Messenger và Instagram integration.

## 1️⃣ Chuẩn bị

### Cài đặt dependencies

```bash
pip install -r requirements.txt
```

### Cấu hình environment

```bash
cp .env.example .env
```

Chỉnh sửa `.env` với Meta API credentials:

```env
FACEBOOK_APP_SECRET=your_app_secret
FACEBOOK_VERIFY_TOKEN=verify_token_123
FACEBOOK_PAGE_ACCESS_TOKEN=page_token_here
INSTAGRAM_VERIFY_TOKEN=verify_token_123
INSTAGRAM_PAGE_ACCESS_TOKEN=instagram_token_here
```

## 2️⃣ Khởi động API

```bash
uvicorn main:app --reload
```

API sẽ chạy tại `http://localhost:8000`

## 3️⃣ Cấu hình Webhook (Local Testing)

### Sử dụng Ngrok

```bash
# Terminal 1: Chạy ngrok
ngrok http 8000

# Copy URL: https://your-ngrok-url.ngrok.io
```

### Cấu hình trong Meta Dashboard

1. **Facebook Messenger**
   - Vào Messenger → Settings
   - Webhook URL: `https://your-ngrok-url.ngrok.io/webhook/facebook`
   - Verify Token: `verify_token_123`
   - Subscribe: `messages`, `messaging_postbacks`

2. **Instagram**
   - Vào Instagram → Settings
   - Webhook URL: `https://your-ngrok-url.ngrok.io/webhook/instagram`
   - Verify Token: `verify_token_123`
   - Subscribe: `messages`, `messaging_postbacks`

## 4️⃣ Test Webhook

```bash
# Test webhook verification
python test_webhook.py

# Test Messenger API
python test_messenger_api.py

# Test Instagram API
python test_instagram_api.py
```

## 5️⃣ Gửi tin nhắn

### Gửi tin nhắn Facebook

```python
import asyncio
from services.messenger_api import MessengerAPI, QuickReply

async def send_message():
    api = MessengerAPI("your_page_access_token")
    
    # Text message
    await api.send_text_message(
        recipient_id="user_123",
        text="Hello!"
    )
    
    # Quick reply
    quick_replies = [
        QuickReply("Yes", "YES"),
        QuickReply("No", "NO"),
    ]
    await api.send_quick_reply(
        recipient_id="user_123",
        text="Do you like this?",
        quick_replies=quick_replies
    )
    
    await api.close()

asyncio.run(send_message())
```

### Gửi tin nhắn Instagram

```python
import asyncio
from services.instagram_api import InstagramAPI

async def send_message():
    api = InstagramAPI("your_instagram_access_token")
    
    # Text message
    await api.send_text_message(
        recipient_id="user_456",
        text="Hello from Instagram!"
    )
    
    # Image message
    await api.send_image_message(
        recipient_id="user_456",
        image_url="https://example.com/image.jpg",
        caption="Check this out!"
    )
    
    await api.close()

asyncio.run(send_message())
```

## 6️⃣ Xử lý Webhook Events

Webhook events được xử lý tự động trong `routes/webhook.py`:

```python
# Webhook events được parse thành:
# - MessageEvent: tin nhắn từ user
# - PostbackEvent: postback từ button

# Xử lý bất đồng bộ:
async def process_facebook_event(event):
    if isinstance(event, MessageEvent):
        print(f"Message from {event.sender_id}: {event.text}")
        # TODO: Xử lý tin nhắn
```

## 7️⃣ API Documentation

Truy cập Swagger UI:

```
http://localhost:8000/docs
```

## 📋 Checklist

- [ ] Cài đặt dependencies
- [ ] Cấu hình .env
- [ ] Khởi động API
- [ ] Cấu hình Ngrok
- [ ] Cấu hình webhook trong Meta Dashboard
- [ ] Test webhook verification
- [ ] Test gửi tin nhắn
- [ ] Kiểm tra webhook events

## 🆘 Troubleshooting

### Webhook verification failed

- Kiểm tra verify token trong .env
- Kiểm tra URL webhook trong Meta Dashboard
- Kiểm tra Ngrok URL

### Message send failed

- Kiểm tra access token
- Kiểm tra user ID
- Kiểm tra page/account permissions

### Signature verification failed

- Kiểm tra app secret
- Kiểm tra request body không bị modified

## 📚 Tài liệu

- [META_API_INTEGRATION.md](./META_API_INTEGRATION.md) - Chi tiết đầy đủ
- [Facebook Messenger Docs](https://developers.facebook.com/docs/messenger-platform)
- [Instagram Graph API Docs](https://developers.facebook.com/docs/instagram-api)

## ✅ Tiêu chí nghiệm thử

- [x] FB Webhook xác thực thành công
- [x] IG Webhook xác thực thành công
- [x] Nhận và xử lý tin nhắn bất đồng bộ
- [x] Gửi tin nhắn văn bản thành công
- [x] Gửi nút Quick Reply thành công
