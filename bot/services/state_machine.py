"""
Order session state machine
"""

import logging
from typing import Optional, Callable, Dict, List
from enum import Enum
from datetime import datetime

logger = logging.getLogger(__name__)


class OrderSessionState(str, Enum):
    """Order session states"""
    COLLECTING = "collecting"      # Collecting order information
    READY = "ready"                # Ready for binding
    BOUND = "bound"                # Bound to user
    CLOSED = "closed"              # Session closed


class StateTransitionError(Exception):
    """State transition error"""
    pass


class OrderSessionStateMachine:
    """State machine for order session"""
    
    # Valid state transitions
    VALID_TRANSITIONS = {
        OrderSessionState.COLLECTING: [
            OrderSessionState.READY,
            OrderSessionState.CLOSED,
        ],
        OrderSessionState.READY: [
            OrderSessionState.BOUND,
            OrderSessionState.CLOSED,
        ],
        OrderSessionState.BOUND: [
            OrderSessionState.CLOSED,
        ],
        OrderSessionState.CLOSED: [],
    }
    
    def __init__(self, initial_state: OrderSessionState = OrderSessionState.COLLECTING):
        """Initialize state machine"""
        self.current_state = initial_state
        self.state_history: List[Dict] = []
        self.callbacks: Dict[str, List[Callable]] = {
            "on_enter": [],
            "on_exit": [],
            "on_transition": [],
        }
        
        # Record initial state
        self._record_state_change(initial_state, None, "initialization")
    
    def can_transition_to(self, target_state: OrderSessionState) -> bool:
        """Check if transition is valid"""
        return target_state in self.VALID_TRANSITIONS.get(self.current_state, [])
    
    def transition_to(
        self,
        target_state: OrderSessionState,
        reason: Optional[str] = None,
        metadata: Optional[Dict] = None,
    ) -> bool:
        """Transition to target state"""
        
        # Check if transition is valid
        if not self.can_transition_to(target_state):
            raise StateTransitionError(
                f"Cannot transition from {self.current_state.value} to {target_state.value}"
            )
        
        # Call on_exit callbacks
        self._call_callbacks("on_exit", self.current_state)
        
        # Perform transition
        previous_state = self.current_state
        self.current_state = target_state
        
        # Record state change
        self._record_state_change(target_state, previous_state, reason, metadata)
        
        # Call on_enter callbacks
        self._call_callbacks("on_enter", target_state)
        
        # Call on_transition callbacks
        self._call_callbacks("on_transition", {
            "from": previous_state,
            "to": target_state,
            "reason": reason,
        })
        
        logger.info(
            f"State transition: {previous_state.value} → {target_state.value}",
            extra={
                "reason": reason,
                "metadata": metadata,
            }
        )
        
        return True
    
    def _record_state_change(
        self,
        state: OrderSessionState,
        previous_state: Optional[OrderSessionState],
        reason: Optional[str],
        metadata: Optional[Dict] = None,
    ):
        """Record state change in history"""
        self.state_history.append({
            "state": state.value,
            "previous_state": previous_state.value if previous_state else None,
            "reason": reason,
            "timestamp": datetime.utcnow().isoformat(),
            "metadata": metadata or {},
        })
    
    def register_callback(self, event: str, callback: Callable):
        """Register callback for event"""
        if event not in self.callbacks:
            raise ValueError(f"Unknown event: {event}")
        self.callbacks[event].append(callback)
    
    def _call_callbacks(self, event: str, *args, **kwargs):
        """Call all callbacks for event"""
        for callback in self.callbacks.get(event, []):
            try:
                callback(*args, **kwargs)
            except Exception as e:
                logger.error(f"Error calling callback for {event}: {e}")
    
    def get_state(self) -> OrderSessionState:
        """Get current state"""
        return self.current_state
    
    def get_state_value(self) -> str:
        """Get current state value"""
        return self.current_state.value
    
    def get_history(self) -> List[Dict]:
        """Get state history"""
        return self.state_history
    
    def to_dict(self) -> Dict:
        """Convert to dictionary"""
        return {
            "current_state": self.current_state.value,
            "history": self.state_history,
        }


class StateTransitionValidator:
    """Validate state transitions"""
    
    @staticmethod
    def validate_collecting_to_ready(session_data: Dict) -> bool:
        """Validate transition from collecting to ready"""
        # Check if required information is collected
        context = session_data.get("conversation_context", {})
        
        # At least one piece of information should be collected
        has_amount = context.get("amount_vnd") is not None
        has_date = context.get("date") is not None
        has_package = context.get("package_code") is not None
        
        return has_amount or has_date or has_package
    
    @staticmethod
    def validate_ready_to_bound(session_data: Dict) -> bool:
        """Validate transition from ready to bound"""
        # Check if user is confirmed
        context = session_data.get("conversation_context", {})
        return context.get("user_confirmed", False)
    
    @staticmethod
    def validate_bound_to_closed(session_data: Dict) -> bool:
        """Validate transition from bound to closed"""
        # Always allow closing
        return True
    
    @staticmethod
    def validate_transition(
        from_state: OrderSessionState,
        to_state: OrderSessionState,
        session_data: Dict,
    ) -> tuple[bool, Optional[str]]:
        """Validate state transition"""
        
        if from_state == OrderSessionState.COLLECTING and to_state == OrderSessionState.READY:
            if not StateTransitionValidator.validate_collecting_to_ready(session_data):
                return False, "Not enough information collected"
        
        elif from_state == OrderSessionState.READY and to_state == OrderSessionState.BOUND:
            if not StateTransitionValidator.validate_ready_to_bound(session_data):
                return False, "User not confirmed"
        
        elif from_state == OrderSessionState.BOUND and to_state == OrderSessionState.CLOSED:
            if not StateTransitionValidator.validate_bound_to_closed(session_data):
                return False, "Cannot close bound session"
        
        return True, None
