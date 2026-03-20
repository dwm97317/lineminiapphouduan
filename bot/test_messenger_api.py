#!/usr/bin/env python3
"""
Test script for Messenger API
"""

import asyncio
import sys
from services.messenger_api import MessengerAPI, QuickReply

# Configuration
FACEBOOK_PAGE_ACCESS_TOKEN = "your_facebook_page_access_token"
TEST_USER_ID = "test_user_123"


async def test_send_text_message():
    """Test sending text message"""
    print("Testing send text message...")
    try:
        api = MessengerAPI(FACEBOOK_PAGE_ACCESS_TOKEN)
        
        result = await api.send_text_message(
            recipient_id=TEST_USER_ID,
            text="Hello! This is a test message."
        )
        
        if "message_id" in result:
            print(f"✓ Text message sent: {result['message_id']}")
            await api.close()
            return True
        else:
            print(f"✗ Failed to send text message: {result}")
            await api.close()
            return False
    except Exception as e:
        print(f"✗ Error: {e}")
        return False


async def test_send_quick_reply():
    """Test sending quick reply"""
    print("Testing send quick reply...")
    try:
        api = MessengerAPI(FACEBOOK_PAGE_ACCESS_TOKEN)
        
        quick_replies = [
            QuickReply("Yes", "PAYLOAD_YES"),
            QuickReply("No", "PAYLOAD_NO"),
            QuickReply("Maybe", "PAYLOAD_MAYBE"),
        ]
        
        result = await api.send_quick_reply(
            recipient_id=TEST_USER_ID,
            text="Do you like this?",
            quick_replies=quick_replies
        )
        
        if "message_id" in result:
            print(f"✓ Quick reply sent: {result['message_id']}")
            await api.close()
            return True
        else:
            print(f"✗ Failed to send quick reply: {result}")
            await api.close()
            return False
    except Exception as e:
        print(f"✗ Error: {e}")
        return False


async def test_send_typing_indicator():
    """Test sending typing indicator"""
    print("Testing send typing indicator...")
    try:
        api = MessengerAPI(FACEBOOK_PAGE_ACCESS_TOKEN)
        
        result = await api.send_typing_indicator(recipient_id=TEST_USER_ID)
        
        if "recipient_id" in result or result.get("success"):
            print(f"✓ Typing indicator sent")
            await api.close()
            return True
        else:
            print(f"✗ Failed to send typing indicator: {result}")
            await api.close()
            return False
    except Exception as e:
        print(f"✗ Error: {e}")
        return False


async def test_get_user_profile():
    """Test getting user profile"""
    print("Testing get user profile...")
    try:
        api = MessengerAPI(FACEBOOK_PAGE_ACCESS_TOKEN)
        
        result = await api.get_user_profile(user_id=TEST_USER_ID)
        
        if "first_name" in result or "id" in result:
            print(f"✓ User profile retrieved: {result}")
            await api.close()
            return True
        else:
            print(f"✗ Failed to get user profile: {result}")
            await api.close()
            return False
    except Exception as e:
        print(f"✗ Error: {e}")
        return False


async def main():
    """Run all tests"""
    print("=" * 60)
    print("Messenger API Tests")
    print("=" * 60)
    print()
    
    results = {
        "Send Text Message": await test_send_text_message(),
        "Send Quick Reply": await test_send_quick_reply(),
        "Send Typing Indicator": await test_send_typing_indicator(),
        "Get User Profile": await test_get_user_profile(),
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
        print("✅ All Messenger API tests passed!")
        return 0
    else:
        print("❌ Some Messenger API tests failed.")
        return 1


if __name__ == "__main__":
    sys.exit(asyncio.run(main()))
