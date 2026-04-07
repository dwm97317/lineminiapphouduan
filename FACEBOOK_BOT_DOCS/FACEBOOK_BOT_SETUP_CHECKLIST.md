# Facebook Bot Setup Checklist

**Project:** Line Mini App - Facebook Bot Integration  
**Last Updated:** April 7, 2026  
**Status:** Implementation Ready

---

## Phase 1: Code Implementation ⚙️

### Step 1: Create FacebookBot Controller
- [ ] Create file: `/source/application/api/controller/FacebookBot.php`
- [ ] Copy code from `FACEBOOK_IMPLEMENTATION_GUIDE.md` > Phase 1, Step 1.1
- [ ] Verify file syntax: `php -l /source/application/api/controller/FacebookBot.php`
- [ ] Check for errors: View controller has `verify()` and `webhook()` methods
- [ ] Verify: Contains signature validation (`verifySignature()`)
- [ ] Verify: Has event handlers (handleMessage, handlePostback, handleQuickReply)

### Step 2: Add Configuration
- [ ] Open `/source/application/config.php`
- [ ] Add Facebook config array from guide (copy Section "Phase 1, Step 1.2")
- [ ] Save file
- [ ] Test load: Create test file to `var_dump(config('facebook'))`

### Step 3: Add Routes
- [ ] Open `/source/application/route.php`
- [ ] Add Facebook routes from guide
- [ ] Route should map to `api/FacebookBot/verify|webhook`
- [ ] Save file
- [ ] Check syntax: `php -l /source/application/route.php`

### Step 4: Setup Environment Variables
- [ ] Create/update `.env` file in project root
- [ ] Add Facebook credentials:
  ```
  FACEBOOK_APP_ID=
  FACEBOOK_APP_SECRET=
  FACEBOOK_PAGE_TOKEN=
  FACEBOOK_VERIFY_TOKEN=my_test_token_12345
  ```
- [ ] Note: Leave app secret/token empty for now (will get from Facebook later)
- [ ] Verify file has no syntax errors

---

## Phase 2: Local Testing 🧪

### Step 5: Test Webhook Verification Locally
```bash
# Run webhook verification test
curl -X GET "http://localhost:8000/api/facebook/webhook?hub.mode=subscribe&hub.verify_token=my_test_token_12345&hub.challenge=test123"

# Expected response: "test123" (200 OK)
```

**Checklist:**
- [ ] Server is running on localhost:8000
- [ ] Response code is 200
- [ ] Response body equals the challenge parameter
- [ ] No PHP errors in response

### Step 6: Run Webhook Test Suite
```bash
# Make test file executable
chmod +x tests/bot_qa/test_facebook_webhook.php

# Run all tests
php tests/bot_qa/test_facebook_webhook.php all

# Or run specific tests
php tests/bot_qa/test_facebook_webhook.php verify
php tests/bot_qa/test_facebook_webhook.php message
php tests/bot_qa/test_facebook_webhook.php performance
```

**Checklist:**
- [ ] Test TC_FB_01 passes (webhook verification)
- [ ] Test TC_FB_02 passes (invalid token rejection)
- [ ] Test TC_FB_03 passes (message reception)
- [ ] Test TC_FB_04 passes (signature validation)
- [ ] Test TC_FB_08 passes (response time < 2s)
- [ ] All other tests pass
- [ ] No PHP errors or warnings

### Step 7: Check Logging
```bash
# View recent log entries
tail -100 /path/to/runtime/log/*.log

# Filter for FacebookBot logs
tail -100 /path/to/runtime/log/*.log | grep -i facebook
```

**Checklist:**
- [ ] Log file exists
- [ ] FacebookBot entries appear in logs
- [ ] No error entries for webhook requests
- [ ] Timestamp format is correct

---

## Phase 3: Facebook Account Setup 🔑

### Step 8: Create Facebook Developer Account
**Website:** https://developers.facebook.com/

- [ ] Visit https://developers.facebook.com/
- [ ] Click "Get Started"
- [ ] Create account or login with existing Facebook account
- [ ] Verify email address
- [ ] Complete profile setup
- [ ] Accept Developer Terms

**Result:** You should have a Facebook Developer account

### Step 9: Create Facebook App

