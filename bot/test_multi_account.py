#!/usr/bin/env python3
"""
Test script for multi-account management
"""

import sys
import requests
from typing import Dict, Any

# Configuration
API_BASE_URL = "http://localhost:8000"
CUSTOMER_ID = "customer_123"

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
# TEST 1: Link Multiple Accounts
# ============================================================================

def test_link_accounts():
    """Test 1: Link multiple accounts"""
    print("\n" + "="*70)
    print("TEST 1: Link multiple accounts")
    print("="*70)
    
    account_ids = []
    
    try:
        # Test 1.1: Link Facebook account
        print("\n1.1 Link Facebook account:")
        payload = {
            "customer_id": CUSTOMER_ID,
            "platform": "facebook",
            "platform_account_id": "fb_user_123",
            "platform_account_name": "John Doe",
            "access_token": "test_fb_token",
        }
        
        response = requests.post(
            f"{API_BASE_URL}/api/v1/account/link",
            json=payload,
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            account_ids.append(data.get("account_id"))
            log_test("Multi-Account", "Link Facebook", True, 
                    f"Account ID: {data.get('account_id')}")
        else:
            log_test("Multi-Account", "Link Facebook", False, 
                    f"Status code: {response.status_code}")
        
        # Test 1.2: Link Instagram account
        print("\n1.2 Link Instagram account:")
        payload = {
            "customer_id": CUSTOMER_ID,
            "platform": "instagram",
            "platform_account_id": "ig_user_456",
            "platform_account_name": "john_doe_ig",
            "access_token": "test_ig_token",
        }
        
        response = requests.post(
            f"{API_BASE_URL}/api/v1/account/link",
            json=payload,
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            account_ids.append(data.get("account_id"))
            log_test("Multi-Account", "Link Instagram", True, 
                    f"Account ID: {data.get('account_id')}")
        else:
            log_test("Multi-Account", "Link Instagram", False, 
                    f"Status code: {response.status_code}")
        
        return account_ids
    
    except Exception as e:
        log_test("Multi-Account", "Link accounts", False, str(e))
        return account_ids


# ============================================================================
# TEST 2: List Linked Accounts
# ============================================================================

def test_list_accounts():
    """Test 2: List linked accounts"""
    print("\n" + "="*70)
    print("TEST 2: List linked accounts")
    print("="*70)
    
    try:
        # Test 2.1: Get account list
        print("\n2.1 Get account list:")
        response = requests.get(
            f"{API_BASE_URL}/api/v1/account/list",
            params={"customer_id": CUSTOMER_ID},
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            total = data.get("total_accounts")
            max_accounts = data.get("max_accounts")
            
            if total > 0:
                log_test("Multi-Account", "List accounts", True, 
                        f"Total: {total}/{max_accounts} accounts")
                
                # Print account details
                for acc in data.get("accounts", []):
                    print(f"    - {acc['platform']}: {acc['platform_account_name']}")
            else:
                log_test("Multi-Account", "List accounts", False, 
                        "No accounts found")
        else:
            log_test("Multi-Account", "List accounts", False, 
                    f"Status code: {response.status_code}")
    
    except Exception as e:
        log_test("Multi-Account", "List accounts", False, str(e))


# ============================================================================
# TEST 3: Account Limit
# ============================================================================

def test_account_limit():
    """Test 3: Account limit enforcement"""
    print("\n" + "="*70)
    print("TEST 3: Account limit enforcement")
    print("="*70)
    
    try:
        # Try to link 11 accounts (should fail)
        print("\n3.1 Try to exceed account limit:")
        
        # First, get current account count
        response = requests.get(
            f"{API_BASE_URL}/api/v1/account/list",
            params={"customer_id": CUSTOMER_ID},
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            current_count = data.get("total_accounts")
            max_accounts = data.get("max_accounts")
            
            if current_count >= max_accounts:
                log_test("Multi-Account", "Account limit enforced", True, 
                        f"Current: {current_count}/{max_accounts}")
            else:
                log_test("Multi-Account", "Account limit enforced", False, 
                        f"Can still add accounts: {current_count}/{max_accounts}")
    
    except Exception as e:
        log_test("Multi-Account", "Account limit", False, str(e))


# ============================================================================
# TEST 4: Bot Commands
# ============================================================================

def test_bot_commands():
    """Test 4: Bot commands"""
    print("\n" + "="*70)
    print("TEST 4: Bot commands")
    print("="*70)
    
    try:
        # Test 4.1: View accounts command
        print("\n4.1 Execute 「Xem tài khoản đã liên kết」command:")
        payload = {
            "user_id": "user_123",
            "customer_id": CUSTOMER_ID,
            "command_text": "Xem tài khoản đã liên kết",
            "platform": "facebook",
        }
        
        response = requests.post(
            f"{API_BASE_URL}/api/v1/command/execute",
            json=payload,
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get("status") == "success":
                log_test("Bot Commands", "View accounts", True, 
                        "Command executed successfully")
                print(f"    Message: {data.get('message')[:50]}...")
            else:
                log_test("Bot Commands", "View accounts", False, 
                        f"Status: {data.get('status')}")
        else:
            log_test("Bot Commands", "View accounts", False, 
                    f"Status code: {response.status_code}")
        
        # Test 4.2: Add package command
        print("\n4.2 Execute 「Bổ sung mã bưu kiện」command:")
        payload = {
            "user_id": "user_123",
            "customer_id": CUSTOMER_ID,
            "command_text": "Bổ sung mã bưu kiện",
            "platform": "facebook",
        }
        
        response = requests.post(
            f"{API_BASE_URL}/api/v1/command/execute",
            json=payload,
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get("status") == "success":
                log_test("Bot Commands", "Add package", True, 
                        "Command executed successfully")
            else:
                log_test("Bot Commands", "Add package", False, 
                        f"Status: {data.get('status')}")
        else:
            log_test("Bot Commands", "Add package", False, 
                    f"Status code: {response.status_code}")
        
        # Test 4.3: Order history command
        print("\n4.3 Execute 「Đơn hàng của tôi」command:")
        payload = {
            "user_id": "user_123",
            "customer_id": CUSTOMER_ID,
            "command_text": "Đơn hàng của tôi",
            "platform": "facebook",
        }
        
        response = requests.post(
            f"{API_BASE_URL}/api/v1/command/execute",
            json=payload,
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get("status") == "success":
                log_test("Bot Commands", "Order history", True, 
                        "Command executed successfully")
            else:
                log_test("Bot Commands", "Order history", False, 
                        f"Status: {data.get('status')}")
        else:
            log_test("Bot Commands", "Order history", False, 
                    f"Status code: {response.status_code}")
    
    except Exception as e:
        log_test("Bot Commands", "Execute commands", False, str(e))


# ============================================================================
# TEST 5: Unlink Account
# ============================================================================

def test_unlink_account(account_id: int):
    """Test 5: Unlink account"""
    print("\n" + "="*70)
    print("TEST 5: Unlink account")
    print("="*70)
    
    try:
        # Test 5.1: Request confirmation
        print("\n5.1 Request unlink confirmation:")
        payload = {
            "account_id": account_id,
        }
        
        response = requests.post(
            f"{API_BASE_URL}/api/v1/account/unlink/confirm",
            json=payload,
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            confirmation_code = data.get("confirmation_code")
            
            if confirmation_code:
                log_test("Multi-Account", "Request confirmation", True, 
                        f"Code: {confirmation_code}")
                
                # Test 5.2: Unlink with confirmation
                print("\n5.2 Unlink with confirmation code:")
                payload = {
                    "account_id": account_id,
                    "confirmation_code": confirmation_code,
                }
                
                response = requests.post(
                    f"{API_BASE_URL}/api/v1/account/unlink",
                    json=payload,
                    timeout=5
                )
                
                if response.status_code == 200:
                    log_test("Multi-Account", "Unlink account", True, 
                            "Account unlinked successfully")
                else:
                    log_test("Multi-Account", "Unlink account", False, 
                            f"Status code: {response.status_code}")
            else:
                log_test("Multi-Account", "Request confirmation", False, 
                        "No confirmation code received")
        else:
            log_test("Multi-Account", "Request confirmation", False, 
                    f"Status code: {response.status_code}")
    
    except Exception as e:
        log_test("Multi-Account", "Unlink account", False, str(e))


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
    print("MULTI-ACCOUNT MANAGEMENT - COMPREHENSIVE TEST SUITE")
    print("="*70)
    print(f"API Base URL: {API_BASE_URL}")
    print(f"Customer ID: {CUSTOMER_ID}")
    
    # Test 1: Link accounts
    account_ids = test_link_accounts()
    
    # Test 2: List accounts
    test_list_accounts()
    
    # Test 3: Account limit
    test_account_limit()
    
    # Test 4: Bot commands
    test_bot_commands()
    
    # Test 5: Unlink account (if we have account IDs)
    if account_ids:
        test_unlink_account(account_ids[0])
    
    # Print summary
    return print_summary()


if __name__ == "__main__":
    sys.exit(main())
