"""
Order session API routes
"""

import logging
from fastapi import APIRouter, HTTPException, Depends, BackgroundTasks
from typing import Optional
import redis

from models.order_session import (
    CreateOrderSessionRequest,
    UpdateOrderSessionStatusRequest,
    OrderSessionResponse,
    OrderSessionState,
)
from services.session_service import SessionService
from config import settings

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/api/v1", tags=["order-session"])

# Redis client
redis_client = redis.Redis(
    host=settings.REDIS_HOST,
    port=settings.REDIS_PORT,
    db=settings.REDIS_DB,
    password=settings.REDIS_PASSWORD if settings.REDIS_PASSWORD else None,
    decode_responses=True
)

# Session service
session_service = SessionService(redis_client)


def get_session_service() -> SessionService:
    """Get session service dependency"""
    return session_service


@router.post("/order-session", response_model=OrderSessionResponse)
async def create_order_session(
    request: CreateOrderSessionRequest,
    service: SessionService = Depends(get_session_service),
) -> OrderSessionResponse:
    """
    Create new order session
    
    Args:
        request: Create session request
        service: Session service
    
    Returns:
        Created session
    """
    try:
        # Create session
        session = service.create_session(
            platform_account_id=request.platform_account_id,
            user_id=request.user_id,
            user_name=request.user_name,
        )
        
        # Record metric
        from metrics import record_session_created
        record_session_created(request.platform)
        
        logger.info(
            f"Created order session: {session.session_id}",
            extra={
                "session_id": session.session_id,
                "user_id": request.user_id,
            }
        )
        
        return OrderSessionResponse(**session.to_dict())
    
    except Exception as e:
        logger.error(f"Error creating order session: {e}")
        raise HTTPException(status_code=500, detail="Failed to create session")


@router.get("/order-session/{session_id}", response_model=OrderSessionResponse)
async def get_order_session(
    session_id: str,
    service: SessionService = Depends(get_session_service),
) -> OrderSessionResponse:
    """
    Get order session by ID
    
    Args:
        session_id: Session ID
        service: Session service
    
    Returns:
        Session details
    """
    try:
        # Get session
        session = service.get_session(session_id)
        
        if not session:
            raise HTTPException(status_code=404, detail="Session not found")
        
        logger.debug(f"Retrieved order session: {session_id}")
        
        return OrderSessionResponse(**session.to_dict())
    
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error retrieving order session: {e}")
        raise HTTPException(status_code=500, detail="Failed to retrieve session")


@router.patch("/order-session/{session_id}/status", response_model=OrderSessionResponse)
async def update_order_session_status(
    session_id: str,
    request: UpdateOrderSessionStatusRequest,
    service: SessionService = Depends(get_session_service),
) -> OrderSessionResponse:
    """
    Update order session status
    
    Args:
        session_id: Session ID
        request: Update request
        service: Session service
    
    Returns:
        Updated session
    """
    try:
        # Update session state
        service.update_session_state(
            session_id=session_id,
            new_state=request.state,
            reason=request.reason,
        )
        
        # Get updated session
        session = service.get_session(session_id)
        
        if not session:
            raise HTTPException(status_code=404, detail="Session not found")
        
        logger.info(
            f"Updated order session status: {session_id}",
            extra={
                "session_id": session_id,
                "new_state": request.state.value,
                "reason": request.reason,
            }
        )
        
        return OrderSessionResponse(**session.to_dict())
    
    except ValueError as e:
        logger.warning(f"Invalid session: {e}")
        raise HTTPException(status_code=404, detail=str(e))
    except Exception as e:
        logger.error(f"Error updating order session: {e}")
        raise HTTPException(status_code=500, detail="Failed to update session")


@router.post("/order-session/{session_id}/message")
async def add_message_to_session(
    session_id: str,
    message_text: str,
    message_id: Optional[str] = None,
    service: SessionService = Depends(get_session_service),
):
    """
    Add message to session and extract information
    
    Args:
        session_id: Session ID
        message_text: Message text
        message_id: Optional message ID
        service: Session service
    
    Returns:
        Analysis result
    """
    try:
        # Add message and extract info
        result = service.add_message_to_session(
            session_id=session_id,
            message_text=message_text,
            message_id=message_id,
        )
        
        logger.info(
            f"Added message to session: {session_id}",
            extra={
                "session_id": session_id,
                "message_id": message_id,
                "confidence": result["analysis"]["confidence"],
            }
        )
        
        return result
    
    except ValueError as e:
        logger.warning(f"Invalid session: {e}")
        raise HTTPException(status_code=404, detail=str(e))
    except Exception as e:
        logger.error(f"Error adding message to session: {e}")
        raise HTTPException(status_code=500, detail="Failed to add message")


@router.post("/order-session/{session_id}/close")
async def close_order_session(
    session_id: str,
    reason: Optional[str] = None,
    service: SessionService = Depends(get_session_service),
):
    """
    Close order session
    
    Args:
        session_id: Session ID
        reason: Reason for closing
        service: Session service
    
    Returns:
        Success status
    """
    try:
        # Close session
        service.close_session(session_id, reason)
        
        logger.info(
            f"Closed order session: {session_id}",
            extra={
                "session_id": session_id,
                "reason": reason,
            }
        )
        
        return {"status": "ok", "session_id": session_id}
    
    except ValueError as e:
        logger.warning(f"Invalid session: {e}")
        raise HTTPException(status_code=404, detail=str(e))
    except Exception as e:
        logger.error(f"Error closing order session: {e}")
        raise HTTPException(status_code=500, detail="Failed to close session")


@router.get("/order-session/{session_id}/ttl")
async def get_session_ttl(
    session_id: str,
    service: SessionService = Depends(get_session_service),
):
    """
    Get session cache TTL
    
    Args:
        session_id: Session ID
        service: Session service
    
    Returns:
        TTL in seconds
    """
    try:
        ttl = service.get_session_ttl(session_id)
        
        return {
            "session_id": session_id,
            "ttl_seconds": ttl,
            "ttl_hours": ttl / 3600 if ttl else None,
        }
    
    except Exception as e:
        logger.error(f"Error getting session TTL: {e}")
        raise HTTPException(status_code=500, detail="Failed to get TTL")
