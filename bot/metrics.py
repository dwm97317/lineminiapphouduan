"""
Prometheus metrics for Bot API
"""

from prometheus_client import Counter, Histogram, Gauge, generate_latest, CONTENT_TYPE_LATEST
from typing import Optional
import time

# Message metrics
bot_messages_total = Counter(
    'bot_messages_total',
    'Total messages processed',
    ['platform', 'status', 'message_type']
)

bot_messages_processing_seconds = Histogram(
    'bot_processing_seconds',
    'Message processing time in seconds',
    ['platform', 'endpoint'],
    buckets=(0.1, 0.5, 1.0, 2.0, 5.0, 10.0)
)

# Session metrics
bot_sessions_total = Gauge(
    'bot_sessions_total',
    'Total active sessions'
)

bot_sessions_created = Counter(
    'bot_sessions_created_total',
    'Total sessions created',
    ['platform']
)

# Package metrics
bot_packages_reported = Counter(
    'bot_packages_reported_total',
    'Total packages reported',
    ['platform', 'status']
)

bot_packages_processing_seconds = Histogram(
    'bot_packages_processing_seconds',
    'Package processing time in seconds',
    ['platform'],
    buckets=(0.5, 1.0, 2.0, 5.0, 10.0)
)

# API metrics
bot_api_requests_total = Counter(
    'bot_api_requests_total',
    'Total API requests',
    ['method', 'endpoint', 'status']
)

bot_api_request_duration_seconds = Histogram(
    'bot_api_request_duration_seconds',
    'API request duration in seconds',
    ['method', 'endpoint'],
    buckets=(0.01, 0.05, 0.1, 0.5, 1.0, 5.0)
)

# Webhook metrics
bot_webhook_requests_total = Counter(
    'bot_webhook_requests_total',
    'Total webhook requests',
    ['platform', 'status']
)

bot_webhook_signature_failures = Counter(
    'bot_webhook_signature_failures_total',
    'Total webhook signature verification failures',
    ['platform']
)

# Error metrics
bot_errors_total = Counter(
    'bot_errors_total',
    'Total errors',
    ['error_type', 'component']
)

# Database metrics
bot_database_connections = Gauge(
    'bot_database_connections',
    'Active database connections'
)

bot_database_query_duration_seconds = Histogram(
    'bot_database_query_duration_seconds',
    'Database query duration in seconds',
    ['query_type'],
    buckets=(0.01, 0.05, 0.1, 0.5, 1.0)
)

# Cache metrics
bot_cache_hits = Counter(
    'bot_cache_hits_total',
    'Total cache hits',
    ['cache_type']
)

bot_cache_misses = Counter(
    'bot_cache_misses_total',
    'Total cache misses',
    ['cache_type']
)

# Queue metrics
bot_queue_size = Gauge(
    'bot_queue_size',
    'Current queue size',
    ['queue_type']
)

bot_queue_processing_time = Histogram(
    'bot_queue_processing_time_seconds',
    'Queue item processing time in seconds',
    ['queue_type'],
    buckets=(0.1, 0.5, 1.0, 5.0, 10.0)
)


class MetricsContext:
    """Context manager for tracking metrics"""
    
    def __init__(self, metric_type: str, **labels):
        self.metric_type = metric_type
        self.labels = labels
        self.start_time = None
    
    def __enter__(self):
        self.start_time = time.time()
        return self
    
    def __exit__(self, exc_type, exc_val, exc_tb):
        duration = time.time() - self.start_time
        
        if self.metric_type == 'message_processing':
            bot_messages_processing_seconds.labels(**self.labels).observe(duration)
        elif self.metric_type == 'package_processing':
            bot_packages_processing_seconds.labels(**self.labels).observe(duration)
        elif self.metric_type == 'api_request':
            bot_api_request_duration_seconds.labels(**self.labels).observe(duration)
        elif self.metric_type == 'webhook_request':
            pass  # Handled separately
        elif self.metric_type == 'database_query':
            bot_database_query_duration_seconds.labels(**self.labels).observe(duration)
        elif self.metric_type == 'queue_processing':
            bot_queue_processing_time.labels(**self.labels).observe(duration)


def record_message(platform: str, status: str, message_type: str):
    """Record message metric"""
    bot_messages_total.labels(
        platform=platform,
        status=status,
        message_type=message_type
    ).inc()


def record_session_created(platform: str):
    """Record session creation"""
    bot_sessions_created.labels(platform=platform).inc()


def record_package(platform: str, status: str):
    """Record package metric"""
    bot_packages_reported.labels(
        platform=platform,
        status=status
    ).inc()


def record_webhook_request(platform: str, status: str):
    """Record webhook request"""
    bot_webhook_requests_total.labels(
        platform=platform,
        status=status
    ).inc()


def record_webhook_signature_failure(platform: str):
    """Record webhook signature failure"""
    bot_webhook_signature_failures.labels(platform=platform).inc()


def record_error(error_type: str, component: str):
    """Record error"""
    bot_errors_total.labels(
        error_type=error_type,
        component=component
    ).inc()


def record_cache_hit(cache_type: str):
    """Record cache hit"""
    bot_cache_hits.labels(cache_type=cache_type).inc()


def record_cache_miss(cache_type: str):
    """Record cache miss"""
    bot_cache_misses.labels(cache_type=cache_type).inc()


def set_active_sessions(count: int):
    """Set active sessions count"""
    bot_sessions_total.set(count)


def set_queue_size(queue_type: str, size: int):
    """Set queue size"""
    bot_queue_size.labels(queue_type=queue_type).set(size)


def set_database_connections(count: int):
    """Set database connections count"""
    bot_database_connections.set(count)


def get_metrics():
    """Get all metrics in Prometheus format"""
    return generate_latest()
