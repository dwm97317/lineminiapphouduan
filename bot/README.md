# Bot API

FastAPI-based bot service với MySQL database và Redis cache.

## 📋 Tính năng

- ✅ FastAPI framework
- ✅ CORS middleware
- ✅ MySQL database với migrations
- ✅ Redis cache
- ✅ Docker & Docker Compose
- ✅ Health check endpoint
- ✅ Environment configuration

## 🚀 Khởi động nhanh

### Với Docker (Khuyến nghị)

```bash
cd bot
docker-compose up -d
```

### Kiểm tra

```bash
curl http://localhost:8000/health
# Response: {"status":"ok"}
```

## 📁 Cấu trúc thư mục

```
bot/
├── main.py                      # FastAPI application
├── config.py                    # Configuration settings
├── requirements.txt             # Python dependencies
├── Dockerfile                   # Docker image
├── docker-compose.yml           # Docker Compose config
├── .env.example                 # Environment template
├── .gitignore                   # Git ignore rules
├── SETUP.md                     # Setup guide
├── README.md                    # This file
├── META_API_INTEGRATION.md      # Meta API guide
├── QUICKSTART_META_API.md       # Quick start guide
├── test_setup.py                # Setup verification script
├── test_webhook.py              # Webhook tests
├── test_messenger_api.py        # Messenger API tests
├── test_instagram_api.py        # Instagram API tests
├── verify_setup.sh              # Shell verification script
├── database/
│   ├── README.md                # Database guide
│   └── migrations/
│       ├── 000_init.sql
│       ├── 001_create_platform_account.sql
│       ├── 002_create_order_session.sql
│       ├── 003_create_order_package.sql
│       └── 004_create_order_message.sql
├── services/
│   ├── __init__.py
│   ├── messenger_api.py         # Facebook Messenger API
│   ├── instagram_api.py         # Instagram Graph API
│   ├── webhook_validator.py     # Signature verification
│   └── webhook_handler.py       # Event parsing
├── routes/
│   ├── __init__.py
│   └── webhook.py               # Webhook endpoints
└── models/
    ├── __init__.py
    └── message.py               # Message models
```

## 🗄️ Database Schema

### platform_account
- Platform account information (LINE, Facebook, etc)
- Stores access tokens and account details

### order_session
- User conversation sessions
- Tracks session state and activity

### order_package
- Order packages/items
- Links to order sessions

### order_message
- Messages in conversations
- Stores user and bot messages

## 🔗 Meta API Integration

### Facebook Messenger
- Webhook verification
- Receive messages
- Send text messages
- Send quick reply buttons
- Typing indicator
- Get user profile

### Instagram
- Webhook verification
- Receive messages
- Send text messages
- Send image messages
- Mark messages as seen
- Get user profile

**Guides:**
- [META_API_INTEGRATION.md](./META_API_INTEGRATION.md) - Chi tiết đầy đủ
- [QUICKSTART_META_API.md](./QUICKSTART_META_API.md) - Bắt đầu nhanh

## 🔧 Cấu hình

Tạo file `.env` từ `.env.example`:

```bash
cp .env.example .env
```

Chỉnh sửa các giá trị cần thiết:

```env
# FastAPI
API_HOST=0.0.0.0
API_PORT=8000

# Database
DB_HOST=localhost
DB_PORT=3306
DB_USER=bot_user
DB_PASSWORD=bot_password
DB_NAME=bot_db

# Redis
REDIS_HOST=localhost
REDIS_PORT=6379
```

## 📚 API Documentation

Sau khi khởi động, truy cập:

- **Swagger UI**: http://localhost:8000/docs
- **ReDoc**: http://localhost:8000/redoc

## ✅ Tiêu chí nghiệm thử

- [x] FastAPI service khởi động bình thường
- [x] Tất cả bảng database được tạo thành công
- [x] Kết nối Redis hoạt động
- [x] Docker Compose chạy được
- [x] Endpoint /health trả về {"status": "ok"}

## 🧪 Kiểm tra Setup

### Python script

```bash
python test_setup.py
```

### Shell script

```bash
bash verify_setup.sh
```

## 📖 Hướng dẫn chi tiết

- [Setup Guide](./SETUP.md) - Hướng dẫn cài đặt chi tiết
- [Database Guide](./database/README.md) - Hướng dẫn database migrations

## 🛑 Dừng services

```bash
docker-compose down
```

## 🔄 Reset (xóa tất cả data)

```bash
docker-compose down -v
```

## 📝 License

MIT
