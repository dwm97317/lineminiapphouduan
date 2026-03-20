"""
Gray release (canary deployment) strategy
"""

import os
import logging
from typing import Dict, List
from enum import Enum

logger = logging.getLogger(__name__)


class ReleaseStage(str, Enum):
    """Release stages"""
    BLUE = "blue"
    GREEN = "green"
    CANARY = "canary"
    STABLE = "stable"


class GrayReleaseConfig:
    """Gray release configuration"""
    
    def __init__(self):
        # Current active version
        self.active_version = os.getenv("ACTIVE_VERSION", "blue")
        
        # Canary traffic percentage (0-100)
        self.canary_traffic_percentage = int(
            os.getenv("CANARY_TRAFFIC_PERCENTAGE", "0")
        )
        
        # Canary user IDs (for targeted rollout)
        self.canary_user_ids = os.getenv("CANARY_USER_IDS", "").split(",")
        self.canary_user_ids = [uid.strip() for uid in self.canary_user_ids if uid.strip()]
        
        # Canary platforms (for platform-specific rollout)
        self.canary_platforms = os.getenv("CANARY_PLATFORMS", "").split(",")
        self.canary_platforms = [p.strip() for p in self.canary_platforms if p.strip()]
        
        # Health check thresholds
        self.error_rate_threshold = float(os.getenv("ERROR_RATE_THRESHOLD", "0.1"))
        self.latency_threshold_ms = float(os.getenv("LATENCY_THRESHOLD_MS", "5000"))
        
        # Rollback triggers
        self.auto_rollback_enabled = os.getenv("AUTO_ROLLBACK_ENABLED", "true").lower() == "true"
        self.rollback_error_rate = float(os.getenv("ROLLBACK_ERROR_RATE", "0.25"))
        self.rollback_latency_ms = float(os.getenv("ROLLBACK_LATENCY_MS", "10000"))
    
    def should_route_to_canary(
        self,
        user_id: str = None,
        platform: str = None,
        request_hash: int = None
    ) -> bool:
        """Determine if request should be routed to canary"""
        
        # If canary traffic is 0%, always route to stable
        if self.canary_traffic_percentage == 0:
            return False
        
        # If canary traffic is 100%, always route to canary
        if self.canary_traffic_percentage == 100:
            return True
        
        # Check if user is in canary list
        if user_id and user_id in self.canary_user_ids:
            logger.info(f"Routing user {user_id} to canary (in user list)")
            return True
        
        # Check if platform is in canary list
        if platform and platform in self.canary_platforms:
            logger.info(f"Routing platform {platform} to canary (in platform list)")
            return True
        
        # Hash-based routing for percentage-based rollout
        if request_hash is not None:
            threshold = (self.canary_traffic_percentage / 100.0) * 100
            if (request_hash % 100) < threshold:
                logger.debug(f"Routing request to canary (hash-based, {self.canary_traffic_percentage}%)")
                return True
        
        return False
    
    def get_target_version(
        self,
        user_id: str = None,
        platform: str = None,
        request_hash: int = None
    ) -> str:
        """Get target version for request"""
        if self.should_route_to_canary(user_id, platform, request_hash):
            return "green" if self.active_version == "blue" else "blue"
        return self.active_version
    
    def update_canary_traffic(self, percentage: int):
        """Update canary traffic percentage"""
        if not 0 <= percentage <= 100:
            raise ValueError("Percentage must be between 0 and 100")
        
        self.canary_traffic_percentage = percentage
        logger.info(f"Updated canary traffic to {percentage}%")
    
    def add_canary_user(self, user_id: str):
        """Add user to canary list"""
        if user_id not in self.canary_user_ids:
            self.canary_user_ids.append(user_id)
            logger.info(f"Added user {user_id} to canary list")
    
    def remove_canary_user(self, user_id: str):
        """Remove user from canary list"""
        if user_id in self.canary_user_ids:
            self.canary_user_ids.remove(user_id)
            logger.info(f"Removed user {user_id} from canary list")
    
    def add_canary_platform(self, platform: str):
        """Add platform to canary list"""
        if platform not in self.canary_platforms:
            self.canary_platforms.append(platform)
            logger.info(f"Added platform {platform} to canary list")
    
    def remove_canary_platform(self, platform: str):
        """Remove platform from canary list"""
        if platform in self.canary_platforms:
            self.canary_platforms.remove(platform)
            logger.info(f"Removed platform {platform} from canary list")
    
    def promote_canary_to_stable(self):
        """Promote canary to stable"""
        self.active_version = "green" if self.active_version == "blue" else "blue"
        self.canary_traffic_percentage = 0
        logger.info(f"Promoted canary to stable. Active version: {self.active_version}")
    
    def rollback_to_stable(self):
        """Rollback canary to stable"""
        self.canary_traffic_percentage = 0
        logger.warning(f"Rolled back canary. Active version: {self.active_version}")
    
    def to_dict(self) -> Dict:
        """Convert to dictionary"""
        return {
            "active_version": self.active_version,
            "canary_traffic_percentage": self.canary_traffic_percentage,
            "canary_user_ids": self.canary_user_ids,
            "canary_platforms": self.canary_platforms,
            "error_rate_threshold": self.error_rate_threshold,
            "latency_threshold_ms": self.latency_threshold_ms,
            "auto_rollback_enabled": self.auto_rollback_enabled,
        }


# Global instance
_gray_release_config = None


def get_gray_release_config() -> GrayReleaseConfig:
    """Get global gray release config"""
    global _gray_release_config
    if _gray_release_config is None:
        _gray_release_config = GrayReleaseConfig()
    return _gray_release_config


class GrayReleaseMiddleware:
    """Middleware for gray release routing"""
    
    def __init__(self, app):
        self.app = app
        self.config = get_gray_release_config()
    
    async def __call__(self, scope, receive, send):
        if scope["type"] != "http":
            await self.app(scope, receive, send)
            return
        
        # Get request info
        path = scope.get("path", "")
        headers = dict(scope.get("headers", []))
        
        # Extract user ID and platform from headers
        user_id = headers.get(b"x-user-id", b"").decode()
        platform = headers.get(b"x-platform", b"").decode()
        
        # Calculate request hash for percentage-based routing
        request_hash = hash(f"{path}{user_id}") % 100
        
        # Determine target version
        target_version = self.config.get_target_version(user_id, platform, request_hash)
        
        # Add target version to scope
        scope["target_version"] = target_version
        
        await self.app(scope, receive, send)
