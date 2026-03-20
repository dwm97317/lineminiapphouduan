# Database Migrations

Hướng dẫn chạy migration cho bot database.

## Cấu trúc Migration

```
migrations/
├── 000_init.sql                    # Khởi tạo database
├── 001_create_platform_account.sql # Bảng platform_account
├── 002_create_order_session.sql    # Bảng order_session
├── 003_create_order_package.sql    # Bảng order_package
└── 004_create_order_message.sql    # Bảng order_message
```

## Chạy Migration

### Cách 1: Sử dụng Docker Compose (Khuyến nghị)

```bash
cd bot
docker-compose up -d
```

Docker sẽ tự động chạy tất cả migration files trong thư mục `migrations/` khi MySQL container khởi động.

### Cách 2: Chạy thủ công với MySQL CLI

```bash
# Kết nối đến MySQL
mysql -h localhost -u root -p

# Chạy từng migration file
source migrations/000_init.sql;
source migrations/001_create_platform_account.sql;
source migrations/002_create_order_session.sql;
source migrations/003_create_order_package.sql;
source migrations/004_create_order_message.sql;
```

### Cách 3: Sử dụng Python script

```bash
python run_migrations.py
```

## Kiểm tra Migration

```bash
# Kết nối đến database
mysql -h localhost -u bot_user -p bot_db

# Liệt kê tất cả bảng
SHOW TABLES;

# Kiểm tra cấu trúc bảng
DESCRIBE platform_account;
DESCRIBE order_session;
DESCRIBE order_package;
DESCRIBE order_message;
```

## Xóa Database (Nếu cần reset)

```bash
docker-compose down -v
```

Lệnh này sẽ xóa tất cả containers và volumes.
