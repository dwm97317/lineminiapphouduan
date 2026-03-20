#!/usr/bin/env python3
"""
Comprehensive test suite for Meta API integration
Tests all acceptance criteria
"""

import sys
import json
import hmac
import hashlib
import requests
import asyncio
from typing import Dict, Any, List
from datetime import datetime

# Configuration
API_BASE_URL = "http://localhost:8000"
FACEBOOK_APP_SECRET = "test_app_secret"
FACEBOOK_VERIFY_TOKEN = "test_verify_token"
INSTAGRAM_VERIFY_TOKEN = "test_verify_token"

# Test results
test_results: Dict[str, Dict[str, Any]] = {}


def log_test(category: str, test_name: str, status: bool, message: str = ""):
    """Log test result"""
    if category not in test_results:
        test_results[category] = {}
    
    test_results[category][test_name] = {
        "status": "✓ PASS" if status else "✗ FAIL",
        "message": message,
        "timestamp": datetime.now().isoformat()
    }
    
    status_icon = "✓" if status else "✗"
    print(f"  {status_icon} {test_name}")
    if message:
        print(f"    → {message}")


def generate_signature(body: bytes, app_secret: str) -> str:
    """Generate webhook signature"""
    hash_value = hmac.new(
        app_secret.encode('utf-8'),
        body,
        hashlib.sha1
    ).hexdigest()
    return f"sha1={hash_value}"


# ============================================================================
# TEST 1: FB Webhook Verification
# ============================================================================