**In Facebook Developer Dashboard:**
- [ ] Click "My Apps" → "Create App"
- [ ] Choose "Business" type
- [ ] Fill in:
  - App Name: `Order Tracking Bot` (or your choice)
  - App Contact Email: your@email.com
  - Select Category: `Business`
  - Accept terms
- [ ] Click "Create App"
- [ ] Complete security check (CAPTCHA, phone verification)

**Result:** App ID should appear in App Dashboard

**Copy these values:**
```
App ID: ___________________
App Secret: ___________________
```

### Step 10: Add Messenger Product

**In App Dashboard:**
- [ ] Click "Add Product"
- [ ] Search for "Messenger"
- [ ] Click "Set Up"
- [ ] Select Platform: "Facebook"
- [ ] Accept terms

**Result:** Messenger product added to your app

### Step 11: Create Test Facebook Page

**Options:**
- [ ] If you don't have a Facebook Page: Create one
  - Go to Facebook.com
  - Click dropdown (top left) → "Create"
  - Choose "Page"
  - Select category (e.g., "Business")
  - Fill details and create
  - Keep page name simple (can be test)

- [ ] If you have existing page: Use that one

**Get Page ID:**
- [ ] Go to your page settings
- [ ] Copy the page ID from URL or About section

```
Page Name: ___________________
Page ID: ___________________
```

### Step 12: Generate Access Tokens

**In Facebook App Dashboard:**
- [ ] Go to Messenger → Settings
- [ ] Under "Access Tokens"
- [ ] Select your Page from dropdown
- [ ] Copy Page Access Token

**Save this:**
```
Page Access Token: ___________________
```

**Verify token works:**
```bash
# Test API call with token
curl -X GET "https://graph.instagram.com/v18.0/me?access_token=YOUR_TOKEN"

# Should return page info (200 OK)
```

- [ ] API call returns 200 OK
- [ ] Response includes page info

---

## Phase 4: Webhook Configuration 🔗

### Step 13: Get Public HTTPS URL

You need a public HTTPS URL for your webhook. Options:

#### Option A: Use ngrok (for testing on localhost)
```bash
# Download ngrok from https://ngrok.com/download
# Unzip and run:
./ngrok http 8000

# Copy the HTTPS URL shown
```

**Save:** 
```
Webhook URL: https://xxxxx.ngrok.io/api/facebook/webhook
```

- [ ] ngrok is running
- [ ] HTTPS URL is accessible
- [ ] Can reach http://localhost:8000 through ngrok

#### Option B: Use production server
```
Webhook URL: https://yourdomain.com/api/facebook/webhook
```

- [ ] Domain has valid SSL certificate
- [ ] Server is publicly accessible
- [ ] HTTPS works (not HTTP)
- [ ] Can POST to that URL

### Step 14: Configure Webhook in Facebook

**In Facebook App Dashboard → Messenger → Settings:**

**Webhooks Section:**
- [ ] Click "Add Callback URL"
- [ ] Callback URL: `https://your-url/api/facebook/webhook`
- [ ] Verify Token: `my_test_token_12345` (from .env file)
- [ ] Click "Add"

**Expected:** Facebook will send verification request to your webhook

**If verification fails:**
- [ ] Check webhook URL is accessible
- [ ] Verify token matches exactly (case-sensitive)
- [ ] Check server logs for errors
- [ ] Ensure response is under 2 seconds

### Step 15: Subscribe Page to App

**Still in Webhooks Section:**
- [ ] Under "Subscribe Fields" select:
  - [ ] messages
  - [ ] messaging_postbacks
  - [ ] messaging_quick_replies
- [ ] Click "Save"

**Verify:**
- [ ] Page appears in "Subscribed Pages" list
- [ ] All three fields are selected

---

## Phase 5: Integration Testing 📨

### Step 16: Update Configuration with Real Tokens

**In `/source/.env` or config:**
```
FACEBOOK_APP_ID=your_app_id
FACEBOOK_APP_SECRET=your_app_secret
FACEBOOK_PAGE_TOKEN=your_page_token
FACEBOOK_VERIFY_TOKEN=my_test_token_12345
```

- [ ] All values filled in correctly
- [ ] No quotes around values
- [ ] No trailing spaces

### Step 17: Manual Test - Send Message via Facebook

