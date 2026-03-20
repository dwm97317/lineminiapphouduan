"""
Webhook signature verification for Meta APIs
"""

import hmac
import hashlib
import logging
from typing import Optional

logger = logging.getLogger(__name__)


class WebhookValidator:
    """Validate webhook signatures from Meta (Facebook/Instagram)"""
    
    @staticmethod
    def verify_signature(
        body: bytes,
        signature: str,
        app_secret: str
    ) -> bool:
        """
        Verify webhook signature
        
        Args:
            body: Raw request body (bytes)
            signature: X-Hub-Signature header value (format: sha1=<hash>)
            app_secret: Facebook App Secret
        
        Returns:
            True if signature is valid, False otherwise
        """
        try:
            # Extract hash from signature header
            # Format: sha1=<hash>
            if not signature or '=' not in signature:
                logger.warning("Invalid signature format")
                return False
            
            hash_algorithm, hash_value = signature.split('=', 1)
            
            if hash_algorithm != 'sha1':
                logger.warning(f"Unsupported hash algorithm: {hash_algorithm}")
                return False
            
            # Calculate expected hash
            expected_hash = hmac.new(
                app_secret.encode('utf-8'),
                body,
                hashlib.sha1
            ).hexdigest()
            
            # Compare hashes (constant-time comparison)
            is_valid = hmac.compare_digest(expected_hash, hash_value)
            
            if not is_valid:
                logger.warning("Webhook signature verification failed")
            else:
                logger.debug("Webhook signature verified successfully")
            
            return is_valid
            
        except Exception as e:
            logger.error(f"Error verifying webhook signature: {e}")
            return False
    
    @staticmethod
    def verify_facebook_signature(
        body: bytes,
        signature: str,
        app_secret: str
    ) -> bool:
        """
        Verify Facebook webhook signature
        
        Args:
            body: Raw request body (bytes)
            signature: X-Hub-Signature header value
            app_secret: Facebook App Secret
        
        Returns:
            True if signature is valid
        """
        return WebhookValidator.verify_signature(body, signature, app_secret)
    
    @staticmethod
    def verify_instagram_signature(
        body: bytes,
        signature: str,
        app_secret: str
    ) -> bool:
        """
        Verify Instagram webhook signature
        
        Args:
            body: Raw request body (bytes)
            signature: X-Hub-Signature header value
            app_secret: Facebook App Secret
        
        Returns:
            True if signature is valid
        """
        return WebhookValidator.verify_signature(body, signature, app_secret)