def test_fb_webhook_verification():
    """Test 1: FB Webhook xác thực thành công"""
    print("\n" + "="*70)
    print("TEST 1: FB Webhook xác thực thành công")
    print("="*70)
    
    try:
        # Test 1.1: Valid verification
        print("\n1.1 Valid verification request:")
        response = requests.get(
            f"{API_BASE_URL}/webhook/facebook",
            params={
                "hub.mode": "subscribe",
                "hub.challenge": "test_challenge_12345",
                "hub.verify_token": FACEBOOK_VERIFY_TOKEN
            },
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get("hub.challenge") == "test_challenge_12345":
                log_test("FB Webhook", "Valid verification", True, 
                        f"Challenge echoed back correctly")
            else:
                log_test("FB Webhook", "Valid verification", False, 
                        "Challenge mismatch")
        else:
            log_test("FB Webhook", "Valid verification", False, 
                    f"Status code: {response.status_code}")
        
        # Test 1.2: Invalid verify token
        print("\n1.2 Invalid verify token:")
        response = requests.get(
            f"{API_BASE_URL}/webhook/facebook",
            params={
                "hub.mode": "subscribe",
                "hub.challenge": "test_challenge_12345",
                "hub.verify_token": "wrong_token"
            },
            timeout=5
        )
        
        if response.status_code == 403:
            log_test("FB Webhook", "Invalid token rejection", True, 
                    "Correctly rejected invalid token")
        else:
            log_test("FB Webhook", "Invalid token rejection", False, 
                    f"Expected 403, got {response.status_code}")
        
        # Test 1.3: Invalid hub mode
        print("\n1.3 Invalid hub mode:")
        response = requests.get(
            f"{API_BASE_URL}/webhook/facebook",
            params={
                "hub.mode": "invalid_mode",
                "hub.challenge": "test_challenge_12345",
                "hub.verify_token": FACEBOOK_VERIFY_TOKEN
            },
            timeout=5
        )
        
        if response.status_code == 403:
            log_test("FB Webhook", "Invalid mode rejection", True, 
                    "Correctly rejected invalid mode")
        else:
            log_test("FB Webhook", "Invalid mode rejection", False, 
                    f"Expected 403, got {response.status_code}")
        
    except Exception as e:
        log_test("FB Webhook", "Verification test", False, str(e))


# ============================================================================
# TEST 2: IG Webhook Verification
# ============================================================================

def test_ig_webhook_verification():
    """Test 2: IG Webhook xác thực thành công"""
    print("\n" + "="*70)
    print("TEST 2: IG Webhook xác thực thành công")
    print("="*70)
    
    try:
        # Test 2.1: Valid verification
        print("\n2.1 Valid verification request:")
        response = requests.get(
            f"{API_BASE_URL}/webhook/instagram",
            params={
                "hub.mode": "subscribe",
                "hub.challenge": "test_challenge_67890",
                "hub.verify_token": INSTAGRAM_VERIFY_TOKEN
            },
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get("hub.challenge") == "test_challenge_67890":
                log_test("IG Webhook", "Valid verification", True, 
                        "Challenge echoed back correctly")
            else:
                log_test("IG Webhook", "Valid verification", False, 
                        "Challenge mismatch")
        else:
            log_test("IG Webhook", "Valid verification", False, 
                    f"Status code: {response.status_code}")
        
        # Test 2.2: Invalid verify token
        print("\n2.2 Invalid verify token:")
        response = requests.get(
            f"{API_BASE_URL}/webhook/instagram",
            params={
                "hub.mode": "subscribe",
                "hub.challenge": "test_challenge_67890",
                "hub.verify_token": "wrong_token"
            },
            timeout=5
        )
        
        if response.status_code == 403:
            log_test("IG Webhook", "Invalid token rejection", True, 
                    "Correctly rejected invalid token")
        else:
            log_test("IG Webhook", "Invalid token rejection", False, 
                    f"Expected 403, got {response.status_code}")
        
    except Exception as e:
        log_test("IG Webhook", "Verification test", False, str(e))


# ============================================================================
# TEST 3: Webhook Signature Verification
# ============================================================================

def test_webhook_signature_verification():
    """Test 3: Signature verification"""
    print("\n" + "="*70)
    print("TEST 3: Webhook Signature Verification")
    print("="*70)
    
    try:
        # Test 3.1: Valid signature
        print("\n3.1 Valid signature:")
        payload = {
            "object": "page",
            "entry": [
                {
                    "id": "123456789",
                    "time": 1234567890,
                    "messaging": []
                }
            ]
        }
        
        body = json.dumps(payload).encode('utf-8')
        signature = generate_signature(body, FACEBOOK_APP_SECRET)
        
        response = requests.post(
            f"{API_BASE_URL}/webhook/facebook",
            data=body,
            headers={
                "Content-Type": "application/json",
                "X-Hub-Signature": signature
            },
            timeout=5
        )
        
        if response.status_code == 200:
            log_test("Signature", "Valid signature accepted", True, 
                    "Webhook processed successfully")
        else:
            log_test("Signature", "Valid signature accepted", False, 
                    f"Status code: {response.status_code}")
        
        # Test 3.2: Invalid signature
        print("\n3.2 Invalid signature:")
        invalid_signature = "sha1=invalid_signature_hash"
        
        response = requests.post(
            f"{API_BASE_URL}/webhook/facebook",
            data=body,
            headers={
                "Content-Type": "application/json",
                "X-Hub-Signature": invalid_signature
            },
            timeout=5
        )
        
        if response.status_code == 403:
            log_test("Signature", "Invalid signature rejected", True, 
                    "Correctly rejected invalid signature")
        else:
            log_test("Signature", "Invalid signature rejected", False, 
                    f"Expected 403, got {response.status_code}")
        
        # Test 3.3: Missing signature
        print("\n3.3 Missing signature:")
        response = requests.post(
            f"{API_BASE_URL}/webhook/facebook",
            data=body,
            headers={
                "Content-Type": "application/json"
            },
            timeout=5
        )
        
        if response.status_code == 403:
            log_test("Signature", "Missing signature rejected", True, 
                    "Correctly rejected missing signature")
        else:
            log_test("Signature", "Missing signature rejected", False, 
                    f"Expected 403, got {response.status_code}")
        
    except Exception as e:
        log_test("Signature", "Verification test", False, str(e))


# ============================================================================
# TEST 4: Message Reception and Async Processing
# ============================================================================

def test_message_reception():
    """Test 4: Nhận và xử lý tin nhắn bất đồng bộ"""
    print("\n" + "="*70)
    print("TEST 4: Nhận và xử lý tin nhắn bất đồng bộ bình thường")
    print("="*70)
    
    try:
        # Test 4.1: Facebook message reception
        print("\n4.1 Facebook message reception:")
        payload = {
            "object": "page",
            "entry": [
                {
                    "id": "123456789",
                    "time": 1234567890,
                    "messaging": [
                        {
                            "sender": {"id": "user_123"},
                            "recipient": {"id": "page_123"},
                            "timestamp": 1234567890,
                            "message": {
                                "mid": "msg_123",
                                "text": "Hello bot!"
                            }
                        }
                    ]
                }
            ]
        }
        
        body = json.dumps(payload).encode('utf-8')
        signature = generate_signature(body, FACEBOOK_APP_SECRET)
        
        response = requests.post(
            f"{API_BASE_URL}/webhook/facebook",
            data=body,
            headers={
                "Content-Type": "application/json",
                "X-Hub-Signature": signature
            },
            timeout=5
        )
        
        if response.status_code == 200:
            log_test("Message Reception", "Facebook message received", True, 
                    "Message processed asynchronously")
        else:
            log_test("Message Reception", "Facebook message received", False, 
                    f"Status code: {response.status_code}")
        
        # Test 4.2: Instagram message reception
        print("\n4.2 Instagram message reception:")
        payload = {
            "object": "instagram",
            "entry": [
                {
                    "id": "123456789",
                    "time": 1234567890,
                    "messaging": [
                        {
                            "sender": {"id": "user_456"},
                            "recipient": {"id": "page_456"},
                            "timestamp": 1234567890,
                            "message": {
                                "mid": "msg_456",
                                "text": "Hello from Instagram!"
                            }
                        }
                    ]
                }
            ]
        }
        
        body = json.dumps(payload).encode('utf-8')
        signature = generate_signature(body, FACEBOOK_APP_SECRET)
        
        response = requests.post(
            f"{API_BASE_URL}/webhook/instagram",
            data=body,
            headers={
                "Content-Type": "application/json",
                "X-Hub-Signature": signature
            },
            timeout=5
        )
        
        if response.status_code == 200:
            log_test("Message Reception", "Instagram message received", True, 
                    "Message processed asynchronously")
        else:
            log_test("Message Reception", "Instagram message received", False, 
                    f"Status code: {response.status_code}")
        
        # Test 4.3: Multiple messages
        print("\n4.3 Multiple messages in single webhook:")
        payload = {
            "object": "page",
            "entry": [
                {
                    "id": "123456789",
                    "time": 1234567890,
                    "messaging": [
                        {
                            "sender": {"id": "user_123"},
                            "recipient": {"id": "page_123"},
                            "timestamp": 1234567890,
                            "message": {
                                "mid": "msg_1",
                                "text": "Message 1"
                            }
                        },
                        {
                            "sender": {"id": "user_456"},
                            "recipient": {"id": "page_123"},
                            "timestamp": 1234567891,
                            "message": {
                                "mid": "msg_2",
                                "text": "Message 2"
                            }
                        }
                    ]
                }
            ]
        }
        
        body = json.dumps(payload).encode('utf-8')
        signature = generate_signature(body, FACEBOOK_APP_SECRET)
        
        response = requests.post(
            f"{API_BASE_URL}/webhook/facebook",
            data=body,
            headers={
                "Content-Type": "application/json",
                "X-Hub-Signature": signature
            },
            timeout=5
        )
        
        if response.status_code == 200:
            log_test("Message Reception", "Multiple messages processed", True, 
                    "All messages queued for async processing")
        else:
            log_test("Message Reception", "Multiple messages processed", False, 
                    f"Status code: {response.status_code}")
        
    except Exception as e:
        log_test("Message Reception", "Reception test", False, str(e))


# ============================================================================
# TEST 5: Send Text Message
# ============================================================================

def test_send_text_message():
    """Test 5: Gửi tin nhắn văn bản thành công"""
    print("\n" + "="*70)
    print("TEST 5: Gửi tin nhắn văn bản thành công")
    print("="*70)
    
    try:
        # Test 5.1: Check Messenger API class exists
        print("\n5.1 Messenger API class:")
        try:
            from services.messenger_api import MessengerAPI
            log_test("Text Message", "MessengerAPI class exists", True, 
                    "Class imported successfully")
        except ImportError as e:
            log_test("Text Message", "MessengerAPI class exists", False, str(e))
            return
        
        # Test 5.2: Check send_text_message method
        print("\n5.2 send_text_message method:")
        api = MessengerAPI("test_token")
        if hasattr(api, 'send_text_message'):
            log_test("Text Message", "send_text_message method exists", True, 
                    "Method is available")
        else:
            log_test("Text Message", "send_text_message method exists", False, 
                    "Method not found")
        
        # Test 5.3: Check method signature
        print("\n5.3 Method signature:")
        import inspect
        sig = inspect.signature(api.send_text_message)
        params = list(sig.parameters.keys())
        if 'recipient_id' in params and 'text' in params:
            log_test("Text Message", "Method signature correct", True, 
                    f"Parameters: {params}")
        else:
            log_test("Text Message", "Method signature correct", False, 
                    f"Expected recipient_id and text, got {params}")
        
    except Exception as e:
        log_test("Text Message", "Send text test", False, str(e))


# ============================================================================
# TEST 6: Send Quick Reply
# ============================================================================

def test_send_quick_reply():
    """Test 6: Gửi nút Quick Reply thành công"""
    print("\n" + "="*70)
    print("TEST 6: Gửi nút Quick Reply thành công")
    print("="*70)
    
    try:
        # Test 6.1: Check QuickReply class
        print("\n6.1 QuickReply class:")
        try:
            from services.messenger_api import QuickReply
            log_test("Quick Reply", "QuickReply class exists", True, 
                    "Class imported successfully")
        except ImportError as e:
            log_test("Quick Reply", "QuickReply class exists", False, str(e))
            return
        
        # Test 6.2: Create QuickReply instance
        print("\n6.2 Create QuickReply instance:")
        try:
            qr = QuickReply("Yes", "PAYLOAD_YES")
            log_test("Quick Reply", "QuickReply instance created", True, 
                    f"Title: {qr.title}, Payload: {qr.payload}")
        except Exception as e:
            log_test("Quick Reply", "QuickReply instance created", False, str(e))
            return
        
        # Test 6.3: QuickReply to_dict method
        print("\n6.3 QuickReply to_dict method:")
        try:
            qr_dict = qr.to_dict()
            if 'content_type' in qr_dict and 'title' in qr_dict and 'payload' in qr_dict:
                log_test("Quick Reply", "to_dict method works", True, 
                        f"Output: {qr_dict}")
            else:
                log_test("Quick Reply", "to_dict method works", False, 
                        "Missing required fields")
        except Exception as e:
            log_test("Quick Reply", "to_dict method works", False, str(e))
        
        # Test 6.4: Check send_quick_reply method
        print("\n6.4 send_quick_reply method:")
        from services.messenger_api import MessengerAPI
        api = MessengerAPI("test_token")
        if hasattr(api, 'send_quick_reply'):
            log_test("Quick Reply", "send_quick_reply method exists", True, 
                    "Method is available")
        else:
            log_test("Quick Reply", "send_quick_reply method exists", False, 
                    "Method not found")
        
        # Test 6.5: Method signature
        print("\n6.5 Method signature:")
        import inspect
        sig = inspect.signature(api.send_quick_reply)
        params = list(sig.parameters.keys())
        if 'recipient_id' in params and 'text' in params and 'quick_replies' in params:
            log_test("Quick Reply", "Method signature correct", True, 
                    f"Parameters: {params}")
        else:
            log_test("Quick Reply", "Method signature correct", False, 
                    f"Expected recipient_id, text, quick_replies, got {params}")
        
    except Exception as e:
        log_test("Quick Reply", "Quick reply test", False, str(e))


# ============================================================================
# MAIN TEST RUNNER
# ============================================================================

def print_summary():
    """Print test summary"""
    print("\n" + "="*70)
    print("TEST SUMMARY")
    print("="*70)
    
    total_tests = 0
    passed_tests = 0
    
    for category, tests in test_results.items():
        print(f"\n{category}:")
        for test_name, result in tests.items():
            total_tests += 1
            if "PASS" in result["status"]:
                passed_tests += 1
            print(f"  {result['status']} {test_name}")
            if result["message"]:
                print(f"      {result['message']}")
    
    print("\n" + "="*70)
    print(f"TOTAL: {passed_tests}/{total_tests} tests passed")
    print("="*70)
    
    if passed_tests == total_tests:
        print("\n✅ ALL TESTS PASSED!")
        return 0
    else:
        print(f"\n❌ {total_tests - passed_tests} test(s) failed")
        return 1


def main():
    """Run all tests"""
    print("\n" + "="*70)
    print("META API INTEGRATION - COMPREHENSIVE TEST SUITE")
    print("="*70)
    print(f"API Base URL: {API_BASE_URL}")
    print(f"Start Time: {datetime.now().isoformat()}")
    
    # Run all tests
    test_fb_webhook_verification()
    test_ig_webhook_verification()
    test_webhook_signature_verification()
    test_message_reception()
    test_send_text_message()
    test_send_quick_reply()
    
    # Print summary
    return print_summary()


if __name__ == "__main__":
    sys.exit(main())
