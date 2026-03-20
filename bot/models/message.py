"""
Message models for database
"""

from datetime import datetime
from typing import Optional
from enum import Enum


class MessagePlatform(str, Enum):
    """Message platform"""
    FACEBOOK = "facebook"
    INSTAGRAM = "instagram"


class MessageDirection(str, Enum):
    """Message direction"""
    INBOUND = "inbound"
    OUTBOUND = "outbound"


class MessageStatus(str, Enum):
    """Message status"""
    RECEIVED = "received"
    SENT = "sent"
    DELIVERED = "delivered"
    READ = "read"
    FAILED = "failed"


class Message:
    """Message model"""
    
    def __init__(
        self,
        platform: MessagePlatform,
        sender_id: str,
        recipient_id: str,
        text: Optional[str] = None,
        direction: MessageDirection = MessageDirection.INBOUND,
        status: MessageStatus = MessageStatus.RECEIVED,
        platform_message_id: Optional[str] = None,
        metadata: Optional[dict] = None,
    ):
        self.platform = platform
        self.sender_id = sender_id
        self.recipient_id = recipient_id
        self.text = text
        self.direction = direction
        self.status = status
        self.platform_message_id = platform_message_id
        self.metadata = metadata or {}
        self.created_at = datetime.now()
    
    def to_dict(self) -> dict:
        return {
            "platform": self.platform.value,
            "sender_id": self.sender_id,
            "recipient_id": self.recipient_id,
            "text": self.text,
            "direction": self.direction.value,
            "status": self.status.value,
            "platform_message_id": self.platform_message_id,
            "metadata": self.metadata,
            "created_at": self.created_at.isoformat(),
        }
