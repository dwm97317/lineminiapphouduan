#!/usr/bin/env python3
"""
Test script for webhook integration
"""

import sys
import json
import hmac
import hashlib
import requests
from typing import Dict, Any

# Configuration
API_BASE_URL = "http://localhost:8000"
FACEBOOK_APP_SECRET = "your_facebook_app_secret"
FACEBOOK_VERIFY_TOKEN = "your_facebook_verify_token"
INSTAGRAM_VERIFY_TOKEN = "your_instagram_verify_token"


def generate_signature(body: bytes, app_secret: str) -> str:
    """Generate webhook signature"""
    hash_value = hmac.new(
        app_secret.encode('utf-8'),
        body,
        hashlib.sha1
    ).hexdigest()
    return f"sha1={hash_value}"


def test_facebook_webhook_verification():
    """Test Facebook webhook verification"""
    print("Testing Facebook webhook verification...")
    try:
        response = requests.get(
            f"{API_BASE_URL}/webhook/facebook",
            params={
                "hub.mode": "subscribe",
                "hub.challenge": "test_challenge_123",
                "hub.verify_token": FACEBOOK_VERIFY_TOKEN
            }
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get("hub.challenge") == "test_challenge_123":
                print("✓ Facebook webhook verification passed")
                return True
            else:
                print("✗ Challenge mismatch")
                return False
        else:
            print(f"✗ Facebook webhook verification failed: {response.status_code}")
            return False
    except Exception as e:
        print(f"✗ Error: {e}")
        return False


def test_instagram_webhook_verification():
    """Test Instagram webhook verification"""
    print("Testing Instagram webhook verification...")
    try:
        response = requests.get(
            f"{API_BASE_URL}/webhook/instagram",
            params={
                "hub.mode": "subscribe",
                "hub.challenge": "test_challenge_456",
                "hub.verify_token": INSTAGRAM_VERIFY_TOKEN
            }
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get("hub.challenge") == "test_challenge_456":
                print("✓ Instagram webhook verification passed")
                return True
            else:
                print("✗ Challenge mismatch")
                return False
        else:
            print(f"✗ Instagram webhook verification failed: {response.status_code}")
            return False
    except Exception as e:
        print(f"✗ Error: {e}")
        return False


def test_facebook_message_webhook():
    """Test Facebook message webhook"""
    print("Testing Facebook message webhook...")
    try:
        # Create test message payload
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
            }
        )
        
        if response.status_code == 200:
            print("✓ Facebook message webhook received")
            return True
        else:
            print(f"✗ Facebook message webhook failed: {response.status_code}")
            print(f"  Response: {response.text}")
            return False
    except Exception as e:
        print(f"✗ Error: {e}")
        return False


def test_instagram_message_webhook():
    """Test Instagram message webhook"""
    print("Testing Instagram message webhook...")
    try:
        # Create test message payload
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
            }
        )
        
        if response.status_code == 200:
            print("✓ Instagram message webhook received")
            return True
        else:
            print(f"✗ Instagram message webhook failed: {response.status_code}")
            print(f"  Response: {response.text}")
            return False
    except Exception as e:
        print(f"✗ Error: {e}")
        return False


def test_invalid_signature():
    """Test webhook with invalid signature"""
    print("Testing webhook with invalid signature...")
    try:
        payload = {
            "object": "page",
            "entry": []
        }
        
        body = json.dumps(payload).encode('utf-8')
        invalid_signature = "sha1=invalid_signature"
        
        response = requests.post(
            f"{API_BASE_URL}/webhook/facebook",
            data=body,
            headers={
                "Content-Type": "application/json",
                "X-Hub-Signature": invalid_signature
            }
        )
        
        if response.status_code == 403:
            print("✓ Invalid signature correctly rejected")
            return True
        else:
            print(f"✗ Invalid signature not rejected: {response.status_code}")
            return False
    except Exception as e:
        print(f"✗ Error: {e}")
        return False


def main():
    """Run all tests"""
    print("=" * 60)
    print("Webhook Integration Tests")
    print("=" * 60)
    print()
    
    results = {
        "Facebook Webhook Verification": test_facebook_webhook_verification(),
        "Instagram Webhook Verification": test_instagram_webhook_verification(),
        "Facebook Message Webhook": test_facebook_message_webhook(),
        "Instagram Message Webhook": test_instagram_message_webhook(),
        "Invalid Signature Rejection": test_invalid_signature(),
    }
    
    print()
    print("=" * 60)
    print("Test Results:")
    print("=" * 60)
    
    for test_name, result in results.items():
        status = "✓ PASS" if result else "✗ FAIL"
        print(f"{test_name}: {status}")
    
    print()
    
    if all(results.values()):
        print("✅ All webhook tests passed!")
        return 0
    else:
        print("❌ Some webhook tests failed.")
        return 1


if __name__ == "__main__":
    sys.exit(main())
