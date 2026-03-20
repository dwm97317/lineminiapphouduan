"""
Order session service
"""

import logging
import json
import uuid
from typing import Optional, Dict, Any
from datetime import datetime, timedelta
import redis

from models.order_session import (
    OrderSession,
    OrderSessionState,
    OrderSessionStatus,
)
from services.state_machine import (
    OrderSessionStateMachine,
    StateTransitionValidator,
    StateTransitionError,
)
from services.info_extractor import InfoExtractor, MessageAnalyzer

logger = logging.getLogger(__name__)

# Redis TTL for session cache (24 hours)
SESSION_CACHE_TTL = 24 * 60 * 60


class SessionService:
    """Service for managing order sessions"""
    
    def __init__(self, redis_client: redis.Redis):
        """Initialize session service"""
        self.redis_client = redis_client
    
    def create_session(
        self,
        platform_account_id: int,
        user_id: str,
        user_name: Optional[str] = None,
    ) -> OrderSession:
        """Create new order session"""
        
        # Generate unique session ID
        session_id = f"session_{uuid.uuid4().hex[:12]}"
        
        # Create session
        session = OrderSession(
            session_id=session_id,
            platform_account_id=platform_account_id,
            user_id=user_id,
            user_name=user_name,
            state=OrderSessionState.COLLECTING,
            status=OrderSessionStatus.ACTIVE,
        )
        
        # Cache session
        self._cache_session(session)
        
        logger.info(
            f"Created order session: {session_id}",
            extra={
                "session_id": session_id,
                "user_id": user_id,
                "platform_account_id": platform_account_id,
            }
        )
        
        return session
    
    def get_session(self, session_id: str) -> Optional[OrderSession]:
        """Get session by ID"""
        
        # Try to get from cache first
        cached = self._get_cached_session(session_id)
        if cached:
            logger.debug(f"Session retrieved from cache: {session_id}")
            return cached
        
        # TODO: Get from database if not in cache
        logger.warning(f"Session not found: {session_id}")
        return None
    
    def update_session_state(
        self,
        session_id: str,
        new_state: OrderSessionState,
        reason: Optional[str] = None,
    ) -> bool:
        """Update session state"""
        
        # Get session
        session = self.get_session(session_id)
        if not session:
            raise ValueError(f"Session not found: {session_id}")
        
        # Validate transition
        is_valid, error_msg = StateTransitionValidator.validate_transition(
            session.state,
            new_state,
            session.to_dict(),
        )
        
        if not is_valid:
            logger.warning(f"Invalid state transition: {error_msg}")
            raise StateTransitionError(error_msg)
        
        # Update state
        session.state = new_state
        session.updated_at = datetime.utcnow()
        
        # Cache updated session
        self._cache_session(session)
        
        logger.info(
            f"Updated session state: {session_id}",
            extra={
                "session_id": session_id,
                "new_state": new_state.value,
                "reason": reason,
            }
        )
        
        return True
    
    def add_message_to_session(
        self,
        session_id: str,
        message_text: str,
        message_id: Optional[str] = None,
    ) -> Dict[str, Any]:
        """Add message to session and extract information"""
        
        # Get session
        session = self.get_session(session_id)
        if not session:
            raise ValueError(f"Session not found: {session_id}")
        
        # Analyze message
        analysis = MessageAnalyzer.analyze(message_text)
        
        # Update session context
        context = session.conversation_context
        
        if analysis["has_amount"]:
            context["amount_vnd"] = analysis["extracted_info"]["amount_vnd"]
        
        if analysis["has_date"]:
            context["date"] = analysis["extracted_info"]["date"]
        
        if analysis["has_package"]:
            context["package_code"] = analysis["extracted_info"]["package_code"]
        
        if analysis["has_contact"]:
            if analysis["extracted_info"]["phone"]:
                context["phone"] = analysis["extracted_info"]["phone"]
            if analysis["extracted_info"]["email"]:
                context["email"] = analysis["extracted_info"]["email"]
        
        # Update message count
        context["message_count"] = context.get("message_count", 0) + 1
        
        session.conversation_context = context
        session.updated_at = datetime.utcnow()
        
        # Cache updated session
        self._cache_session(session)
        
        logger.info(
            f"Added message to session: {session_id}",
            extra={
                "session_id": session_id,
                "message_id": message_id,
                "confidence": analysis["confidence"],
            }
        )
        
        return {
            "session_id": session_id,
            "message_id": message_id,
            "analysis": analysis,
            "updated_context": context,
        }
    
    def close_session(
        self,
        session_id: str,
        reason: Optional[str] = None,
    ) -> bool:
        """Close session"""
        
        # Get session
        session = self.get_session(session_id)
        if not session:
            raise ValueError(f"Session not found: {session_id}")
        
        # Update state to closed
        session.state = OrderSessionState.CLOSED
        session.status = OrderSessionStatus.COMPLETED
        session.updated_at = datetime.utcnow()
        
        # Cache updated session
        self._cache_session(session)
        
        logger.info(
            f"Closed session: {session_id}",
            extra={
                "session_id": session_id,
                "reason": reason,
            }
        )
        
        return True
    
    def expire_session(self, session_id: str) -> bool:
        """Mark session as expired"""
        
        # Get session
        session = self.get_session(session_id)
        if not session:
            return False
        
        # Update status to expired
        session.status = OrderSessionStatus.EXPIRED
        session.updated_at = datetime.utcnow()
        
        # Cache updated session (with shorter TTL)
        self._cache_session(session, ttl=3600)  # 1 hour
        
        logger.info(f"Expired session: {session_id}")
        
        return True
    
    def _cache_session(self, session: OrderSession, ttl: int = SESSION_CACHE_TTL):
        """Cache session in Redis"""
        try:
            cache_key = f"session:{session.session_id}"
            session_data = json.dumps(session.to_dict(), default=str)
            self.redis_client.setex(cache_key, ttl, session_data)
            logger.debug(f"Cached session: {session.session_id}")
        except Exception as e:
            logger.error(f"Error caching session: {e}")
    
    def _get_cached_session(self, session_id: str) -> Optional[OrderSession]:
        """Get session from Redis cache"""
        try:
            cache_key = f"session:{session_id}"
            cached_data = self.redis_client.get(cache_key)
            
            if not cached_data:
                return None
            
            session_dict = json.loads(cached_data)
            
            # Reconstruct session object
            session = OrderSession(
                session_id=session_dict["session_id"],
                platform_account_id=session_dict["platform_account_id"],
                user_id=session_dict["user_id"],
                user_name=session_dict.get("user_name"),
                state=OrderSessionState(session_dict["state"]),
                status=OrderSessionStatus(session_dict["status"]),
                conversation_context=session_dict.get("conversation_context", {}),
                created_at=datetime.fromisoformat(session_dict["created_at"]),
                updated_at=datetime.fromisoformat(session_dict["updated_at"]),
            )
            
            return session
        
        except Exception as e:
            logger.error(f"Error retrieving cached session: {e}")
            return None
    
    def get_session_ttl(self, session_id: str) -> Optional[int]:
        """Get remaining TTL for session cache"""
        try:
            cache_key = f"session:{session_id}"
            ttl = self.redis_client.ttl(cache_key)
            return ttl if ttl > 0 else None
        except Exception as e:
            logger.error(f"Error getting session TTL: {e}")
            return None
    
    def delete_session(self, session_id: str) -> bool:
        """Delete session from cache"""
        try:
            cache_key = f"session:{session_id}"
            self.redis_client.delete(cache_key)
            logger.info(f"Deleted session: {session_id}")
            return True
        except Exception as e:
            logger.error(f"Error deleting session: {e}")
            return False
