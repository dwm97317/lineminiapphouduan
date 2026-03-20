"""
Instagram Graph API integration
"""

import httpx
import logging
from typing import Optional, Dict, Any
from config import settings

logger = logging.getLogger(__name__)


class InstagramAPI:
    """Instagram Graph API client"""
    
    BASE_URL = "https://graph.instagram.com/v18.0"
    
    def __init__(self, page_access_token: str):
        """
        Initialize Instagram API client
        
        Args:
            page_access_token: Instagram Business Account Access Token
        """
        self.page_access_token = page_access_token
        self.client = httpx.AsyncClient(timeout=30.0)
    
    async def send_text_message(
        self,
        recipient_id: str,
        text: str
    ) -> Dict[str, Any]:
        """
        Send text message to Instagram user
        
        Args:
            recipient_id: Instagram user ID (PSID)
            text: Message text
        
        Returns:
            API response
        """
        try:
            payload = {
                "recipient": {"id": recipient_id},
                "message": {"text": text}
            }
            
            response = await self.client.post(
                f"{self.BASE_URL}/me/messages",
                params={"access_token": self.page_access_token},
                json=payload
            )
            
            response.raise_for_status()
            result = response.json()
            
            logger.info(f"Instagram message sent to {recipient_id}: {result}")
            return result
            
        except httpx.HTTPError as e:
            logger.error(f"Failed to send Instagram message: {e}")
            raise
    
    async def send_image_message(
        self,
        recipient_id: str,
        image_url: str,
        caption: Optional[str] = None
    ) -> Dict[str, Any]:
        """
        Send image message to Instagram user
        
        Args:
            recipient_id: Instagram user ID (PSID)
            image_url: URL of the image
            caption: Optional image caption
        
        Returns:
            API response
        """
        try:
            message_data = {
                "attachment": {
                    "type": "image",
                    "payload": {"url": image_url}
                }
            }
            
            if caption:
                message_data["text"] = caption
            
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
            
            logger.info(f"Instagram image sent to {recipient_id}: {result}")
            return result
            
        except httpx.HTTPError as e:
            logger.error(f"Failed to send Instagram image: {e}")
            raise
    
    async def get_user_profile(self, user_id: str) -> Dict[str, Any]:
        """
        Get Instagram user profile information
        
        Args:
            user_id: Instagram user ID (PSID)
        
        Returns:
            User profile data
        """
        try:
            response = await self.client.get(
                f"{self.BASE_URL}/{user_id}",
                params={
                    "fields": "name,profile_pic_url,username",
                    "access_token": self.page_access_token
                }
            )
            
            response.raise_for_status()
            return response.json()
            
        except httpx.HTTPError as e:
            logger.error(f"Failed to get Instagram user profile: {e}")
            raise
    
    async def mark_message_as_seen(self, message_id: str) -> Dict[str, Any]:
        """
        Mark message as seen
        
        Args:
            message_id: Instagram message ID
        
        Returns:
            API response
        """
        try:
            payload = {
                "recipient": {"id": message_id}
            }
            
            response = await self.client.post(
                f"{self.BASE_URL}/me/messages",
                params={"access_token": self.page_access_token},
                json=payload
            )
            
            response.raise_for_status()
            return response.json()
            
        except httpx.HTTPError as e:
            logger.error(f"Failed to mark message as seen: {e}")
            raise
    
    async def close(self):
        """Close HTTP client"""
        await self.client.aclose()
