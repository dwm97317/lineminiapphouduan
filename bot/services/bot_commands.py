"""
Bot commands handler for multi-account management
"""

import logging
from typing import Optional, Dict, Any, List
from enum import Enum

from services.account_service import AccountService
from services.session_service import SessionService
from models.account import PlatformType

logger = logging.getLogger(__name__)


class BotCommand(str, Enum):
    """Bot commands"""
    VIEW_ACCOUNTS = "xem tài khoản đã liên kết"
    ADD_PACKAGE = "bổ sung mã bưu kiện"
    ORDER_HISTORY = "đơn hàng của tôi"


class BotCommandHandler:
    """Handle bot commands"""
    
    def __init__(
        self,
        account_service: AccountService,
        session_service: SessionService,
    ):
        """Initialize command handler"""
        self.account_service = account_service
        self.session_service = session_service
    
    def handle_command(
        self,
        user_id: str,
        customer_id: str,
        command_text: str,
    ) -> Dict[str, Any]:
        """Handle bot command"""
        
        command_lower = command_text.lower().strip()
        
        # View linked accounts
        if command_lower == BotCommand.VIEW_ACCOUNTS.value:
            return self.handle_view_accounts(customer_id)
        
        # Add package code
        elif command_lower == BotCommand.ADD_PACKAGE.value:
            return self.handle_add_package(customer_id)
        
        # Order history
        elif command_lower == BotCommand.ORDER_HISTORY.value:
            return self.handle_order_history(customer_id)
        
        else:
            return {
                "status": "unknown_command",
                "message": "Lệnh không được nhận diện. Vui lòng thử lại.",
            }
    
    def handle_view_accounts(self, customer_id: str) -> Dict[str, Any]:
        """Handle 「Xem tài khoản đã liên kết」command"""
        
        try:
            # Get customer accounts
            accounts = self.account_service.get_customer_accounts(customer_id)
            
            if not accounts:
                return {
                    "status": "no_accounts",
                    "message": "Bạn chưa liên kết tài khoản nào. Vui lòng liên kết tài khoản để bắt đầu.",
                }
            
            # Format account list
            account_list = []
            for i, account in enumerate(accounts, 1):
                account_list.append({
                    "number": i,
                    "platform": account.platform.value,
                    "account_name": account.platform_account_name or account.platform_account_id,
                    "status": account.status.value,
                    "linked_date": account.created_at.strftime("%d/%m/%Y"),
                })
            
            # Build message
            message = "📱 Tài khoản đã liên kết:\n\n"
            for acc in account_list:
                message += f"{acc['number']}. {acc['platform'].upper()}: {acc['account_name']}\n"
                message += f"   Trạng thái: {acc['status']}\n"
                message += f"   Liên kết từ: {acc['linked_date']}\n\n"
            
            message += f"Tổng cộng: {len(accounts)}/10 tài khoản"
            
            logger.info(
                f"Viewed accounts for customer: {customer_id}",
                extra={"account_count": len(accounts)}
            )
            
            return {
                "status": "success",
                "message": message,
                "accounts": account_list,
            }
        
        except Exception as e:
            logger.error(f"Error viewing accounts: {e}")
            return {
                "status": "error",
                "message": "Có lỗi xảy ra. Vui lòng thử lại sau.",
            }
    
    def handle_add_package(self, customer_id: str) -> Dict[str, Any]:
        """Handle 「Bổ sung mã bưu kiện」command"""
        
        try:
            # Get customer accounts
            accounts = self.account_service.get_customer_accounts(customer_id)
            
            if not accounts:
                return {
                    "status": "no_accounts",
                    "message": "Bạn chưa liên kết tài khoản nào. Vui lòng liên kết tài khoản trước.",
                }
            
            # TODO: Get pending packages from database
            # For now, return sample data
            pending_packages = [
                {
                    "package_code": "VN1234567890VN",
                    "status": "pending",
                    "created_date": "15/01/2024",
                },
                {
                    "package_code": "VN0987654321VN",
                    "status": "pending",
                    "created_date": "14/01/2024",
                },
            ]
            
            if not pending_packages:
                return {
                    "status": "no_pending",
                    "message": "Không có đơn chờ nào. Tất cả đơn hàng đã được xử lý.",
                }
            
            # Build message
            message = "📦 Danh sách đơn chờ bưu kiện:\n\n"
            for i, pkg in enumerate(pending_packages, 1):
                message += f"{i}. Mã: {pkg['package_code']}\n"
                message += f"   Trạng thái: {pkg['status']}\n"
                message += f"   Ngày tạo: {pkg['created_date']}\n\n"
            
            message += "Vui lòng gửi mã bưu kiện để bổ sung thông tin."
            
            logger.info(
                f"Viewed pending packages for customer: {customer_id}",
                extra={"package_count": len(pending_packages)}
            )
            
            return {
                "status": "success",
                "message": message,
                "packages": pending_packages,
            }
        
        except Exception as e:
            logger.error(f"Error viewing pending packages: {e}")
            return {
                "status": "error",
                "message": "Có lỗi xảy ra. Vui lòng thử lại sau.",
            }
    
    def handle_order_history(self, customer_id: str) -> Dict[str, Any]:
        """Handle 「Đơn hàng của tôi」command"""
        
        try:
            # Get customer accounts
            accounts = self.account_service.get_customer_accounts(customer_id)
            
            if not accounts:
                return {
                    "status": "no_accounts",
                    "message": "Bạn chưa liên kết tài khoản nào. Vui lòng liên kết tài khoản trước.",
                }
            
            # TODO: Get order history from database
            # For now, return sample data
            orders = [
                {
                    "order_id": "ORD001",
                    "package_code": "VN1234567890VN",
                    "amount": "500.000 đ",
                    "status": "completed",
                    "created_date": "15/01/2024",
                    "completed_date": "16/01/2024",
                },
                {
                    "order_id": "ORD002",
                    "package_code": "VN0987654321VN",
                    "amount": "300.000 đ",
                    "status": "processing",
                    "created_date": "14/01/2024",
                    "completed_date": None,
                },
            ]
            
            if not orders:
                return {
                    "status": "no_orders",
                    "message": "Bạn chưa có đơn hàng nào.",
                }
            
            # Build message
            message = "📋 Lịch sử đơn hàng:\n\n"
            for i, order in enumerate(orders, 1):
                message += f"{i}. Đơn #{order['order_id']}\n"
                message += f"   Mã bưu kiện: {order['package_code']}\n"
                message += f"   Số tiền: {order['amount']}\n"
                message += f"   Trạng thái: {order['status']}\n"
                message += f"   Ngày tạo: {order['created_date']}\n"
                if order['completed_date']:
                    message += f"   Hoàn thành: {order['completed_date']}\n"
                message += "\n"
            
            message += f"Tổng cộng: {len(orders)} đơn hàng"
            
            logger.info(
                f"Viewed order history for customer: {customer_id}",
                extra={"order_count": len(orders)}
            )
            
            return {
                "status": "success",
                "message": message,
                "orders": orders,
            }
        
        except Exception as e:
            logger.error(f"Error viewing order history: {e}")
            return {
                "status": "error",
                "message": "Có lỗi xảy ra. Vui lòng thử lại sau.",
            }
