"""
Webhook event handlers for Facebook Messenger and Instagram
"""

import logging
from typing import Dict, Any, Optional
from datetime import datetime
from enum import Enum

logger = logging.getLogger(__name__)


class MessageType(str, Enum):
    """Message types"""
    TEXT = "text"
    IMAGE = "image"
    VIDEO = "video"
    AUDIO = "audio"
    FILE = "file"
    LOCATION = "location"
    POSTBACK = "postback"
    QUICK_REPLY = "quick_reply"


class WebhookEvent:
    """Base webhook event model"""
    
    def __init__(self, event_data: Dict[str, Any]):
        self.event_data = event_data
        self.timestamp = datetime.now()
    
    def to_dict(self) -> Dict[str, Any]:
        return self.event_data


class MessageEvent(WebhookEvent):
    """Message event from webhook"""
    
    def __init__(self, event_data: Dict[str, Any]):
        super().__init__(event_data)
        self.sender_id = event_data.get("sender", {}).get("id")
        self.recipient_id = event_data.get("recipient", {}).get("id")
        self.timestamp = event_data.get("timestamp")
        self.message = event_data.get("message", {})
    
    @property
    def message_id(self) -> Optional[str]:
        """Get message ID"""
        return self.message.get("mid")
    
    @property
    def text(self) -> Optional[str]:
        """Get message text"""
        return self.message.get("text")
    
    @property
    def quick_reply_payload(self) -> Optional[str]:
        """Get quick reply payload"""
        quick_reply = self.message.get("quick_reply", {})
        return quick_reply.get("payload")
    
    @property
    def attachments(self) -> list:
        """Get message attachments"""
        return self.message.get("attachments", [])
    
    @property
    def message_type(self) -> MessageType:
        """Determine message type"""
        if self.text:
            return MessageType.TEXT
        
        if self.quick_reply_payload:
            return MessageType.QUICK_REPLY
        
        if self.attachments:
            attachment = self.attachments[0]
            attachment_type = attachment.get("type", "").lower()
            
            if attachment_type == "image":
                return MessageType.IMAGE
            elif attachment_type == "video":
                return MessageType.VIDEO
            elif attachment_type == "audio":
                return MessageType.AUDIO
            elif attachment_type == "file":
                return MessageType.FILE
            elif attachment_type == "location":
                return MessageType.LOCATION
        
        return MessageType.TEXT
    
    def to_dict(self) -> Dict[str, Any]:
        return {
            "sender_id": self.sender_id,
            "recipient_id": self.recipient_id,
            "message_id": self.message_id,
            "text": self.text,
            "message_type": self.message_type.value,
            "quick_reply_payload": self.quick_reply_payload,
            "attachments": self.attachments,
            "timestamp": self.timestamp
        }


class PostbackEvent(WebhookEvent):
    """Postback event from webhook"""
    
    def __init__(self, event_data: Dict[str, Any]):
        super().__init__(event_data)
        self.sender_id = event_data.get("sender", {}).get("id")
        self.recipient_id = event_data.get("recipient", {}).get("id")
        self.timestamp = event_data.get("timestamp")
        self.postback = event_data.get("postback", {})
    
    @property
    def payload(self) -> Optional[str]:
        """Get postback payload"""
        return self.postback.get("payload")
    
    @property
    def title(self) -> Optional[str]:
        """Get postback title"""
        return self.postback.get("title")
    
    def to_dict(self) -> Dict[str, Any]:
        return {
            "sender_id": self.sender_id,
            "recipient_id": self.recipient_id,
            "payload": self.payload,
            "title": self.title,
            "timestamp": self.timestamp
        }


class WebhookEventParser:
    """Parse webhook events from Meta APIs"""
    
    @staticmethod
    def parse_facebook_webhook(data: Dict[str, Any]) -> list:
        """
        Parse Facebook webhook data
        
        Args:
            data: Webhook payload
        
        Returns:
            List of parsed events
        """
        events = []
        
        try:
            entries = data.get("entry", [])
            
            for entry in entries:
                messaging_events = entry.get("messaging", [])
                
                for event in messaging_events:
                    if "message" in event:
                        events.append(MessageEvent(event))
                    elif "postback" in event:
                        events.append(PostbackEvent(event))
                    else:
                        logger.debug(f"Unhandled event type: {event.keys()}")
            
            logger.info(f"Parsed {len(events)} events from Facebook webhook")
            return events
            
        except Exception as e:
            logger.error(f"Error parsing Facebook webhook: {e}")
            return []
    
    @staticmethod
    def parse_instagram_webhook(data: Dict[str, Any]) -> list:
        """
        Parse Instagram webhook data
        
        Args:
            data: Webhook payload
        
        Returns:
            List of parsed events
        """
        # Instagram uses same format as Facebook
        return WebhookEventParser.parse_facebook_webhook(data)
