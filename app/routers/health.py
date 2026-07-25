"""
Health endpoint — reports service status, loaded models, memory, and cache stats.
"""

import psutil
from fastapi import APIRouter
from app.models.schemas import HealthResponse
from app.models.cache import cache_stats
from app.routers.analyze import DETECTOR_MAP

router = APIRouter(tags=["health"])


@router.get("/api/health", response_model=HealthResponse)
async def health():
    mem = psutil.virtual_memory()
    cs = cache_stats()
    return HealthResponse(
        status="ok",
        version="1.0.0",
        models_loaded=sorted(DETECTOR_MAP.keys()),
        cuda_available=False,
        memory_mb={"used": mem.used / 1024 / 1024, "total": mem.total / 1024 / 1024},
        cache_entries=cs["total_entries"],
    )
