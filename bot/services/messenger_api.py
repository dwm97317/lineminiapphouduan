"""
Facebook Messenger API integration
"""

import httpx
import logging
from typing import Optional, List, Dict, Any
from config import settings

logger = logging.getLogger(__name__)


class QuickReply:
    """Quick Reply button model"""
    
    def __init__(self, title: str, payload: str):
        self.title = title
        self.payload = payload
    
    def to_dict(self) -> Dict[str, str]:
        return {
            "content_type": "text",
            "title": self.title,
            "payload": self.payload
        }


class MessengerAPI:
    """Facebook Messenger API client"""
    
    BASE_URL = "https://graph.facebook.com/v18.0"
    
    def __init__(self, page_access_token: str):
        """
        Initialize Messenger API client
        
        Args:
            page_access_token: Facebook Page Access Token
        """
        self.page_access_token = page_access_token
        self.client = httpx.AsyncClient(timeout=30.0)
    
    async def send_text_message(
        self,
        recipient_id: str,
        text: str,
        quick_replies: Optional[List[QuickReply]] = None
    ) -> Dict[str, Any]:
        """
        Send text message to user
        
        Args:
            recipient_id: Facebook user ID
            text: Message text
            quick_replies: Optional list of QuickReply buttons
        
        Returns:
            API response
        """
        try:
            message_data = {
                "text": text
            }
            
            if quick_replies:
                message_data["quick_replies"] = [qr.to_dict() for qr in quick_replies]
            
            payload = {
                "recipient": {"id": recipient_id},
                "message": message_data
            }
            
            response = await self.client.post(
                f"{self.BASE_URL}/me/messages",
                params={"access_token": self.page_access_token},
                json=payload
            )
            
            response.raise_for_status()
            result = response.json()
            
            logger.info(f"Message sent to {recipient_id}: {result}")
            return result
            
        except httpx.HTTPError as e:
            logger.error(f"Failed to send message: {e}")
            raise
    
    async def send_quick_reply(
        self,
        recipient_id: str,
        text: str,
        quick_replies: List[QuickReply]
    ) -> Dict[str, Any]:
        """
        Send message with quick reply buttons
        
        Args:
            recipient_id: Facebook user ID
            text: Message text
            quick_replies: List of QuickReply buttons
        
        Returns:
            API response
        """
        return await self.send_text_message(recipient_id, text, quick_replies)
    
    async def send_typing_indicator(self, recipient_id: str) -> Dict[str, Any]:
        """
        Send typing indicator
        
        Args:
            recipient_id: Facebook user ID
        
        Returns:
            API response
        """
        try:
            payload = {
                "recipient": {"id": recipient_id},
                "sender_action": "typing_on"
            }
            
            response = await self.client.post(
                f"{self.BASE_URL}/me/messages",
                params={"access_token": self.page_access_token},
                json=payload
            )
            
            response.raise_for_status()
            return response.json()
            
        except httpx.HTTPError as e:
            logger.error(f"Failed to send typing indicator: {e}")
            raise
    
    async def get_user_profile(self, user_id: str) -> Dict[str, Any]:
        """
        Get user profile information
        
        Args:
            user_id: Facebook user ID
        
        Returns:
            User profile data
        """
        try:
            response = await self.client.get(
                f"{self.BASE_URL}/{user_id}",
                params={
                    "fields": "first_name,last_name,profile_pic_url,locale,timezone",
                    "access_token": self.page_access_token
                }
            )
            
            response.raise_for_status()
            return response.json()
            
        except httpx.HTTPError as e:
            logger.error(f"Failed to get user profile: {e}")
            raise
    
    async def close(self):
        """Close HTTP client"""
        await self.client.aclose()
