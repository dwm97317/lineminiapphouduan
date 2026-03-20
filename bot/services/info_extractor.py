"""
Information extractor for order messages
"""

import re
import logging
from typing import Optional, Dict, Any
from datetime import datetime

logger = logging.getLogger(__name__)


class InfoExtractor:
    """Extract information from messages"""
    
    # Regex patterns
    VND_PATTERN = r'(\d{1,3}(?:[.,]\d{3})*|\d+)\s*(?:đ|VND|vnd|₫)'
    DATE_PATTERN = r'(\d{1,2})[/-](\d{1,2})[/-](\d{2,4})'
    PACKAGE_CODE_PATTERN = r'([A-Z]{2}\d{9}[A-Z]{2}|[A-Z0-9]{10,20})'
    PHONE_PATTERN = r'(?:\+84|0)(?:9|8|7|6|5|3|2|1)\d{8,9}'
    EMAIL_PATTERN = r'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}'
    
    @staticmethod
    def extract_amount_vnd(text: str) -> Optional[float]:
        """Extract VND amount from text"""
        try:
            matches = re.findall(InfoExtractor.VND_PATTERN, text, re.IGNORECASE)
            if not matches:
                return None
            
            # Get the first match
            amount_str = matches[0]
            
            # Remove separators and convert to float
            amount_str = amount_str.replace('.', '').replace(',', '')
            amount = float(amount_str)
            
            logger.debug(f"Extracted VND amount: {amount}")
            return amount
        
        except Exception as e:
            logger.error(f"Error extracting VND amount: {e}")
            return None
    
    @staticmethod
    def extract_date(text: str) -> Optional[str]:
        """Extract date from text (YYYY-MM-DD format)"""
        try:
            matches = re.findall(InfoExtractor.DATE_PATTERN, text)
            if not matches:
                return None
            
            # Get the first match
            day, month, year = matches[0]
            day = int(day)
            month = int(month)
            year = int(year)
            
            # Handle 2-digit year
            if year < 100:
                year += 2000 if year < 50 else 1900
            
            # Validate date
            try:
                date_obj = datetime(year, month, day)
                date_str = date_obj.strftime("%Y-%m-%d")
                logger.debug(f"Extracted date: {date_str}")
                return date_str
            except ValueError:
                logger.warning(f"Invalid date: {day}/{month}/{year}")
                return None
        
        except Exception as e:
            logger.error(f"Error extracting date: {e}")
            return None
    
    @staticmethod
    def extract_package_code(text: str) -> Optional[str]:
        """Extract package code from text"""
        try:
            matches = re.findall(InfoExtractor.PACKAGE_CODE_PATTERN, text)
            if not matches:
                return None
            
            # Get the first match
            package_code = matches[0].upper()
            logger.debug(f"Extracted package code: {package_code}")
            return package_code
        
        except Exception as e:
            logger.error(f"Error extracting package code: {e}")
            return None
    
    @staticmethod
    def extract_phone(text: str) -> Optional[str]:
        """Extract phone number from text"""
        try:
            matches = re.findall(InfoExtractor.PHONE_PATTERN, text)
            if not matches:
                return None
            
            phone = matches[0]
            logger.debug(f"Extracted phone: {phone}")
            return phone
        
        except Exception as e:
            logger.error(f"Error extracting phone: {e}")
            return None
    
    @staticmethod
    def extract_email(text: str) -> Optional[str]:
        """Extract email from text"""
        try:
            matches = re.findall(InfoExtractor.EMAIL_PATTERN, text)
            if not matches:
                return None
            
            email = matches[0]
            logger.debug(f"Extracted email: {email}")
            return email
        
        except Exception as e:
            logger.error(f"Error extracting email: {e}")
            return None
    
    @staticmethod
    def extract_all(text: str) -> Dict[str, Any]:
        """Extract all information from text"""
        return {
            "amount_vnd": InfoExtractor.extract_amount_vnd(text),
            "date": InfoExtractor.extract_date(text),
            "package_code": InfoExtractor.extract_package_code(text),
            "phone": InfoExtractor.extract_phone(text),
            "email": InfoExtractor.extract_email(text),
            "raw_text": text,
        }
    
    @staticmethod
    def calculate_confidence(extracted_info: Dict[str, Any]) -> float:
        """Calculate extraction confidence (0-1)"""
        confidence = 0.0
        total_fields = 5  # amount, date, package, phone, email
        
        if extracted_info.get("amount_vnd") is not None:
            confidence += 0.2
        if extracted_info.get("date") is not None:
            confidence += 0.2
        if extracted_info.get("package_code") is not None:
            confidence += 0.2
        if extracted_info.get("phone") is not None:
            confidence += 0.2
        if extracted_info.get("email") is not None:
            confidence += 0.2
        
        return min(confidence, 1.0)


class MessageAnalyzer:
    """Analyze messages for order information"""
    
    @staticmethod
    def analyze(text: str) -> Dict[str, Any]:
        """Analyze message and extract information"""
        extracted = InfoExtractor.extract_all(text)
        confidence = InfoExtractor.calculate_confidence(extracted)
        
        return {
            "extracted_info": extracted,
            "confidence": confidence,
            "has_amount": extracted.get("amount_vnd") is not None,
            "has_date": extracted.get("date") is not None,
            "has_package": extracted.get("package_code") is not None,
            "has_contact": extracted.get("phone") is not None or extracted.get("email") is not None,
        }
