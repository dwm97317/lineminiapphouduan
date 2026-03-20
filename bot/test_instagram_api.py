#!/usr/bin/env python3
"""
Test script for Instagram API
"""

import asyncio
import sys
from services.instagram_api import InstagramAPI

# Configuration
INSTAGRAM_PAGE_ACCESS_TOKEN = "your_instagram_page_access_token"
TEST_USER_ID = "test_user_456"
TEST_IMAGE_URL = "https://example.com/image.jpg"


async def test_send_text_message():
    """Test sending text message"""
    print("Testing send text message...")
    try:
        api = InstagramAPI(INSTAGRAM_PAGE_ACCESS_TOKEN)
        
        result = await api.send_text_message(
            recipient_id=TEST_USER_ID,
            text="Hello from Instagram!"
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


async def test_send_image_message():
    """Test sending image message"""
    print("Testing send image message...")
    try:
        api = InstagramAPI(INSTAGRAM_PAGE_ACCESS_TOKEN)
        
        result = await api.send_image_message(
            recipient_id=TEST_USER_ID,
            image_url=TEST_IMAGE_URL,
            caption="Check out this image!"
        )
        
        if "message_id" in result:
            print(f"✓ Image message sent: {result['message_id']}")
            await api.close()
            return True
        else:
            print(f"✗ Failed to send image message: {result}")
            await api.close()
            return False
    except Exception as e:
        print(f"✗ Error: {e}")
        return False


async def test_get_user_profile():
    """Test getting user profile"""
    print("Testing get user profile...")
    try:
        api = InstagramAPI(INSTAGRAM_PAGE_ACCESS_TOKEN)
        
        result = await api.get_user_profile(user_id=TEST_USER_ID)
        
        if "name" in result or "id" in result:
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


async def test_mark_message_as_seen():
    """Test marking message as seen"""
    print("Testing mark message as seen...")
    try:
        api = InstagramAPI(INSTAGRAM_PAGE_ACCESS_TOKEN)
        
        result = await api.mark_message_as_seen(message_id=TEST_USER_ID)
        
        if "success" in result or "recipient_id" in result:
            print(f"✓ Message marked as seen")
            await api.close()
            return True
        else:
            print(f"✗ Failed to mark message as seen: {result}")
            await api.close()
            return False
    except Exception as e:
        print(f"✗ Error: {e}")
        return False


async def main():
    """Run all tests"""
    print("=" * 60)
    print("Instagram API Tests")
    print("=" * 60)
    print()
    
    results = {
        "Send Text Message": await test_send_text_message(),
        "Send Image Message": await test_send_image_message(),
        "Get User Profile": await test_get_user_profile(),
        "Mark Message as Seen": await test_mark_message_as_seen(),
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
        print("✅ All Instagram API tests passed!")
        return 0
    else:
        print("❌ Some Instagram API tests failed.")
        return 1


if __name__ == "__main__":
    sys.exit(asyncio.run(main()))
