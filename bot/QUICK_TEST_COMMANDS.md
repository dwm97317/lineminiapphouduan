# Quick Test Commands

Các lệnh nhanh để test từng tiêu chí.

## 🚀 Chuẩn bị

```bash
# Terminal 1: Khởi động API
cd bot
uvicorn main:app --reload

# Terminal 2: Chạy tests
cd bot
```

## ✅ Test 1: FB Webhook Verification

```bash
# Test valid verification
curl -X GET "http://localhost:8000/webhook/facebook?hub.mode=subscribe&hub.challenge=test_challenge&hub.verify_token=test_verify_token"

# Expected: {"hub.challenge":"test_challenge"}
```

## ✅ Test 2: IG Webhook Verification

```bash
# Test valid verification
curl -X GET "http://localhost:8000/webhook/instagram?hub.mode=subscribe&hub.challenge=test_challenge&hub.verify_token=test_verify_token"

# Expected: {"hub.challenge":"test_challenge"}
```

## ✅ Test 3: Signature Verification

```bash
# Test valid signature
python -c "
import json
import hmac
import hashlib
import requests

payload = {'object': 'page', 'entry': []}
body = json.dumps(payload).encode('utf-8')
hash_value = hmac.new(b'test_app_secret', body, hashlib.sha1).hexdigest()
signature = f'sha1={hash_value}'

response = requests.post(
    'http://localhost:8000/webhook/facebook',
    data=body,
    headers={'Content-Type': 'application/json', 'X-Hub-Signature': signature}
)
print(f'Status: {response.status_code}')
print(f'Response: {response.json()}')
"

# Expected: Status 200, {"status":"ok"}
```

## ✅ Test 4: Message Reception

```bash
# Test Facebook message
python -c "
import json
import hmac
import hashlib
import requests

payload = {
    'object': 'page',
    'entry': [{
        'id': '123456789',
        'time': 1234567890,
        'messaging': [{
            'sender': {'id': 'user_123'},
            'recipient': {'id': 'page_123'},
            'timestamp': 1234567890,
            'message': {'mid': 'msg_123', 'text': 'Hello bot!'}
        }]
    }]
}

body = json.dumps(payload).encode('utf-8')
hash_value = hmac.new(b'test_app_secret', body, hashlib.sha1).hexdigest()
signature = f'sha1={hash_value}'

response = requests.post(
    'http://localhost:8000/webhook/facebook',
    data=body,
    headers={'Content-Type': 'application/json', 'X-Hub-Signature': signature}
)
print(f'Status: {response.status_code}')
print(f'Response: {response.json()}')
"

# Expected: Status 200, {"status":"ok"}
```

## ✅ Test 5: Send Text Message

```bash
# Check MessengerAPI class
python -c "
from services.messenger_api import MessengerAPI
api = MessengerAPI('test_token')
print(f'MessengerAPI class: OK')
print(f'send_text_message method: {hasattr(api, \"send_text_message\")}')
print(f'send_quick_reply method: {hasattr(api, \"send_quick_reply\")}')
"

# Expected: All methods exist
```

## ✅ Test 6: Send Quick Reply

```bash
# Check QuickReply class
python -c "
from services.messenger_api import QuickReply, MessengerAPI

# Create QuickReply
qr = QuickReply('Yes', 'PAYLOAD_YES')
print(f'QuickReply created: {qr.title}')
print(f'QuickReply to_dict: {qr.to_dict()}')

# Check send_quick_reply
api = MessengerAPI('test_token')
print(f'send_quick_reply method: {hasattr(api, \"send_quick_reply\")}')
"

# Expected: All checks pass
```

## 🧪 Run All Tests

```bash
# Comprehensive test suite
python run_all_tests.py

# Or with shell script
bash run_tests.sh
```

## 📊 Expected Output

```
======================================================================
META API INTEGRATION - COMPREHENSIVE TEST SUITE
======================================================================

TEST 1: FB Webhook xác thực thành công
  ✓ Valid verification
  ✓ Invalid token rejection
  ✓ Invalid mode rejection

TEST 2: IG Webhook xác thực thành công
  ✓ Valid verification
  ✓ Invalid token rejection

TEST 3: Webhook Signature Verification
  ✓ Valid signature accepted
  ✓ Invalid signature rejected
  ✓ Missing signature rejected

TEST 4: Nhận và xử lý tin nhắn bất đồng bộ
  ✓ Facebook message received
  ✓ Instagram message received
  ✓ Multiple messages processed

TEST 5: Gửi tin nhắn văn bản thành công
  ✓ MessengerAPI class exists
  ✓ send_text_message method exists
  ✓ Method signature correct

TEST 6: Gửi nút Quick Reply thành công
  ✓ QuickReply class exists
  ✓ QuickReply instance created
  ✓ to_dict method works
  ✓ send_quick_reply method exists
  ✓ Method signature correct

======================================================================
TEST SUMMARY
======================================================================
TOTAL: 20/20 tests passed
======================================================================
✅ ALL TESTS PASSED!
```

## 🔍 Troubleshooting

### API not running
```bash
# Check if port 8000 is in use
lsof -i :8000

# Kill process if needed
kill -9 <PID>

# Start API
uvicorn main:app --reload
```

### Import errors
```bash
# Reinstall dependencies
pip install -r requirements.txt

# Or specific packages
pip install fastapi uvicorn httpx requests
```

### Webhook verification failed
```bash
# Check .env file
cat .env

# Verify token should match
FACEBOOK_VERIFY_TOKEN=test_verify_token
INSTAGRAM_VERIFY_TOKEN=test_verify_token
```

## 📝 Test Checklist

- [ ] API is running on http://localhost:8000
- [ ] .env file is configured
- [ ] Test 1: FB Webhook verification passed
- [ ] Test 2: IG Webhook verification passed
- [ ] Test 3: Signature verification passed
- [ ] Test 4: Message reception passed
- [ ] Test 5: Send text message passed
- [ ] Test 6: Send quick reply passed
- [ ] All 20+ tests passed
- [ ] Ready for production

## 🎯 Next Steps

1. Run `python run_all_tests.py`
2. Verify all tests pass
3. Check logs for any warnings
4. Configure real webhook URLs in Meta Dashboard
5. Deploy to production

---

**For detailed information, see [TESTING_GUIDE.md](./TESTING_GUIDE.md)**
