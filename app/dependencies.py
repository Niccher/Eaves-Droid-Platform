import secrets
from fastapi import Header, HTTPException, status
from app.config import settings

async def verify_internal_token(x_internal_token: str = Header(None)):
    """
    FastAPI dependency to verify the internal API token using a secure,
    constant-time string comparison to prevent timing attacks.
    """
    if not x_internal_token:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Missing internal authentication token"
        )
    
    if not secrets.compare_digest(x_internal_token, settings.internal_token):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid internal authentication token"
        )
