"""
Order session models
"""

from datetime import datetime
from typing import Optional, Dict, Any
from enum import Enum
from pydantic import BaseModel, Field


class OrderSessionState(str, Enum):
    """Order session states"""
    COLLECTING = "collecting"      # Collecting order information
    READY = "ready"                # Ready for binding
    BOUND = "bound"                # Bound to user
    CLOSED = "closed"              # Session closed


class OrderSessionStatus(str, Enum):
    """Order session status"""
    ACTIVE = "active"
    COMPLETED = "completed"
    ABANDONED = "abandoned"
    EXPIRED = "expired"


class OrderSession:
    """Order session model"""
    
    def __init__(
        self,
        session_id: str,
        platform_account_id: int,
        user_id: str,
        user_name: Optional[str] = None,
        state: OrderSessionState = OrderSessionState.COLLECTING,
        status: OrderSessionStatus = OrderSessionStatus.ACTIVE,
        conversation_context: Optional[Dict[str, Any]] = None,
        created_at: Optional[datetime] = None,
        updated_at: Optional[datetime] = None,
    ):
        self.session_id = session_id
        self.platform_account_id = platform_account_id
        self.user_id = user_id
        self.user_name = user_name
        self.state = state
        self.status = status
        self.conversation_context = conversation_context or {}
        self.created_at = created_at or datetime.utcnow()
        self.updated_at = updated_at or datetime.utcnow()
    
    def to_dict(self) -> Dict[str, Any]:
        """Convert to dictionary"""
        return {
            "session_id": self.session_id,
            "platform_account_id": self.platform_account_id,
            "user_id": self.user_id,
            "user_name": self.user_name,
            "state": self.state.value,
            "status": self.status.value,
            "conversation_context": self.conversation_context,
            "created_at": self.created_at.isoformat(),
            "updated_at": self.updated_at.isoformat(),
        }


class CreateOrderSessionRequest(BaseModel):
    """Create order session request"""
    platform_account_id: int = Field(..., description="Platform account ID")
    user_id: str = Field(..., description="User ID")
    user_name: Optional[str] = Field(None, description="User name")
    platform: str = Field(..., description="Platform (facebook, instagram)")


class UpdateOrderSessionStatusRequest(BaseModel):
    """Update order session status request"""
    state: OrderSessionState = Field(..., description="New state")
    reason: Optional[str] = Field(None, description="Reason for state change")


class OrderSessionResponse(BaseModel):
    """Order session response"""
    session_id: str
    platform_account_id: int
    user_id: str
    user_name: Optional[str]
    state: str
    status: str
    conversation_context: Dict[str, Any]
    created_at: str
    updated_at: str


class ExtractedInfo(BaseModel):
    """Extracted information from message"""
    amount_vnd: Optional[float] = Field(None, description="Amount in VND")
    date: Optional[str] = Field(None, description="Date (YYYY-MM-DD)")
    package_code: Optional[str] = Field(None, description="Package code")
    raw_text: str = Field(..., description="Raw text")
    confidence: float = Field(default=0.0, description="Extraction confidence")


class MessageWithExtraction(BaseModel):
    """Message with extracted information"""
    message_id: str
    text: str
    extracted_info: ExtractedInfo
    timestamp: str
