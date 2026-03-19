# Bot Database - Hướng dẫn cài đặt

## Cấu trúc thư mục

```
bot/database/
└── migrations/
    ├── 000_init.sql                  # Khởi tạo database
    ├── 001_create_platform_account.sql
    ├── 002_create_order_session.sql
    ├── 003_create_order_package.sql
    └── 004_create_order_message.sql
```

## Cách chạy migration

```bash
mysql -u root -p < bot/database/migrations/000_init.sql
mysql -u root -p bot_db < bot/database/migrations/001_create_platform_account.sql
mysql -u root -p bot_db < bot/database/migrations/002_create_order_session.sql
mysql -u root -p bot_db < bot/database/migrations/003_create_order_package.sql
mysql -u root -p bot_db < bot/database/migrations/004_create_order_message.sql
```

## Mô tả các bảng

| Bảng | Mô tả |
|------|-------|
| `platform_account` | Tài khoản FB/IG liên kết với Customer ID |
| `order_session` | Phiên đặt hàng (state machine) |
| `order_package` | Mã bưu kiện liên kết với đơn hàng |
| `order_message` | Bằng chứng chat (ảnh, tin nhắn) |

## State Machine - order_session.status

```
collecting → ready → bound → closed
```

- `collecting` : Đang thu thập thông tin đơn hàng
- `ready`      : Đủ thông tin, chờ mã bưu kiện
- `bound`      : Đã liên kết mã bưu kiện
- `closed`     : Đã hoàn tất

## Quan hệ với hệ thống vận chuyển (ThinkPHP)

| Bot DB | Hệ thống vận chuyển | Ghi chú |
|--------|---------------------|---------|
| `platform_account.customer_id` | `yoshop_user.user_id` | ID khách hàng |
| `order_package.package_no` | `yoshop_package.express_num` | Mã bưu kiện quốc tế |
