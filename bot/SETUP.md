# Bot API Setup Guide

## Yêu cầu

- Docker & Docker Compose
- Python 3.11+ (nếu chạy locally)
- MySQL 8.0+
- Redis 7+

## Cài đặt nhanh (Docker)

### 1. Chuẩn bị

```bash
cd bot
cp .env.example .env
```

### 2. Khởi động services

```bash
docker-compose up -d
```

Lệnh này sẽ:
- Tạo MySQL database với tất cả migrations
- Khởi động Redis server
- Chạy FastAPI application

### 3. Kiểm tra trạng thái

```bash
# Kiểm tra containers
docker-compose ps

# Xem logs
docker-compose logs -f api
```

### 4. Test endpoints

```bash
# Health check
curl http://localhost:8000/health

# Response mong đợi
{"status":"ok"}
```

## Cài đặt Local (không Docker)

### 1. Tạo virtual environment

```bash
python -m venv venv
source venv/bin/activate  # Linux/Mac
# hoặc
venv\Scripts\activate  # Windows
```

### 2. Cài đặt dependencies

```bash
pip install -r requirements.txt
```

### 3. Cấu hình environment

```bash
cp .env.example .env
# Chỉnh sửa .env với thông tin MySQL/Redis của bạn
```

### 4. Chạy migrations

```bash
# Đảm bảo MySQL đang chạy
mysql -h localhost -u root -p < database/migrations/000_init.sql
mysql -h localhost -u root -p < database/migrations/001_create_platform_account.sql
mysql -h localhost -u root -p < database/migrations/002_create_order_session.sql
mysql -h localhost -u root -p < database/migrations/003_create_order_package.sql
mysql -h localhost -u root -p < database/migrations/004_create_order_message.sql
```

### 5. Khởi động API

```bash
uvicorn main:app --reload
```

API sẽ chạy tại `http://localhost:8000`

## Kiểm tra Database

```bash
# Kết nối MySQL
mysql -h localhost -u bot_user -p bot_db

# Liệt kê bảng
SHOW TABLES;

# Kiểm tra cấu trúc
DESCRIBE platform_account;
DESCRIBE order_session;
DESCRIBE order_package;
DESCRIBE order_message;
```

## Kiểm tra Redis

```bash
# Kết nối Redis
redis-cli

# Test connection
ping
# Response: PONG
```

## Troubleshooting

### MySQL connection failed
- Kiểm tra MySQL đang chạy: `docker-compose ps`
- Kiểm tra credentials trong .env
- Xem logs: `docker-compose logs mysql`

### Redis connection failed
- Kiểm tra Redis đang chạy: `docker-compose ps`
- Xem logs: `docker-compose logs redis`

### Port already in use
```bash
# Thay đổi port trong docker-compose.yml
# hoặc kill process đang sử dụng port
```

## Dừng services

```bash
docker-compose down
```

## Xóa tất cả data (reset)

```bash
docker-compose down -v
```

## Tiếp theo

- Xem API documentation: `http://localhost:8000/docs`
- Xem ReDoc: `http://localhost:8000/redoc`
