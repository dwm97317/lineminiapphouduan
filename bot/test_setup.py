#!/usr/bin/env python3
"""
Test script to verify Bot API setup
"""

import sys
import requests
import redis
from config import settings

def test_api_health():
    """Test API health endpoint"""
    print("Testing API health endpoint...")
    try:
        response = requests.get("http://localhost:8000/health", timeout=5)
        if response.status_code == 200 and response.json().get("status") == "ok":
            print("✓ API health check passed")
            return True
        else:
            print(f"✗ API health check failed: {response.text}")
            return False
    except Exception as e:
        print(f"✗ API health check error: {e}")
        return False

def test_redis_connection():
    """Test Redis connection"""
    print("Testing Redis connection...")
    try:
        client = redis.Redis(
            host=settings.REDIS_HOST,
            port=settings.REDIS_PORT,
            db=settings.REDIS_DB,
            password=settings.REDIS_PASSWORD if settings.REDIS_PASSWORD else None,
            decode_responses=True
        )
        if client.ping():
            print("✓ Redis connection successful")
            return True
    except Exception as e:
        print(f"✗ Redis connection failed: {e}")
        return False

def test_database_connection():
    """Test database connection"""
    print("Testing database connection...")
    try:
        import mysql.connector
        conn = mysql.connector.connect(
            host=settings.DB_HOST,
            port=settings.DB_PORT,
            user=settings.DB_USER,
            password=settings.DB_PASSWORD,
            database=settings.DB_NAME
        )
        cursor = conn.cursor()
        cursor.execute("SHOW TABLES")
        tables = cursor.fetchall()
        cursor.close()
        conn.close()
        
        if len(tables) >= 4:
            print(f"✓ Database connection successful ({len(tables)} tables found)")
            return True
        else:
            print(f"✗ Database has only {len(tables)} tables, expected at least 4")
            return False
    except Exception as e:
        print(f"✗ Database connection failed: {e}")
        return False

def main():
    """Run all tests"""
    print("=" * 50)
    print("Bot API Setup Verification")
    print("=" * 50)
    print()
    
    results = {
        "API Health": test_api_health(),
        "Redis Connection": test_redis_connection(),
        "Database Connection": test_database_connection(),
    }
    
    print()
    print("=" * 50)
    print("Test Results:")
    print("=" * 50)
    
    for test_name, result in results.items():
        status = "✓ PASS" if result else "✗ FAIL"
        print(f"{test_name}: {status}")
    
    print()
    
    if all(results.values()):
        print("✅ All tests passed! Setup is complete.")
        return 0
    else:
        print("❌ Some tests failed. Please check the errors above.")
        return 1

if __name__ == "__main__":
    sys.exit(main())
