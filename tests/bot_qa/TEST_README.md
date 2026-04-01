# Bot System - Test Suite Documentation

## 📋 Overview

Bộ test suite chuyên nghiệp cho hệ thống Bot LINE Mini App, bao gồm:
- **Performance Tests**: Kiểm tra hiệu năng dưới tải cao
- **Security Tests**: Kiểm tra lỗ hổng bảo mật
- **Exception Handling Tests**: Kiểm tra khả năng phục hồi

## 🚀 Quick Start

### Yêu cầu hệ thống
```bash
# PHP 7.4+
php -v

# Web server đang chạy
php -S localhost:8000 -t web/

# (Optional) Redis extension
sudo apt-get install php-redis
```

### Chạy tất cả tests
```bash
cd /home/quangpc/Desktop/lineminiapphouduan/test

# Performance tests
php test_performance.php all

# Security tests
php test_security.php all

# Exception handling tests
php test_exception.php all
```

---

## 📊 Test Categories

### 1️⃣ Performance Tests (`test_performance.php`)

Kiểm tra hiệu năng hệ thống dưới tải cao.

#### TC_PERF_01: 100 Concurrent Users
**Mục tiêu**: Xử lý 100 người dùng đồng thời  
**Tiêu chí đạt**: 
- ✅ 100% success rate
- ✅ Avg response time < 2 giây

```bash
php test_performance.php perf_concurrent
```

#### TC_PERF_02: Redis Cache Hit Ratio
**Mục tiêu**: Kiểm tra hiệu quả cache  
**Tiêu chí đạt**: 
- ✅ Cache hit ratio >= 80%

```bash
php test_performance.php perf_cache
```

#### TC_PERF_03: Database Connection Pool
**Mục tiêu**: Stress test connection pool  
**Tiêu chí đạt**: 
- ✅ Không có "too many connections" errors

```bash
php test_performance.php perf_db_pool
```

#### TC_PERF_04: Webhook Message Queue
**Mục tiêu**: Xử lý burst webhook messages  
**Tiêu chí đạt**: 
- ✅ 100% messages processed

```bash
php test_performance.php perf_webhook
```

---

### 2️⃣ Security Tests (`test_security.php`)

Kiểm tra các lỗ hổng bảo mật.

#### TC_SEC_01: API Key Authentication
**Mục tiêu**: Prevent API bypass  
**Tiêu chí đạt**: 
- ✅ Request không có API key → 401 Unauthorized
- ✅ Invalid API key → 401 Unauthorized

```bash
php test_security.php sec_api_key
```

#### TC_SEC_02: SQL Injection Prevention
**Mục tiêu**: Prevent SQL injection attacks  
**Patterns tested**:
- `' OR '1'='1`
- `' OR 1=1; DROP TABLE users;--`
- `' UNION SELECT * FROM users;--`
- XSS attempts

```bash
php test_security.php sec_sql_injection
```

#### TC_SEC_03: wxapp_id Forgery (Multi-tenancy)
**Mục tiêu**: Prevent cross-tenant access  
**Tiêu chí đạt**: 
- ✅ Token wxapp_id=1 không thể truy cập wxapp_id=2

```bash
php test_security.php sec_wxapp_id
```

#### TC_SEC_04: Rate Limiting
**Mục tiêu**: Enforce 100 requests/minute limit  
**Tiêu chí đạt**: 
- ✅ Returns 429 after 100 requests

```bash
php test_security.php sec_rate_limit
```

---

### 3️⃣ Exception Handling Tests (`test_exception.php`)

Kiểm tra khả năng phục hồi khi có lỗi.

#### TC_EXC_01: Meta API Timeout
**Mục tiêu**: Handle external API timeouts  
**Test type**: Manual + Automated check

```bash
php test_exception.php exc_meta_timeout
```

#### TC_EXC_02: Carrier API Fallback
**Mục tiêu**: Graceful error handling  
**Tiêu chí đạt**: 
- ✅ Không trả về 500 error

