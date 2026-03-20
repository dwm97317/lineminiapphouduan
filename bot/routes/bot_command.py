"""
Bot command routes
"""

import logging
from fastapi import APIRouter, HTTPException, BackgroundTasks
from pydantic import BaseModel
from typing import Optional
import redis

from services.bot_commands import BotCommandHandler
from services.account_service import AccountService
from services.session_service import SessionService
from services.messenger_api import MessengerAPI
from config import settings

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/api/v1/command", tags=["command"])

# Redis client
redis_client = redis.Redis(
    host=settings.REDIS_HOST,
    port=settings.REDIS_PORT,
    db=settings.REDIS_DB,
    password=settings.REDIS_PASSWORD if settings.REDIS_PASSWORD else None,
    decode_responses=True
)

# Services
account_service = AccountService(redis_client)
session_service = SessionService(redis_client)
command_handler = BotCommandHandler(account_service, session_service)


class BotCommandRequest(BaseModel):
    """Bot command request"""
    user_id: str
    customer_id: str
    command_text: str
    platform: str = "facebook"


@router.post("/execute")
async def execute_command(
    request: BotCommandRequest,
    background_tasks: BackgroundTasks,
):
    """
    Execute bot command
    
    Args:
        request: Command request
        background_tasks: Background tasks
    
    Returns:
        Command result
    """
    try:
        # Handle command
        result = command_handler.handle_command(
            user_id=request.user_id,
            customer_id=request.customer_id,
            command_text=request.command_text,
        )
        
        # Send response via Messenger (async)
        if request.platform == "facebook":
            background_tasks.add_task(
                send_messenger_response,
                user_id=request.user_id,
                message=result.get("message"),
            )
        
        logger.info(
            f"Executed command: {request.command_text}",
            extra={
                "user_id": request.user_id,
                "customer_id": request.customer_id,
                "status": result.get("status"),
            }
        )
        
        return result
    
    except Exception as e:
        logger.error(f"Error executing command: {e}")
        raise HTTPException(status_code=500, detail="Failed to execute command")


async def send_messenger_response(user_id: str, message: str):
    """Send response via Messenger"""
    try:
        api = MessengerAPI(settings.FACEBOOK_PAGE_ACCESS_TOKEN)
        await api.send_text_message(
            recipient_id=user_id,
            text=message,
        )
        await api.close()
        logger.debug(f"Sent Messenger response to {user_id}")
    except Exception as e:
        logger.error(f"Error sending Messenger response: {e}")
