"""
Webhook routes for Facebook Messenger and Instagram
"""

import logging
from fastapi import APIRouter, Request, BackgroundTasks, HTTPException, Query
from typing import Dict, Any
from config import settings
from services.webhook_validator import WebhookValidator
from services.webhook_handler import WebhookEventParser, MessageEvent, PostbackEvent

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/webhook", tags=["webhook"])


@router.get("/facebook")
async def verify_facebook_webhook(
    hub_mode: str = Query(...),
    hub_challenge: str = Query(...),
    hub_verify_token: str = Query(...)
) -> Dict[str, Any]:
    """
    Verify Facebook webhook (GET request)
    
    Args:
        hub_mode: Should be "subscribe"
        hub_challenge: Challenge string to echo back
        hub_verify_token: Verification token
    
    Returns:
        Challenge string if verified
    """
    try:
        # Get verify token from environment
        verify_token = settings.FACEBOOK_VERIFY_TOKEN
        
        if hub_mode != "subscribe":
            logger.warning(f"Invalid hub_mode: {hub_mode}")
            raise HTTPException(status_code=403, detail="Invalid hub_mode")
        
        if hub_verify_token != verify_token:
            logger.warning("Invalid verify token")
            raise HTTPException(status_code=403, detail="Invalid verify token")
        
        logger.info("Facebook webhook verified successfully")
        return {"hub.challenge": hub_challenge}
        
    except Exception as e:
        logger.error(f"Facebook webhook verification failed: {e}")
        raise HTTPException(status_code=403, detail="Verification failed")


@router.post("/facebook")
async def handle_facebook_webhook(
    request: Request,
    background_tasks: BackgroundTasks
) -> Dict[str, str]:
    """
    Handle Facebook webhook events (POST request)
    
    Args:
        request: HTTP request
        background_tasks: Background task queue
    
    Returns:
        Acknowledgment response
    """
    try:
        # Get raw body for signature verification
        body = await request.body()
        
        # Get signature from header
        signature = request.headers.get("X-Hub-Signature", "")
        
        # Verify signature
        app_secret = settings.FACEBOOK_APP_SECRET
        if not WebhookValidator.verify_facebook_signature(body, signature, app_secret):
            logger.warning("Facebook webhook signature verification failed")
            raise HTTPException(status_code=403, detail="Invalid signature")
        
        # Parse JSON
        data = await request.json()
        
        # Parse events
        events = WebhookEventParser.parse_facebook_webhook(data)
        
        # Process events in background
        for event in events:
            background_tasks.add_task(process_facebook_event, event)
        
        logger.info(f"Received {len(events)} Facebook webhook events")
        return {"status": "ok"}
        
    except Exception as e:
        logger.error(f"Error handling Facebook webhook: {e}")
        raise HTTPException(status_code=500, detail="Internal server error")


@router.get("/instagram")
async def verify_instagram_webhook(
    hub_mode: str = Query(...),
    hub_challenge: str = Query(...),
    hub_verify_token: str = Query(...)
) -> Dict[str, Any]:
    """
    Verify Instagram webhook (GET request)
    
    Args:
        hub_mode: Should be "subscribe"
        hub_challenge: Challenge string to echo back
        hub_verify_token: Verification token
    
    Returns:
        Challenge string if verified
    """
    try:
        # Get verify token from environment
        verify_token = settings.INSTAGRAM_VERIFY_TOKEN
        
        if hub_mode != "subscribe":
            logger.warning(f"Invalid hub_mode: {hub_mode}")
            raise HTTPException(status_code=403, detail="Invalid hub_mode")
        
        if hub_verify_token != verify_token:
            logger.warning("Invalid verify token")
            raise HTTPException(status_code=403, detail="Invalid verify token")
        
        logger.info("Instagram webhook verified successfully")
        return {"hub.challenge": hub_challenge}
        
    except Exception as e:
        logger.error(f"Instagram webhook verification failed: {e}")
        raise HTTPException(status_code=403, detail="Verification failed")


@router.post("/instagram")
async def handle_instagram_webhook(
    request: Request,
    background_tasks: BackgroundTasks
) -> Dict[str, str]:
    """
    Handle Instagram webhook events (POST request)
    
    Args:
        request: HTTP request
        background_tasks: Background task queue
    
    Returns:
        Acknowledgment response
    """
    try:
        # Get raw body for signature verification
        body = await request.body()
        
        # Get signature from header
        signature = request.headers.get("X-Hub-Signature", "")
        
        # Verify signature
        app_secret = settings.FACEBOOK_APP_SECRET  # Same app secret for Instagram
        if not WebhookValidator.verify_instagram_signature(body, signature, app_secret):
            logger.warning("Instagram webhook signature verification failed")
            raise HTTPException(status_code=403, detail="Invalid signature")
        
        # Parse JSON
        data = await request.json()
        
        # Parse events
        events = WebhookEventParser.parse_instagram_webhook(data)
        
        # Process events in background
        for event in events:
            background_tasks.add_task(process_instagram_event, event)
        
        logger.info(f"Received {len(events)} Instagram webhook events")
        return {"status": "ok"}
        
    except Exception as e:
        logger.error(f"Error handling Instagram webhook: {e}")
        raise HTTPException(status_code=500, detail="Internal server error")


async def process_facebook_event(event: Any) -> None:
    """
    Process Facebook webhook event asynchronously
    
    Args:
        event: Webhook event
    """
    try:
        if isinstance(event, MessageEvent):
            logger.info(f"Processing Facebook message from {event.sender_id}: {event.text}")
            # TODO: Implement message processing logic
            # - Save to database
            # - Send to NLP service
            # - Generate response
            
        elif isinstance(event, PostbackEvent):
            logger.info(f"Processing Facebook postback from {event.sender_id}: {event.payload}")
            # TODO: Implement postback processing logic
        
    except Exception as e:
        logger.error(f"Error processing Facebook event: {e}")


async def process_instagram_event(event: Any) -> None:
    """
    Process Instagram webhook event asynchronously
    
    Args:
        event: Webhook event
    """
    try:
        if isinstance(event, MessageEvent):
            logger.info(f"Processing Instagram message from {event.sender_id}: {event.text}")
            # TODO: Implement message processing logic
            # - Save to database
            # - Send to NLP service
            # - Generate response
            
        elif isinstance(event, PostbackEvent):
            logger.info(f"Processing Instagram postback from {event.sender_id}: {event.payload}")
            # TODO: Implement postback processing logic
        
    except Exception as e:
        logger.error(f"Error processing Instagram event: {e}")
