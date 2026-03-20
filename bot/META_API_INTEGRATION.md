# Meta API Integration Guide

Hướng dẫn tích hợp Facebook Messenger và Instagram Graph API.

## 📋 Tính năng

### Facebook Messenger
- ✅ Webhook verification
- ✅ Receive messages
- ✅ Send text messages
- ✅ Send quick reply buttons
- ✅ Typing indicator
- ✅ Get user profile

### Instagram
- ✅ Webhook verification
- ✅ Receive messages
- ✅ Send text messages
- ✅ Send image messages
- ✅ Mark messages as seen
- ✅ Get user profile

## 🔧 Cấu hình

### 1. Tạo Facebook App

1. Truy cập [Facebook Developers](https://developers.facebook.com/)
2. Tạo app mới hoặc sử dụng app hiện tại
3. Thêm sản phẩm "Messenger"
4. Thêm sản phẩm "Instagram"

### 2. Lấy Credentials

#### Facebook App Secret
- Vào Settings → Basic
- Copy "App Secret"

#### Facebook Page Access Token
- Vào Messenger → Settings
- Tạo Page Access Token cho page của bạn

#### Verify Token
- Tạo token ngẫu nhiên (ví dụ: `my_voice_is_my_password_verify_me`)

### 3. Cấu hình Environment

Cập nhật `.env`:

```env
# Facebook Configuration
FACEBOOK_APP_SECRET=your_app_secret_here
FACEBOOK_VERIFY_TOKEN=my_voice_is_my_password_verify_me
FACEBOOK_PAGE_ACCESS_TOKEN=your_page_access_token_here

# Instagram Configuration
INSTAGRAM_VERIFY_TOKEN=my_voice_is_my_password_verify_me
INSTAGRAM_PAGE_ACCESS_TOKEN=your_instagram_access_token_here
```

### 4. Cấu hình Webhook

#### Facebook Messenger Webhook

1. Vào Messenger → Settings
2. Webhook URL: `https://your-domain.com/webhook/facebook`
3. Verify Token: `my_voice_is_my_password_verify_me`
4. Subscribe to events:
   - `messages`
   - `messaging_postbacks`
   - `messaging_quick_replies`

#### Instagram Webhook

1. Vào Instagram → Settings
2. Webhook URL: `https://your-domain.com/webhook/instagram`
3. Verify Token: `my_voice_is_my_password_verify_me`
4. Subscribe to events:
   - `messages`
   - `messaging_postbacks`

## 📚 API Usage

### Messenger API

#### Send Text Message

```python
from services.messenger_api import MessengerAPI

api = MessengerAPI(page_access_token)
result = await api.send_text_message(
    recipient_id="user_123",
    text="Hello!"
)
```

#### Send Quick Reply

```python
from services.messenger_api import MessengerAPI, QuickReply

api = MessengerAPI(page_access_token)
quick_replies = [
    QuickReply("Yes", "PAYLOAD_YES"),
    QuickReply("No", "PAYLOAD_NO"),
]
result = await api.send_quick_reply(
    recipient_id="user_123",
    text="Do you like this?",
    quick_replies=quick_replies
)
```

#### Send Typing Indicator

```python
result = await api.send_typing_indicator(recipient_id="user_123")
```

#### Get User Profile

```python
profile = await api.get_user_profile(user_id="user_123")
# Returns: {
#   "first_name": "John",
#   "last_name": "Doe",
#   "profile_pic_url": "...",
#   "locale": "en_US",
#   "timezone": -5
# }
```

### Instagram API

#### Send Text Message

```python
from services.instagram_api import InstagramAPI

api = InstagramAPI(page_access_token)
result = await api.send_text_message(
    recipient_id="user_456",
    text="Hello from Instagram!"
)
```

#### Send Image Message

```python
result = await api.send_image_message(
    recipient_id="user_456",
    image_url="https://example.com/image.jpg",
    caption="Check this out!"
)
```

#### Get User Profile

```python
profile = await api.get_user_profile(user_id="user_456")
# Returns: {
#   "name": "John Doe",
#   "profile_pic_url": "...",
#   "username": "johndoe"
# }
```

## 🔐 Webhook Signature Verification

Tất cả webhook requests được xác thực bằng chữ ký HMAC-SHA1:

```python
from services.webhook_validator import WebhookValidator

is_valid = WebhookValidator.verify_facebook_signature(
    body=request_body,
    signature=request.headers.get("X-Hub-Signature"),
    app_secret=app_secret
)
```

## 📨 Webhook Events

### Message Event

```python
from services.webhook_handler import MessageEvent

event = MessageEvent(event_data)
print(event.sender_id)           # User ID
print(event.text)                # Message text
print(event.message_type)        # MessageType enum
print(event.quick_reply_payload) # Quick reply payload
print(event.attachments)         # Attachments list
```

### Postback Event

```python
from services.webhook_handler import PostbackEvent

event = PostbackEvent(event_data)
print(event.sender_id)  # User ID
print(event.payload)    # Postback payload
print(event.title)      # Button title
```

## 🧪 Testing

### Test Webhook Verification

```bash
python test_webhook.py
```

### Test Messenger API

```bash
python test_messenger_api.py
```

### Test Instagram API

```bash
python test_instagram_api.py
```

## 📝 Webhook Endpoints

### Facebook Messenger

**GET** `/webhook/facebook`
- Verify webhook subscription
- Query params: `hub.mode`, `hub.challenge`, `hub.verify_token`

**POST** `/webhook/facebook`
- Receive webhook events
- Header: `X-Hub-Signature` (HMAC-SHA1 signature)
- Body: JSON webhook payload

### Instagram

**GET** `/webhook/instagram`
- Verify webhook subscription
- Query params: `hub.mode`, `hub.challenge`, `hub.verify_token`

**POST** `/webhook/instagram`
- Receive webhook events
- Header: `X-Hub-Signature` (HMAC-SHA1 signature)
- Body: JSON webhook payload

## 🔄 Asynchronous Message Processing

Messages được xử lý bất đồng bộ sử dụng FastAPI BackgroundTasks:

```python
@app.post("/webhook/facebook")
async def handle_facebook_webhook(
    request: Request,
    background_tasks: BackgroundTasks
):
    # Verify signature
    # Parse events
    # Add to background tasks
    for event in events:
        background_tasks.add_task(process_facebook_event, event)
    
    return {"status": "ok"}
```

## 🚀 Deployment

### Ngrok (Local Testing)

```bash
ngrok http 8000
# Webhook URL: https://your-ngrok-url.ngrok.io/webhook/facebook
```

### Production

1. Deploy FastAPI app
2. Configure HTTPS (required by Meta)
3. Update webhook URLs in Meta dashboard
4. Test webhook delivery

## 📖 References

- [Facebook Messenger Platform](https://developers.facebook.com/docs/messenger-platform)
- [Instagram Graph API](https://developers.facebook.com/docs/instagram-api)
- [Webhook Reference](https://developers.facebook.com/docs/messenger-platform/webhooks)
- [Signature Verification](https://developers.facebook.com/docs/messenger-platform/webhooks#security)

## ✅ Tiêu chí nghiệm thử

- [x] FB Webhook xác thực thành công
- [x] IG Webhook xác thực thành công
- [x] Nhận và xử lý tin nhắn bất đồng bộ
- [x] Gửi tin nhắn văn bản thành công
- [x] Gửi nút Quick Reply thành công
