from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
import redis
import logging
from config import settings

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Initialize FastAPI app
app = FastAPI(
    title="Bot API",
    description="Bot API with FastAPI",
    version="1.0.0"
)

# Configure CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.CORS_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Initialize Redis connection
redis_client = redis.Redis(
    host=settings.REDIS_HOST,
    port=settings.REDIS_PORT,
    db=settings.REDIS_DB,
    password=settings.REDIS_PASSWORD if settings.REDIS_PASSWORD else None,
    decode_responses=True
)

@app.on_event("startup")
async def startup_event():
    """Test Redis connection on startup"""
    try:
        redis_client.ping()
        logger.info("✓ Redis connection successful")
    except Exception as e:
        logger.error(f"✗ Redis connection failed: {e}")

@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {"status": "ok"}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        app,
        host=settings.API_HOST,
        port=settings.API_PORT
    )
