#!/usr/bin/env python3
"""
Test script for order session functionality
"""

import sys
import asyncio
import requests
from typing import Dict, Any

# Configuration
API_BASE_URL = "http://localhost:8000"

# Test results
test_results: Dict[str, Dict[str, Any]] = {}


def log_test(category: str, test_name: str, status: bool, message: str = ""):
    """Log test result"""
    if category not in test_results:
        test_results[category] = {}
    
    test_results[category][test_name] = {
        "status": "✓ PASS" if status else "✗ FAIL",
        "message": message,
    }
    
    status_icon = "✓" if status else "✗"
    print(f"  {status_icon} {test_name}")
    if message:
        print(f"    → {message}")


# ============================================================================
# TEST 1: Create Order Session
# ============================================================================

def test_create_order_session():
    """Test 1: Create order session"""
    print("\n" + "="*70)
    print("TEST 1: Create order session")
    print("="*70)
    
    try:
        # Test 1.1: Create session
        print("\n1.1 Create new session:")
        payload = {
            "platform_account_id": 1,
            "user_id": "user_123",
            "user_name": "John Doe",
            "platform": "facebook",
        }
        
        response = requests.post(
            f"{API_BASE_URL}/api/v1/order-session",
            json=payload,
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            session_id = data.get("session_id")
            state = data.get("state")
            
            if session_id and state == "collecting":
                log_test("Order Session", "Create session", True, 
                        f"Session ID: {session_id}")
                return session_id
            else:
                log_test("Order Session", "Create session", False, 
                        f"Invalid response: {data}")
                return None
        else:
            log_test("Order Session", "Create session", False, 
                    f"Status code: {response.status_code}")
            return None
    
    except Exception as e:
        log_test("Order Session", "Create session", False, str(e))
        return None


# ============================================================================
# TEST 2: Get Order Session
# ============================================================================

def test_get_order_session(session_id: str):
    """Test 2: Get order session"""
    print("\n" + "="*70)
    print("TEST 2: Get order session")
    print("="*70)
    
    try:
        # Test 2.1: Get session
        print("\n2.1 Get session by ID:")
        response = requests.get(
            f"{API_BASE_URL}/api/v1/order-session/{session_id}",
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get("session_id") == session_id:
                log_test("Order Session", "Get session", True, 
                        f"State: {data.get('state')}")
                return True
            else:
                log_test("Order Session", "Get session", False, 
                        "Session ID mismatch")
                return False
        else:
            log_test("Order Session", "Get session", False, 
                    f"Status code: {response.status_code}")
            return False
    
    except Exception as e:
        log_test("Order Session", "Get session", False, str(e))
        return False


# ============================================================================
# TEST 3: Add Message and Extract Info
# ============================================================================

def test_add_message_and_extract(session_id: str):
    """Test 3: Add message and extract information"""
    print("\n" + "="*70)
    print("TEST 3: Add message and extract information")
    print("="*70)
    
    try:
        # Test 3.1: Message with VND amount
        print("\n3.1 Message with VND amount:")
        response = requests.post(
            f"{API_BASE_URL}/api/v1/order-session/{session_id}/message",
            params={
                "message_text": "Tôi muốn gửi 500.000 đ",
                "message_id": "msg_1",
            },
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            analysis = data.get("analysis", {})
            
            if analysis.get("has_amount"):
                log_test("Info Extractor", "Extract VND amount", True, 
                        f"Amount: {analysis['extracted_info'].get('amount_vnd')} VND")
            else:
                log_test("Info Extractor", "Extract VND amount", False, 
                        "Amount not extracted")
        else:
            log_test("Info Extractor", "Extract VND amount", False, 
                    f"Status code: {response.status_code}")
        
        # Test 3.2: Message with date
        print("\n3.2 Message with date:")
        response = requests.post(
            f"{API_BASE_URL}/api/v1/order-session/{session_id}/message",
            params={
                "message_text": "Gửi vào ngày 15/01/2024",
                "message_id": "msg_2",
            },
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            analysis = data.get("analysis", {})
            
            if analysis.get("has_date"):
                log_test("Info Extractor", "Extract date", True, 
                        f"Date: {analysis['extracted_info'].get('date')}")
            else:
                log_test("Info Extractor", "Extract date", False, 
                        "Date not extracted")
        else:
            log_test("Info Extractor", "Extract date", False, 
                    f"Status code: {response.status_code}")
        
        # Test 3.3: Message with package code
        print("\n3.3 Message with package code:")
        response = requests.post(
            f"{API_BASE_URL}/api/v1/order-session/{session_id}/message",
            params={
                "message_text": "Mã bưu kiện: VN1234567890VN",
                "message_id": "msg_3",
            },
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            analysis = data.get("analysis", {})
            
            if analysis.get("has_package"):
                log_test("Info Extractor", "Extract package code", True, 
                        f"Code: {analysis['extracted_info'].get('package_code')}")
            else:
                log_test("Info Extractor", "Extract package code", False, 
                        "Package code not extracted")
        else:
            log_test("Info Extractor", "Extract package code", False, 
                    f"Status code: {response.status_code}")
    
    except Exception as e:
        log_test("Info Extractor", "Add message", False, str(e))


# ============================================================================
# TEST 4: State Machine Transitions
# ============================================================================

def test_state_machine(session_id: str):
    """Test 4: State machine transitions"""
    print("\n" + "="*70)
    print("TEST 4: State machine transitions")
    print("="*70)
    
    try:
        # Test 4.1: Transition to ready
        print("\n4.1 Transition to ready:")
        payload = {
            "state": "ready",
            "reason": "Information collected",
        }
        
        response = requests.patch(
            f"{API_BASE_URL}/api/v1/order-session/{session_id}/status",
            json=payload,
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get("state") == "ready":
                log_test("State Machine", "Transition to ready", True, 
                        "State changed to ready")
            else:
                log_test("State Machine", "Transition to ready", False, 
                        f"State: {data.get('state')}")
        else:
            log_test("State Machine", "Transition to ready", False, 
                    f"Status code: {response.status_code}")
        
        # Test 4.2: Transition to bound
        print("\n4.2 Transition to bound:")
        payload = {
            "state": "bound",
            "reason": "User confirmed",
        }
        
        response = requests.patch(
            f"{API_BASE_URL}/api/v1/order-session/{session_id}/status",
            json=payload,
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get("state") == "bound":
                log_test("State Machine", "Transition to bound", True, 
                        "State changed to bound")
            else:
                log_test("State Machine", "Transition to bound", False, 
                        f"State: {data.get('state')}")
        else:
            log_test("State Machine", "Transition to bound", False, 
                    f"Status code: {response.status_code}")
    
    except Exception as e:
        log_test("State Machine", "Transitions", False, str(e))


# ============================================================================
# TEST 5: Redis Cache
# ============================================================================

def test_redis_cache(session_id: str):
    """Test 5: Redis cache"""
    print("\n" + "="*70)
    print("TEST 5: Redis cache")
    print("="*70)
    
    try:
        # Test 5.1: Check TTL
        print("\n5.1 Check session TTL:")
        response = requests.get(
            f"{API_BASE_URL}/api/v1/order-session/{session_id}/ttl",
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            ttl_seconds = data.get("ttl_seconds")
            ttl_hours = data.get("ttl_hours")
            
            if ttl_seconds and ttl_seconds > 0:
                log_test("Redis Cache", "Session cached", True, 
                        f"TTL: {ttl_hours:.1f} hours ({ttl_seconds} seconds)")
            else:
                log_test("Redis Cache", "Session cached", False, 
                        "No TTL found")
        else:
            log_test("Redis Cache", "Session cached", False, 
                    f"Status code: {response.status_code}")
    
    except Exception as e:
        log_test("Redis Cache", "Check TTL", False, str(e))


# ============================================================================
# TEST 6: Close Session
# ============================================================================

def test_close_session(session_id: str):
    """Test 6: Close session"""
    print("\n" + "="*70)
    print("TEST 6: Close session")
    print("="*70)
    
    try:
        # Test 6.1: Close session
        print("\n6.1 Close session:")
        response = requests.post(
            f"{API_BASE_URL}/api/v1/order-session/{session_id}/close",
            params={"reason": "Order completed"},
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get("status") == "ok":
                log_test("Order Session", "Close session", True, 
                        "Session closed successfully")
            else:
                log_test("Order Session", "Close session", False, 
                        f"Response: {data}")
        else:
            log_test("Order Session", "Close session", False, 
                    f"Status code: {response.status_code}")
    
    except Exception as e:
        log_test("Order Session", "Close session", False, str(e))


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
    print("ORDER SESSION - COMPREHENSIVE TEST SUITE")
    print("="*70)
    print(f"API Base URL: {API_BASE_URL}")
    
    # Test 1: Create session
    session_id = test_create_order_session()
    if not session_id:
        print("\n❌ Cannot continue without session ID")
        return 1
    
    # Test 2: Get session
    test_get_order_session(session_id)
    
    # Test 3: Add message and extract info
    test_add_message_and_extract(session_id)
    
    # Test 4: State machine
    test_state_machine(session_id)
    
    # Test 5: Redis cache
    test_redis_cache(session_id)
    
    # Test 6: Close session
    test_close_session(session_id)
    
    # Print summary
    return print_summary()


if __name__ == "__main__":
    sys.exit(main())
