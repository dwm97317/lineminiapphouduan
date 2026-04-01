# 📦 Git Commands - Add Bot QA Tests to Repository

## ✅ Files đã tạo trong `tests/bot_qa/`

### Test Scripts
- ✅ `test_performance.php` (13.9 KB)
- ✅ `test_security.php` (13.5 KB)
- ✅ `test_exception.php` (9.3 KB)
- ✅ `run_all_tests.php` (3.7 KB)

### Documentation
- ✅ `README.md` (folder overview)
- ✅ `TEST_README.md` (English documentation)
- ✅ `HUONG_DAN_TEST.md` (Vietnamese guide)
- ✅ `.gitignore` (git ignore rules)

### Existing Files (copied from test/)
- ✅ `BOT_PERFORMANCE_SECURITY_TEST_PLAN*.md` (3 languages)
- ✅ `BOT_SYSTEM_TEST_PLAN_AND_CASES*.md` (3 languages)
- ✅ `BOT_*_TEST_CASES.csv` (test data)
- ✅ `bot_simulator.html`
- ✅ `fake_login.php`

---

## 🚀 Git Commands

### Option 1: Add ALL files in tests/bot_qa/ (Recommended)

```bash
cd /home/quangpc/Desktop/lineminiapphouduan

# Step 1: Check status
git status tests/bot_qa/

# Step 2: Add all files
git add tests/bot_qa/

# Step 3: Commit with message
git commit -m "feat: Add professional Bot QA test suite

- Performance tests: concurrent users, cache, DB pool
- Security tests: API auth, SQL injection, rate limiting  
- Exception handling: reconnection, fallback mechanisms
- Documentation in EN/ZH/VN languages
- Test cases and simulator tools"

# Step 4: Push to remote
git push origin feature/bot-qa-testing
```

---

### Option 2: Add files selectively

```bash
# Add only test scripts
git add tests/bot_qa/test_performance.php
git add tests/bot_qa/test_security.php
git add tests/bot_qa/test_exception.php
git add tests/bot_qa/run_all_tests.php

# Add documentation
git add tests/bot_qa/README.md
git add tests/bot_qa/TEST_README.md
git add tests/bot_qa/HUONG_DAN_TEST.md

# Add existing test plans and cases
git add tests/bot_qa/BOT_*.md
git add tests/bot_qa/BOT_*.csv
git add tests/bot_qa/bot_simulator.html
git add tests/bot_qa/fake_login.php

# Commit
git commit -m "Add Bot QA test suite"
git push origin feature/bot-qa-testing
```

---

### Option 3: Create new branch if not exists

```bash
# Create and switch to new branch
git checkout -b feature/bot-qa-testing

# Add all files
git add tests/bot_qa/

# Commit
git commit -m "feat: Add comprehensive Bot QA testing framework"

# Push and set upstream
git push -u origin feature/bot-qa-testing
```

---

## 📋 Verify After Push

```bash
# Check git log
git log --oneline -5

# Verify files on remote (GitHub/GitLab web UI)
# Visit: https://github.com/YOUR_REPO/tests/tree/feature/bot-qa-testing/bot_qa
```

---

## 🔍 Git Status Check Commands

```bash
# See what will be committed
git status tests/bot_qa/

# See diff before commit
git diff --cached tests/bot_qa/

# Check file tracking
git ls-files tests/bot_qa/
```

---

## ⚠️ Troubleshooting

### Issue: Files already tracked in different location
```bash
# If files exist in root test/ folder and causing conflicts
git rm --cached test/test_performance.php
git rm --cached test/test_security.php
git rm --cached test/test_exception.php
git add tests/bot_qa/
git commit -m "Move test files to tests/bot_qa/"
```

### Issue: Large file warning
```bash
# Check file sizes
ls -lh tests/bot_qa/

# If CSV files too large, consider compressing or using Git LFS
git lfs install
git lfs track "tests/bot_qa/*.csv"
```

### Issue: Wrong branch
```bash
# Check current branch
git branch

# Switch to correct branch
git checkout feature/bot-qa-testing

# Or create if not exists
git checkout -b feature/bot-qa-testing
```

---

## 📊 Expected Output

After successful commit:
```
[feature/bot-qa-testing abc1234] feat: Add professional Bot QA test suite
 15 files changed, 2847 insertions(+)
 create mode 100644 tests/bot_qa/.gitignore
 create mode 100644 tests/bot_qa/README.md
 create mode 100644 tests/bot_qa/HUONG_DAN_TEST.md
 create mode 100644 tests/bot_qa/TEST_README.md
 create mode 100755 tests/bot_qa/test_performance.php
 create mode 100755 tests/bot_qa/test_security.php
 create mode 100755 tests/bot_qa/test_exception.php
 create mode 100755 tests/bot_qa/run_all_tests.php
 ...
```

---

## 🎯 Quick One-Liner

```bash
cd /home/quangpc/Desktop/lineminiapphouduan && git add tests/bot_qa/ && git commit -m "feat: Add Bot QA test suite" && git push origin feature/bot-qa-testing
```

---

**Sau khi push thành công, các file sẽ sẵn sàng cho team sử dụng! 🎉**
