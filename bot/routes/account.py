"""
Account management API routes
"""

import logging
from fastapi import APIRouter, HTTPException, Depends
from typing import Optional
import redis

from models.account import (
    LinkAccountRequest,
    UnlinkAccountRequest,
    UnlinkConfirmationRequest,
    PlatformAccountResponse,
    AccountListResponse,
    UnlinkConfirmationResponse,
    PlatformType,
)
from services.account_service import AccountService
from config import settings

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/api/v1/account", tags=["account"])

# Redis client
redis_client = redis.Redis(
    host=settings.REDIS_HOST,
    port=settings.REDIS_PORT,
    db=settings.REDIS_DB,
    password=settings.REDIS_PASSWORD if settings.REDIS_PASSWORD else None,
    decode_responses=True
)

# Account service
account_service = AccountService(redis_client)


def get_account_service() -> AccountService:
    """Get account service dependency"""
    return account_service


@router.get("/list", response_model=AccountListResponse)
async def list_accounts(
    customer_id: str,
    service: AccountService = Depends(get_account_service),
) -> AccountListResponse:
    """
    Get all linked accounts for customer
    
    Args:
        customer_id: Customer ID
        service: Account service
    
    Returns:
        List of linked accounts
    """
    try:
        # Get accounts
        accounts = service.get_customer_accounts(customer_id)
        
        # Convert to response format
        account_responses = [
            PlatformAccountResponse(**account.to_dict())
            for account in accounts
        ]
        
        logger.info(
            f"Retrieved accounts for customer: {customer_id}",
            extra={"account_count": len(accounts)}
        )
        
        return AccountListResponse(
            customer_id=customer_id,
            total_accounts=len(accounts),
            max_accounts=10,
            accounts=account_responses,
        )
    
    except Exception as e:
        logger.error(f"Error listing accounts: {e}")
        raise HTTPException(status_code=500, detail="Failed to list accounts")


@router.post("/link", response_model=PlatformAccountResponse)
async def link_account(
    request: LinkAccountRequest,
    service: AccountService = Depends(get_account_service),
) -> PlatformAccountResponse:
    """
    Link new platform account to customer
    
    Args:
        request: Link account request
        service: Account service
    
    Returns:
        Linked account details
    """
    try:
        # Link account
        account = service.link_account(
            customer_id=request.customer_id,
            platform=PlatformType(request.platform),
            platform_account_id=request.platform_account_id,
            platform_account_name=request.platform_account_name,
            access_token=request.access_token,
        )
        
        logger.info(
            f"Linked account: {request.platform_account_id}",
            extra={
                "customer_id": request.customer_id,
                "platform": request.platform,
            }
        )
        
        return PlatformAccountResponse(**account.to_dict())
    
    except ValueError as e:
        logger.warning(f"Invalid link request: {e}")
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        logger.error(f"Error linking account: {e}")
        raise HTTPException(status_code=500, detail="Failed to link account")


@router.post("/unlink/confirm", response_model=UnlinkConfirmationResponse)
async def request_unlink_confirmation(
    request: UnlinkConfirmationRequest,
    service: AccountService = Depends(get_account_service),
) -> UnlinkConfirmationResponse:
    """
    Request confirmation code for unlinking account
    
    Args:
        request: Unlink confirmation request
        service: Account service
    
    Returns:
        Confirmation code and details
    """
    try:
        # Request confirmation
        result = service.request_unlink_confirmation(request.account_id)
        
        logger.info(f"Requested unlink confirmation for account: {request.account_id}")
        
        return UnlinkConfirmationResponse(**result)
    
    except Exception as e:
        logger.error(f"Error requesting unlink confirmation: {e}")
        raise HTTPException(status_code=500, detail="Failed to request confirmation")


@router.post("/unlink")
async def unlink_account(
    request: UnlinkAccountRequest,
    service: AccountService = Depends(get_account_service),
):
    """
    Unlink platform account from customer
    
    Args:
        request: Unlink account request
        service: Account service
    
    Returns:
        Success status
    """
    try:
        # Unlink account
        service.unlink_account(
            account_id=request.account_id,
            confirmation_code=request.confirmation_code,
        )
        
        logger.info(f"Unlinked account: {request.account_id}")
        
        return {
            "status": "ok",
            "message": "Tài khoản đã được hủy liên kết thành công.",
            "account_id": request.account_id,
        }
    
    except ValueError as e:
        logger.warning(f"Invalid unlink request: {e}")
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        logger.error(f"Error unlinking account: {e}")
        raise HTTPException(status_code=500, detail="Failed to unlink account")


@router.get("/{account_id}", response_model=PlatformAccountResponse)
async def get_account(
    account_id: int,
    service: AccountService = Depends(get_account_service),
) -> PlatformAccountResponse:
    """
    Get account details
    
    Args:
        account_id: Account ID
        service: Account service
    
    Returns:
        Account details
    """
    try:
        # Get account
        account = service.get_account(account_id)
        
        if not account:
            raise HTTPException(status_code=404, detail="Account not found")
        
        logger.debug(f"Retrieved account: {account_id}")
        
        return PlatformAccountResponse(**account.to_dict())
    
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error retrieving account: {e}")
        raise HTTPException(status_code=500, detail="Failed to retrieve account")
