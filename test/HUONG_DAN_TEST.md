# 🧪 Hướng Dẫn Test Hệ Thống Bot

## 📦 Files đã tạo

```
test/
├── test_performance.php      ← Test hiệu năng
├── test_security.php         ← Test bảo mật  
├── test_exception.php        ← Test xử lý ngoại lệ
├── run_all_tests.php         ← Chạy tất cả tests
└── TEST_README.md           ← Tài liệu chi tiết (English)
```

---

## 🚀 Chạy Test Nhanh

### 1. Khởi động Web Server
```bash
cd /home/quangpc/Desktop/lineminiapphouduan
php -S localhost:8000 -t web/
```

**Mở terminal mới** để chạy tests.

### 2. Chạy từng loại test

#### Test Hiệu Năng (Performance)
```bash
cd test
php test_performance.php all
```

**Kết quả mẫu:**
```
✅ TC_PERF_01: PASSED (100 users đồng thời)
⚠️  TC_PERF_02: SKIPPED (chưa cài Redis)
✅ TC_PERF_03: PASSED (Database connection pool)
❌ TC_PERF_04: FAILED (Webhook processing)
```

#### Test Bảo Mật (Security)
```bash
php test_security.php all
```

**Kết quả mẫu:**
```
❌ TC_SEC_01: FAILED (API bypass possible)
⚠️  TC_SEC_02: NEEDS REVIEW (SQL injection)
❌ TC_SEC_03: FAILED (Cross-tenant access)
⚠️  TC_SEC_04: WARNING (No rate limiting)
```

#### Test Xử Lý Ngoại Lệ (Exception)
```bash
php test_exception.php all
```

### 3. Chạy TẤT CẢ tests cùng lúc
```bash
php run_all_tests.php
```

---

## 📊 Giải Thích Kết Quả

### Ký Hiệu
- ✅ **PASSED** - Đạt yêu cầu
- ❌ **FAILED** - Không đạt, cần fix
- ⚠️ **WARNING** - Cảnh báo, cần kiểm tra
- ℹ️ **SKIPPED** - Bỏ qua (thiếu điều kiện)
- 🔧 **MANUAL** - Cần test thủ công

### Exit Codes
- `0` = Tất cả tests đạt ✅
- `1` = Có tests không đạt ❌

---

## 🔍 Chi Tiết Các Bài Test

### 1️⃣ Performance Tests (Hiệu Năng)

| Test | Mục Đích | Yêu Cầu Đạt |
|------|----------|-------------|
| **TC_PERF_01** | 100 users đồng thời | 100% success, < 2s response |
| **TC_PERF_02** | Redis cache hit ratio | >= 80% hits |
| **TC_PERF_03** | Database connection pool | Không có "too many connections" |
| **TC_PERF_04** | Webhook message queue | 100% messages processed |

### 2️⃣ Security Tests (Bảo Mật)

| Test | Mục Đích | Yêu Cầu Đạt |
|------|----------|-------------|
| **TC_SEC_01** | API Key authentication | Request không có key → 401 |
| **TC_SEC_02** | SQL injection prevention | Tất cả injections bị chặn |
| **TC_SEC_03** | Multi-tenancy isolation | Token tenant A không access được tenant B |
| **TC_SEC_04** | Rate limiting | 429 error sau 100 requests/phút |

### 3️⃣ Exception Tests (Xử Lý Ngoại Lệ)

| Test | Mục Đích | Loại Test |
|------|----------|-----------|
| **TC_EXC_01** | Meta API timeout | Auto + Manual |
| **TC_EXC_02** | Carrier API fallback | Auto |
| **TC_EXC_03** | Database reconnection | Manual ⚠️ |
| **TC_EXC_04** | Redis reconnection | Manual ⚠️ |

---

## ⚠️ Lỗi Thường Gặp

### ❌ "HTTP code 0" cho tất cả tests
**Nguyên nhân**: Web server chưa chạy  
**Khắc phục**: 
```bash
php -S localhost:8000 -t web/
```

### ❌ "Redis extension not installed"
**Nguyên nhân**: Chưa cài Redis PHP extension  
**Khắc phục**:
```bash
sudo apt-get install php-redis
# Restart web server
```

### ❌ "Permission denied"
**Nguyên nhân**: File chưa có execute permission  
**Khắc phục**:
```bash
chmod +x test/*.php
```

---

## 📋 Checklist Trước Khi Deploy

### Bắt Buộc
- [ ] ✅ Tất cả performance tests pass
- [ ] ✅ Không có security issues critical
- [ ] ✅ Database connection pool ổn định
- [ ] ✅ Response time < 2 giây

### Nên Có
- [ ] ✅ Redis cache active và hit ratio > 80%
- [ ] ✅ API authentication enforced
- [ ] ✅ Rate limiting enabled
- [ ] ✅ Multi-tenancy isolation working

---

## 🎯 Ví Dụ Chạy Test

### Ví dụ 1: Test nhanh performance
```bash
cd test
php test_performance.php perf_concurrent
```

### Ví dụ 2: Test security cụ thể
```bash
php test_security.php sec_sql_injection
```

### Ví dụ 3: Chạy tất cả và lưu log
```bash
php run_all_tests.php 2>&1 | tee test_results.log
```

### Ví dụ 4: Chỉ xem summary
```bash
php test_security.php all | grep -E "(PASSED|FAILED|SUMMARY)"
```

---

## 📞 Hỗ Trợ

### Tài Liệu
- **Chi tiết**: `TEST_README.md` (tiếng Anh)
- **Nhanh**: File này (tiếng Việt)

### Debug Tips
1. Luôn start web server trước khi chạy tests
2. Kiểm tra PHP version: `php -v`
3. Xem logs ở `runtime/log/`
4. Dùng `--verbose` flag nếu có

---

## 📈 Benchmarks Mục Tiêu

| Metric | Tốt | Chấp Nhận | Kém |
|--------|-----|-----------|-----|
| Success Rate | 100% | >95% | <95% |
| Response Time | <500ms | <1s | >2s |
| Cache Hit Ratio | >90% | >80% | <80% |
| DB Errors | 0 | <5 | >5 |

---

## 🔧 Custom Tests

Để chạy test riêng lẻ:

```bash
# Performance
php test_performance.php perf_concurrent
php test_performance.php perf_cache
php test_performance.php perf_db_pool
php test_performance.php perf_webhook

# Security
php test_security.php sec_api_key
php test_security.php sec_sql_injection
php test_security.php sec_wxapp_id
php test_security.php sec_rate_limit

# Exception
php test_exception.php exc_meta_timeout
php test_exception.php exc_carrier_fallback
php test_exception.php exc_db_reconnect
php test_exception.php exc_redis_reconnect
```

---

**Chúc bạn test vui vẻ! 🎉**

Nếu có vấn đề, check `TEST_README.md` để biết chi tiết.
