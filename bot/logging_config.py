"""
Structured logging configuration for production
"""

import logging
import json
from datetime import datetime
from typing import Any, Dict
import sys

class StructuredFormatter(logging.Formatter):
    """JSON structured logging formatter"""
    
    def format(self, record: logging.LogRecord) -> str:
        """Format log record as JSON"""
        log_data: Dict[str, Any] = {
            "timestamp": datetime.utcnow().isoformat(),
            "level": record.levelname,
            "logger": record.name,
            "message": record.getMessage(),
            "module": record.module,
            "function": record.funcName,
            "line": record.lineno,
        }
        
        # Add exception info if present
        if record.exc_info:
            log_data["exception"] = self.formatException(record.exc_info)
        
        # Add extra fields
        if hasattr(record, 'extra_fields'):
            log_data.update(record.extra_fields)
        
        # Add request context if available
        if hasattr(record, 'request_id'):
            log_data["request_id"] = record.request_id
        if hasattr(record, 'user_id'):
            log_data["user_id"] = record.user_id
        if hasattr(record, 'duration_ms'):
            log_data["duration_ms"] = record.duration_ms
        
        return json.dumps(log_data, default=str)


class StructuredLogger(logging.Logger):
    """Custom logger with structured logging support"""
    
    def __init__(self, name: str, level: int = logging.NOTSET):
        super().__init__(name, level)
    
    def log_with_context(
        self,
        level: int,
        message: str,
        **context
    ):
        """Log with additional context"""
        extra = logging.LogRecord(
            name=self.name,
            level=level,
            pathname="",
            lineno=0,
            msg=message,
            args=(),
            exc_info=None
        )
        extra.extra_fields = context
        self.handle(extra)
    
    def info_with_context(self, message: str, **context):
        """Info log with context"""
        self.log_with_context(logging.INFO, message, **context)
    
    def warning_with_context(self, message: str, **context):
        """Warning log with context"""
        self.log_with_context(logging.WARNING, message, **context)
    
    def error_with_context(self, message: str, **context):
        """Error log with context"""
        self.log_with_context(logging.ERROR, message, **context)


def setup_logging(environment: str = "production"):
    """Setup structured logging"""
    
    # Set custom logger class
    logging.setLoggerClass(StructuredLogger)
    
    # Create root logger
    root_logger = logging.getLogger()
    root_logger.setLevel(logging.INFO)
    
    # Remove existing handlers
    for handler in root_logger.handlers[:]:
        root_logger.removeHandler(handler)
    
    # Console handler (stdout)
    console_handler = logging.StreamHandler(sys.stdout)
    console_handler.setLevel(logging.INFO)
    console_formatter = StructuredFormatter()
    console_handler.setFormatter(console_formatter)
    root_logger.addHandler(console_handler)
    
    # Error handler (stderr)
    error_handler = logging.StreamHandler(sys.stderr)
    error_handler.setLevel(logging.ERROR)
    error_handler.setFormatter(console_formatter)
    root_logger.addHandler(error_handler)
    
    # Set specific loggers
    logging.getLogger("uvicorn").setLevel(logging.INFO)
    logging.getLogger("uvicorn.access").setLevel(logging.INFO)
    logging.getLogger("fastapi").setLevel(logging.INFO)
    
    return root_logger


def get_logger(name: str) -> StructuredLogger:
    """Get structured logger instance"""
    return logging.getLogger(name)
