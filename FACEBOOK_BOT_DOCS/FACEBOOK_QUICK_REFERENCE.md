# Facebook Bot - Quick Reference Card

**Print this or bookmark it while implementing!**

---

## 🎯 One-Page Summary

**Status:** Bot works with LINE ✅ | Facebook integration not built ❌

**What you need to do:**
1. Create FacebookBot controller (copy from guide)
2. Add routes and config
3. Test webhook locally
4. Set up Facebook account
5. Deploy and test

**Time needed:** 5-8 hours

---

## 📂 Key Files You Need

| File | Purpose | Status |
|------|---------|--------|
| `FACEBOOK_IMPLEMENTATION_GUIDE.md` | 📖 Start here - code templates | ✅ Ready |
| `BOT_FACEBOOK_INTEGRATION_AUDIT.md` | 📊 Deep technical details | ✅ Ready |
| `FACEBOOK_BOT_SETUP_CHECKLIST.md` | ✓ Step-by-step checklist | ✅ Ready |
| `tests/bot_qa/test_facebook_webhook.php` | 🧪 Test suite (CLI) | ✅ Ready |

---

## 🛠️ Implementation Quick Steps

### Step 1: Create Controller (5 min)
```bash
# Create file
touch /source/application/api/controller/FacebookBot.php

# Copy code from FACEBOOK_IMPLEMENTATION_GUIDE.md (Phase 1, Step 1.1)
```

### Step 2: Update Configuration (5 min)
```php
// In /source/application/config.php, add:
'facebook' => [
    'app_id' => env('FACEBOOK_APP_ID', ''),
    'app_secret' => env('FACEBOOK_APP_SECRET', ''),
    'page_token' => env('FACEBOOK_PAGE_TOKEN', ''),
    'verify_token' => env('FACEBOOK_VERIFY_TOKEN', 'test_token'),
]
```

### Step 3: Add Routes (5 min)
```php
// In /source/application/route.php, add:
'api/facebook/webhook' => 'api/FacebookBot/verify|webhook',
```

### Step 4: Create .env (5 min)
```
FACEBOOK_APP_ID=
FACEBOOK_APP_SECRET=
FACEBOOK_PAGE_TOKEN=
FACEBOOK_VERIFY_TOKEN=my_test_token_12345
```

### Step 5: Test Locally (10 min)
```bash
# Test webhook verification
curl "http://localhost:8000/api/facebook/webhook?hub.mode=subscribe&hub.verify_token=my_test_token_12345&hub.challenge=test123"

# Run test suite
php tests/bot_qa/test_facebook_webhook.php all
```

---

## 🔑 Critical Information

### Facebook Webhook Signature Validation
```php
// MUST verify every webhook
$signature = hash_hmac('sha1', $body, $app_secret);
$expected = 'sha1=' . $signature;

// Compare with header
if (!hash_equals($header_signature, $expected)) {
    return false; // Reject unauthorized webhook
}
```

### Response Time Requirement
```
Maximum: 2000ms (2 seconds)
Target: < 500ms
Facebook will retry if timeout
```

### Required Subscribe Fields
```
✓ messages
✓ messaging_postbacks  
✓ messaging_quick_replies
```

---

## 📱 Testing Checklist

```
Before Facebook Account Setup:
- [ ] Webhook verification works (GET)
- [ ] Message reception works (POST)
- [ ] Signature validation works
- [ ] Response time < 2s
- [ ] All tests pass (php test_facebook_webhook.php all)

After Facebook Account Setup:
- [ ] Send message from Facebook → Bot responds
- [ ] Create order workflow → Stores in database
- [ ] Multiple messages → All processed
- [ ] No error messages in logs
```

---

## 🚨 Common Issues & Quick Fixes

| Issue | Quick Fix |
|-------|-----------|
| 403 Forbidden | Check FACEBOOK_VERIFY_TOKEN matches |
| Webhook not receiving | Verify webhook URL is accessible + HTTPS |
| Timeout error | Reduce curl timeout or use async queue |
| Response taking >2s | Add database indexes / enable caching |
| Message not processed | Check event handler method names |
| Signature validation fails | Verify APP_SECRET is correct |

---

## 🔐 Security Essentials

```php
// Always do these:
✓ Verify webhook signature on every POST
✓ Validate user input before database queries
✓ Use environment variables for secrets
✓ Log all webhook events
✓ Handle errors gracefully (no stack traces to user)
✓ Use HTTPS (required by Facebook)
✓ Never commit secrets to git
```

---

## 📊 Performance Targets

```
Webhook Response: < 2 seconds (Facebook requirement)
Concurrent Users: 100+ (per test suite)
Cache Hit Ratio: > 80% (if using Redis)
Database Response: < 100ms (optimized queries)
API Call Timeout: 10 seconds (with retry logic)
```

---

## 🔗 External Links

```
Facebook Messenger Platform:
https://developers.facebook.com/docs/messenger-platform

Get Started:
https://developers.facebook.com/apps/

ngrok (for localhost testing):
https://ngrok.com/download
```

---

## 💡 Pro Tips

1. **Use ngrok for local testing** - Tunnel localhost to public HTTPS
2. **Test with your own number first** - Before inviting others
3. **Keep verify token simple** - Easy to remember for testing
4. **Check logs frequently** - `/runtime/log/` has all the answers
5. **Use curl for manual testing** - Faster than opening Facebook
6. **Start with text messages** - Add image/media later
7. **Monitor response times** - Add logging to measure performance

---

## 📋 Before You Start

Make sure you have:
- [ ] ThinkPHP 5 installed and working
- [ ] cURL extension enabled
- [ ] PHP 7.4+ 
- [ ] Ability to run CLI commands
- [ ] Access to /runtime/log/ directory
- [ ] MySQL database configured

---

## 🎓 Learning Order

1. **Read:** This Quick Reference (you are here) - 5 min
2. **Read:** FACEBOOK_IMPLEMENTATION_GUIDE.md - 20 min
3. **Code:** Copy controller & configure - 20 min
4. **Test:** Run test suite locally - 10 min
5. **Setup:** Create Facebook account - 30 min
6. **Deploy:** Configure webhook - 10 min
7. **Test:** Manual testing - 30 min

**Total:** ~2-3 hours for basic setup

---

## 💬 Remember

> The bot logic is the same for Facebook and LINE.
> You're just building the **connector** between Facebook Messenger and your existing bot.

**Most of your code (order processing, notifications) is reusable!**

---

## ✅ Success = This Works

```
User sends message to Facebook Page
        ↓
Webhook receives & validates
        ↓
Message routed to order logic
        ↓
Bot processes request
        ↓
Response sent back via Facebook
        ↓
User sees reply in Messenger
```

When this happens end-to-end, you're done! ✨

---

## 📞 Need Help?

1. **Check logs:** `tail -50 /runtime/log/*.log`
2. **Run tests:** `php tests/bot_qa/test_facebook_webhook.php all`
3. **Read docs:** Audit report has answers
4. **Manual test:** Use curl to test webhook

---

**Ready? Start with Step 1: Create FacebookBot.php**

Good luck! 🚀