**From your personal Facebook account:**
- [ ] Go to your test Page
- [ ] Click Message button or find chat
- [ ] Send a test message: `"Hello bot"`
- [ ] Wait for response

**Check results:**
- [ ] Bot responds with a message
- [ ] Check `/runtime/log/` for entries
- [ ] Response arrives within 2 seconds

**If no response:**
- [ ] Check webhook URL is reachable
- [ ] Check server logs for errors
- [ ] Verify Page Access Token is correct
- [ ] Check firewall/network rules
- [ ] Ensure HTTPS is working

### Step 18: Test Order Workflow

**Send these messages in sequence:**
```
1. "link 12345"           (link customer account)
2. "create order"         (start new order)
3. "Shop Name: ABC"       (add shop)
4. "Date: 2026-04-07"     (add date)
5. "Amount: 100"          (add amount)
6. "Tracking: 123ABC"     (add tracking code)
```

**Check each step:**
- [ ] Step 1: Bot acknowledges link
- [ ] Step 2: Bot creates new session
- [ ] Step 3: Bot confirms shop name
- [ ] Step 4: Bot confirms date
- [ ] Step 5: Bot confirms amount
- [ ] Step 6: Bot confirms tracking code
- [ ] No error messages
- [ ] Order saved to database

**Verify in database:**
```sql
-- Check if order was created
SELECT * FROM order_sessions WHERE customer_id = 12345 ORDER BY created_time DESC LIMIT 1;

-- Check tracking codes
SELECT * FROM tracking_codes WHERE order_id = ? LIMIT 1;
```

- [ ] Order record exists
- [ ] All fields are populated
- [ ] Timestamps are correct

---

## Phase 6: Performance Testing ⚡

### Step 19: Response Time Test
```bash
# Measure response time
php tests/bot_qa/test_facebook_webhook.php performance

# Should show response time
```

**Checklist:**
- [ ] Response time < 2000ms (Facebook requirement)
- [ ] Typically < 500ms under normal load
- [ ] No timeout errors
- [ ] Consistent across multiple tests

### Step 20: Load Test (Optional)

```bash
# Install Apache Bench or K6 for load testing
# Then run concurrent requests
ab -n 100 -c 10 http://localhost:8000/api/facebook/webhook
```

**Expected results:**
- [ ] 100 concurrent requests handled
- [ ] No crash or timeout
- [ ] Error rate = 0%
- [ ] Average response time < 2s

---

## Phase 7: Security Verification 🔒

### Step 21: Signature Verification Test

```bash
# Verify signature validation works
php tests/bot_qa/test_facebook_webhook.php all

# Check for "Invalid Signature" test pass
```

**Checklist:**
- [ ] Test TC_FB_04 passes (invalid signature rejection)
- [ ] Bot rejects unsigned requests
- [ ] Bot logs signature validation failures
- [ ] No sensitive data in error messages

### Step 22: Input Validation Check

**Send these malicious inputs to test protection:**

```
1. SQL injection attempt: "'; DROP TABLE orders; --"
   Expected: Bot rejects or escapes safely
   
2. XSS attempt: "<script>alert('xss')</script>"
   Expected: Bot escapes or rejects
   
3. Path traversal: "../../etc/passwd"
   Expected: Bot rejects
   
4. Oversized input: (1MB of text)
   Expected: Bot rejects or truncates
```

**Checklist:**
- [ ] All malicious inputs safely handled
- [ ] No database errors in response
- [ ] No sensitive info leaked
- [ ] Logs record security events

---

## Phase 8: Cleanup & Production (Optional) 🚀

### Step 23: Production Deployment

If moving to production:
- [ ] Update webhook URL to production domain
- [ ] Update FACEBOOK_VERIFY_TOKEN to random secure token
- [ ] Update config with production tokens
- [ ] Enable HTTPS (required by Facebook)
- [ ] Configure firewall for HTTPS (port 443)
- [ ] Setup monitoring/alerting
- [ ] Backup database before switching

### Step 24: Final Verification

**Run complete test suite:**
```bash
php tests/bot_qa/test_e2e_functional.php all
php tests/bot_qa/test_anti_confusion_isolation.php all
php tests/bot_qa/test_exception_scenarios.php all
php tests/bot_qa/test_facebook_webhook.php all
```

