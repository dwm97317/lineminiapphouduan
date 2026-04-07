# Bot QA Test Files
# This folder contains professional test suite for Bot System

## 📁 File Structure

### Test Scripts (PHP)
- `test_performance.php` - Performance testing (concurrent users, cache, DB pool)
- `test_security.php` - Security testing (API auth, SQL injection, rate limiting)
- `test_exception.php` - Exception handling testing (reconnection, fallback)
- `run_all_tests.php` - Master script to run all tests

### Documentation
- `TEST_README.md` - Complete test documentation (English)
- `HUONG_DAN_TEST.md` - Quick test guide (Vietnamese)
- `BOT_PERFORMANCE_SECURITY_TEST_PLAN*.md` - Test plans (EN/ZH/VN)
- `BOT_SYSTEM_TEST_PLAN_AND_CASES*.md` - System test cases (EN/ZH/VN)

### Test Data
- `BOT_*_TEST_CASES.csv` - Test case data in multiple languages
- `bot_simulator.html` - Bot message simulator
- `fake_login.php` - Mock login for testing

## 🚀 Quick Start

### 1. Start Web Server
```bash
cd /home/quangpc/Desktop/lineminiapphouduan
php -S localhost:8000 -t web/
```

### 2. Run Tests (in new terminal)
```bash
cd tests/bot_qa

# Run all tests
php run_all_tests.php

# Or run individual suites
php test_performance.php all
php test_security.php all
php test_exception.php all
```

## 📊 Test Coverage

### Performance Tests
- ✅ TC_PERF_01: 100 Concurrent Users
- ⚠️ TC_PERF_02: Redis Cache Hit Ratio (requires Redis extension)
- ✅ TC_PERF_03: Database Connection Pool
- ❌ TC_PERF_04: Webhook Message Processing

### Security Tests
- ❌ TC_SEC_01: API Key Authentication
- ⚠️ TC_SEC_02: SQL Injection Prevention
- ❌ TC_SEC_03: Multi-tenancy Isolation
- ⚠️ TC_SEC_04: Rate Limiting

### Exception Handling
- ℹ️ TC_EXC_01: Meta API Timeout (Auto + Manual)
- ℹ️ TC_EXC_02: Carrier API Fallback (Auto)
- 🔧 TC_EXC_03: Database Reconnection (Manual)
- 🔧 TC_EXC_04: Redis Reconnection (Manual)

## 🎯 Git Usage

### Add to Git
```bash
cd /home/quangpc/Desktop/lineminiapphouduan
git add tests/bot_qa/
git commit -m "Add professional Bot QA test suite"
git push origin main
```

### Git Ignore Rules
The following are ignored in this folder:
- `*.log` - Test output logs
- `.phpunit.result.cache` - PHPUnit cache
- `coverage/` - Code coverage reports
- `tmp/` - Temporary test files

## 📋 Prerequisites

- PHP 7.4+ with extensions: curl, json, redis (optional)
- Web server running on localhost:8000
- MySQL database configured
- Redis server (optional, for cache tests)

## 🆘 Support

See `TEST_README.md` or `HUONG_DAN_TEST.md` for detailed instructions.
