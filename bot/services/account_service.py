"""
Account management service for multi-account support
"""

import logging
import json
import uuid
import secrets
from typing import Optional, List, Dict, Any
from datetime import datetime, timedelta
import redis

from models.account import (
    PlatformAccount,
    PlatformType,
    AccountStatus,
)

logger = logging.getLogger(__name__)

# Constants
MAX_ACCOUNTS_PER_CUSTOMER = 10
UNLINK_CONFIRMATION_TTL = 300  # 5 minutes
ACCOUNT_CACHE_TTL = 24 * 60 * 60  # 24 hours


class AccountService:
    """Service for managing platform accounts"""
    
    def __init__(self, redis_client: redis.Redis):
        """Initialize account service"""
        self.redis_client = redis_client
    
    def link_account(
        self,
        customer_id: str,
        platform: PlatformType,
        platform_account_id: str,
        platform_account_name: Optional[str] = None,
        access_token: Optional[str] = None,
    ) -> PlatformAccount:
        """Link new platform account to customer"""
        
        # Check account limit
        existing_accounts = self.get_customer_accounts(customer_id)
        if len(existing_accounts) >= MAX_ACCOUNTS_PER_CUSTOMER:
            raise ValueError(
                f"Maximum {MAX_ACCOUNTS_PER_CUSTOMER} accounts per customer. "
                "Please contact support to link more accounts."
            )
        
        # Check if account already linked
        for account in existing_accounts:
            if (account.platform == platform and 
                account.platform_account_id == platform_account_id):
                raise ValueError(f"Account already linked: {platform_account_id}")
        
        # Create account
        account = PlatformAccount(
            account_id=None,  # Will be set by database
            customer_id=customer_id,
            platform=platform,
            platform_account_id=platform_account_id,
            platform_account_name=platform_account_name,
            access_token=access_token,
            status=AccountStatus.ACTIVE,
        )
        
        # TODO: Save to database
        # For now, cache in Redis
        self._cache_account(account)
        
        logger.info(
            f"Linked account: {platform_account_id}",
            extra={
                "customer_id": customer_id,
                "platform": platform.value,
                "platform_account_id": platform_account_id,
            }
        )
        
        return account
    
    def unlink_account(
        self,
        account_id: int,
        confirmation_code: str,
    ) -> bool:
        """Unlink platform account"""
        
        # Verify confirmation code
        if not self._verify_unlink_confirmation(account_id, confirmation_code):
            raise ValueError("Invalid or expired confirmation code")
        
        # TODO: Delete from database
        # For now, remove from cache
        self._delete_account_cache(account_id)
        
        logger.info(f"Unlinked account: {account_id}")
        
        return True
    
    def get_customer_accounts(self, customer_id: str) -> List[PlatformAccount]:
        """Get all accounts for customer"""
        
        # Try to get from cache first
        cached = self._get_cached_customer_accounts(customer_id)
        if cached:
            logger.debug(f"Retrieved {len(cached)} accounts from cache for {customer_id}")
            return cached
        
        # TODO: Get from database
        logger.warning(f"No accounts found for customer: {customer_id}")
        return []
    
    def get_account(self, account_id: int) -> Optional[PlatformAccount]:
        """Get account by ID"""
        
        # Try to get from cache
        cached = self._get_cached_account(account_id)
        if cached:
            return cached
        
        # TODO: Get from database
        logger.warning(f"Account not found: {account_id}")
        return None
    
    def get_account_by_platform_id(
        self,
        platform: PlatformType,
        platform_account_id: str,
    ) -> Optional[PlatformAccount]:
        """Get account by platform ID"""
        
        # TODO: Query database
        logger.debug(f"Looking up account: {platform.value}/{platform_account_id}")
        return None
    
    def request_unlink_confirmation(self, account_id: int) -> Dict[str, Any]:
        """Request confirmation code for unlinking account"""
        
        # Generate confirmation code
        confirmation_code = secrets.token_urlsafe(8)
        expires_at = datetime.utcnow() + timedelta(seconds=UNLINK_CONFIRMATION_TTL)
        
        # Cache confirmation code
        cache_key = f"unlink_confirmation:{account_id}"
        self.redis_client.setex(
            cache_key,
            UNLINK_CONFIRMATION_TTL,
            confirmation_code
        )
        
        logger.info(f"Generated unlink confirmation for account: {account_id}")
        
        return {
            "account_id": account_id,
            "confirmation_code": confirmation_code,
            "expires_at": expires_at.isoformat(),
            "message": f"Confirmation code: {confirmation_code}. Valid for 5 minutes.",
        }
    
    def _verify_unlink_confirmation(self, account_id: int, confirmation_code: str) -> bool:
        """Verify unlink confirmation code"""
        try:
            cache_key = f"unlink_confirmation:{account_id}"
            cached_code = self.redis_client.get(cache_key)
            
            if not cached_code:
                logger.warning(f"No confirmation code found for account: {account_id}")
                return False
            
            is_valid = cached_code == confirmation_code
            
            if is_valid:
                # Delete confirmation code after verification
                self.redis_client.delete(cache_key)
            
            return is_valid
        
        except Exception as e:
            logger.error(f"Error verifying confirmation code: {e}")
            return False
    
    def _cache_account(self, account: PlatformAccount, ttl: int = ACCOUNT_CACHE_TTL):
        """Cache account in Redis"""
        try:
            cache_key = f"account:{account.account_id}"
            account_data = json.dumps(account.to_dict(), default=str)
            self.redis_client.setex(cache_key, ttl, account_data)
            
            # Also cache by customer ID
            customer_key = f"customer_accounts:{account.customer_id}"
            self.redis_client.sadd(customer_key, account.account_id)
            
            logger.debug(f"Cached account: {account.account_id}")
        except Exception as e:
            logger.error(f"Error caching account: {e}")
    
    def _get_cached_account(self, account_id: int) -> Optional[PlatformAccount]:
        """Get account from cache"""
        try:
            cache_key = f"account:{account_id}"
            cached_data = self.redis_client.get(cache_key)
            
            if not cached_data:
                return None
            
            account_dict = json.loads(cached_data)
            
            # Reconstruct account object
            account = PlatformAccount(
                account_id=account_dict["account_id"],
                customer_id=account_dict["customer_id"],
                platform=PlatformType(account_dict["platform"]),
                platform_account_id=account_dict["platform_account_id"],
                platform_account_name=account_dict.get("platform_account_name"),
                status=AccountStatus(account_dict["status"]),
                created_at=datetime.fromisoformat(account_dict["created_at"]),
                updated_at=datetime.fromisoformat(account_dict["updated_at"]),
            )
            
            return account
        
        except Exception as e:
            logger.error(f"Error retrieving cached account: {e}")
            return None
    
    def _get_cached_customer_accounts(self, customer_id: str) -> List[PlatformAccount]:
        """Get all accounts for customer from cache"""
        try:
            customer_key = f"customer_accounts:{customer_id}"
            account_ids = self.redis_client.smembers(customer_key)
            
            accounts = []
            for account_id in account_ids:
                account = self._get_cached_account(int(account_id))
                if account:
                    accounts.append(account)
            
            return accounts
        
        except Exception as e:
            logger.error(f"Error retrieving cached customer accounts: {e}")
            return []
    
    def _delete_account_cache(self, account_id: int):
        """Delete account from cache"""
        try:
            cache_key = f"account:{account_id}"
            self.redis_client.delete(cache_key)
            logger.debug(f"Deleted cached account: {account_id}")
        except Exception as e:
            logger.error(f"Error deleting cached account: {e}")