```bash
php test_exception.php exc_carrier_fallback
```

#### TC_EXC_03: Database Reconnection ⚠️ MANUAL
**Mục tiêu**: Recover from DB connection loss  

**Manual Steps**:
1. Stop MySQL: `sudo systemctl stop mysql`
2. Test API: `curl http://localhost:8000/api/package/index`
3. Expected: "System busy" error
4. Start MySQL: `sudo systemctl start mysql`
5. Test again: Should work normally

```bash
php test_exception.php exc_db_reconnect
```

#### TC_EXC_04: Redis Reconnection ⚠️ MANUAL
**Mục tiêu**: Recover from Redis connection loss  

**Manual Steps**:
1. Stop Redis: `sudo systemctl stop redis`
2. Test API
3. Expected: Fallback to DB or error
4. Start Redis: `sudo systemctl start redis`
5. Test again

```bash
php test_exception.php exc_redis_reconnect
```

---

## 📁 File Structure

```
test/
├── test_performance.php      # Performance test suite
├── test_security.php         # Security test suite
├── test_exception.php        # Exception handling test suite
└── TEST_README.md           # This file
```

---

## 🎯 Usage Examples

### Run individual test
```bash
# Test concurrent performance
php test_performance.php perf_concurrent

# Test SQL injection
php test_security.php sec_sql_injection

# Test database reconnection
php test_exception.php exc_db_reconnect
```

### Run full category
```bash
# All performance tests
php test_performance.php all

# All security tests
php test_security.php all

# All exception tests
php test_exception.php all
```

### Run with output logging
```bash
# Log to file
php test_performance.php all 2>&1 | tee perf_results.log

# Run and show summary only
php test_security.php all | grep -E "(PASSED|FAILED|SUMMARY)"
```

---

## 📊 Interpreting Results

### Symbols
- ✅ **PASSED**: Test criteria met
- ❌ **FAILED**: Test criteria not met
- ⚠️ **WARNING**: Potential issue
- ℹ️ **INFO**: Manual test required
- 🧪 **TEST**: Running test

### Exit Codes
- `0`: All tests passed
- `1`: Some tests failed
- `2`: Error running tests

---

## 🔧 Troubleshooting

### Issue: All HTTP tests fail with code 0
**Solution**: Start web server first
```bash
php -S localhost:8000 -t web/
```

### Issue: Redis tests skipped
**Solution**: Install Redis extension
```bash
sudo apt-get install php-redis
# Restart web server
```

### Issue: "Too many connections" error
**Solution**: Increase MySQL connection limit
```sql
SET GLOBAL max_connections = 500;
```

### Issue: Rate limiting not working
**Solution**: Implement rate limiter middleware in your application

---

## 📈 Performance Benchmarks

| Metric | Target | Acceptable | Poor |
|--------|--------|------------|------|
| Concurrent Users (100) | 100% success | >95% | <95% |
| Avg Response Time | <500ms | <1000ms | >2000ms |
| Cache Hit Ratio | >90% | >80% | <80% |
| DB Connections | 0 errors | <5 errors | >5 errors |

---

## 🔒 Security Checklist

- [ ] API authentication enforced on all endpoints
- [ ] SQL injection prevention validated
- [ ] Multi-tenancy isolation working
- [ ] Rate limiting active (100 req/min)
- [ ] Input validation on all user inputs
- [ ] Error messages don't leak sensitive info

---

## 📝 Notes

1. **Test Environment**: Run tests in staging/QA environment, NOT production
2. **Data Cleanup**: Tests create temporary data - cleanup after execution
3. **Concurrent Testing**: Ensure no other heavy processes running during tests
4. **Manual Tests**: Some tests require manual intervention for safety

---

## 🆘 Support

For issues or questions:
1. Check this README first
2. Review test output logs
3. Check application logs in `runtime/log/`
4. Verify system requirements are met

---

**Last Updated**: 2026-04-01  
**Version**: 1.0  
**Test Framework**: Custom PHP Test Suite
