"""
Account models for multi-account management
"""

from datetime import datetime
from typing import Optional, List, Dict, Any
from enum import Enum
from pydantic import BaseModel, Field


class PlatformType(str, Enum):
    """Platform types"""
    FACEBOOK = "facebook"
    INSTAGRAM = "instagram"
    LINE = "line"


class AccountStatus(str, Enum):
    """Account status"""
    ACTIVE = "active"
    INACTIVE = "inactive"
    SUSPENDED = "suspended"
    PENDING_VERIFICATION = "pending_verification"


class PlatformAccount:
    """Platform account model"""
    
    def __init__(
        self,
        account_id: int,
        customer_id: str,
        platform: PlatformType,
        platform_account_id: str,
        platform_account_name: Optional[str] = None,
        access_token: Optional[str] = None,
        refresh_token: Optional[str] = None,
        token_expires_at: Optional[datetime] = None,
        status: AccountStatus = AccountStatus.ACTIVE,
        created_at: Optional[datetime] = None,
        updated_at: Optional[datetime] = None,
    ):
        self.account_id = account_id
        self.customer_id = customer_id
        self.platform = platform
        self.platform_account_id = platform_account_id
        self.platform_account_name = platform_account_name
        self.access_token = access_token
        self.refresh_token = refresh_token
        self.token_expires_at = token_expires_at
        self.status = status
        self.created_at = created_at or datetime.utcnow()
        self.updated_at = updated_at or datetime.utcnow()
    
    def to_dict(self) -> Dict[str, Any]:
        """Convert to dictionary"""
        return {
            "account_id": self.account_id,
            "customer_id": self.customer_id,
            "platform": self.platform.value,
            "platform_account_id": self.platform_account_id,
            "platform_account_name": self.platform_account_name,
            "status": self.status.value,
            "created_at": self.created_at.isoformat(),
            "updated_at": self.updated_at.isoformat(),
        }


class LinkAccountRequest(BaseModel):
    """Link account request"""
    customer_id: str = Field(..., description="Customer ID")
    platform: str = Field(..., description="Platform (facebook, instagram, line)")
    platform_account_id: str = Field(..., description="Platform account ID")
    platform_account_name: Optional[str] = Field(None, description="Platform account name")
    access_token: str = Field(..., description="Access token")


class UnlinkAccountRequest(BaseModel):
    """Unlink account request"""
    account_id: int = Field(..., description="Account ID to unlink")
    confirmation_code: str = Field(..., description="Confirmation code")


class PlatformAccountResponse(BaseModel):
    """Platform account response"""
    account_id: int
    customer_id: str
    platform: str
    platform_account_id: str
    platform_account_name: Optional[str]
    status: str
    created_at: str
    updated_at: str


class AccountListResponse(BaseModel):
    """Account list response"""
    customer_id: str
    total_accounts: int
    max_accounts: int
    accounts: List[PlatformAccountResponse]


class UnlinkConfirmationRequest(BaseModel):
    """Unlink confirmation request"""
    account_id: int = Field(..., description="Account ID")


class UnlinkConfirmationResponse(BaseModel):
    """Unlink confirmation response"""
    account_id: int
    confirmation_code: str
    expires_at: str
    message: str