**Checklist:**
- [ ] All tests pass (or document expected failures)
- [ ] No new errors in logs
- [ ] System documentation updated
- [ ] Team briefed on changes

---

## Troubleshooting 🔧

### Webhook Not Receiving Messages

**Check 1: Verify webhook is subscribed**
```
Facebook Dashboard → Messenger → Webhooks
- [ ] Webhook URL listed
- [ ] Page is in "Subscribed Pages"
- [ ] Subscribe Fields include "messages"
```

**Check 2: Test endpoint directly**
```bash
curl -X POST http://localhost:8000/api/facebook/webhook \
  -H "Content-Type: application/json" \
  -d '{"object":"page","entry":[{"messaging":[{"sender":{"id":"123"},"message":{"text":"test"}}]}]}'

# Should return 200 OK
```

**Check 3: Check server logs**
```bash
tail -50 /runtime/log/*.log | grep -i facebook
# Look for error messages
```

### Signature Validation Failing

**Check:**
- [ ] FACEBOOK_APP_SECRET is correct (from App Dashboard)
- [ ] Header X-Hub-Signature is present
- [ ] Signature calculation uses correct algorithm (sha1)
- [ ] Entire request body is used (no trimming)

### Response Taking > 2 Seconds

**Optimize:**
- [ ] Add database indexes
- [ ] Enable caching (Redis)
- [ ] Use async queue for API calls
- [ ] Profile slow database queries
- [ ] Check network latency to Facebook API

### Bot Not Responding

**Debug:**
1. Check webhook receives the message
   - [ ] Log entry appears in `/runtime/log/`
   
2. Check message is being processed
   - [ ] handleMessage() is called
   - [ ] Order service is invoked
   
3. Check response is being sent
   - [ ] sendMessage() is called
   - [ ] Facebook API call succeeds
   - [ ] HTTP status code is 200

**Example log check:**
```bash
grep -A 10 "FacebookBot" /runtime/log/20260407.log | grep -i "message\|error"
```

---

## Success Criteria ✅

You can consider implementation successful when:

- [x] FacebookBot controller exists and has no syntax errors
- [x] Routes are configured correctly
- [x] Webhook verification works (GET request)
- [x] Message webhook receives events (POST request)
- [x] Signature validation prevents unauthorized requests
- [x] Test suite passes all tests
- [x] Response time < 2 seconds
- [x] Bot sends/receives messages via Facebook
- [x] Order workflow works end-to-end
- [x] No sensitive data leaks
- [x] Error handling works correctly
- [x] Logs contain appropriate entries

---

## Timeline Estimate

| Phase | Tasks | Time | Total |
|-------|-------|------|-------|
| 1 | Code Implementation | 1-2 hrs | 1-2 hrs |
| 2 | Local Testing | 30 min | 1.5-2.5 hrs |
| 3 | Facebook Setup | 30 min | 2-3 hrs |
| 4 | Webhook Config | 20 min | 2.2-3.2 hrs |
| 5 | Integration Testing | 1-2 hrs | 3.2-5.2 hrs |
| 6 | Performance Testing | 30 min | 3.7-5.7 hrs |
| 7 | Security Testing | 1 hr | 4.7-6.7 hrs |
| 8 | Production (optional) | 1-2 hrs | 5.7-8.7 hrs |

**Minimum time:** 3-5 hours (excluding optional production deployment)

---

## Support Resources

### Documentation
- Audit Report: `BOT_FACEBOOK_INTEGRATION_AUDIT.md`
- Implementation Guide: `FACEBOOK_IMPLEMENTATION_GUIDE.md`
- Test Suite: `tests/bot_qa/test_facebook_webhook.php`

### External Links
- Facebook Messenger Docs: https://developers.facebook.com/docs/messenger-platform
- ThinkPHP Guide: https://www.kancloud.cn/manual/thinkphp5
- ngrok Tunneling: https://ngrok.com/

### Getting Help
1. Check logs first: `/runtime/log/*.log`
2. Review audit report for similar issues
3. Run relevant test case
4. Check test output for error messages

---

## Sign-Off

**Implementation Status:** Ready to Begin  
**Last Updated:** April 7, 2026  
**Prepared By:** Technical Assessment  

**Next Action:** Start with Phase 1, Step 1 (Create FacebookBot Controller)

