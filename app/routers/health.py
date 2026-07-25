import psutil
from fastapi import APIRouter
from app.models.schemas import HealthResponse

router = APIRouter(tags=["health"])


@router.get("/api/health", response_model=HealthResponse)
async def health():
    cuda_available = False
    try:
        import torch
        cuda_available = torch.cuda.is_available()
    except ImportError:
        pass

    mem = psutil.virtual_memory()
    return HealthResponse(
        status="ok",
        version="1.0.0",
        models_loaded=[],
        cuda_available=cuda_available,
        memory_mb={"used": mem.used / 1024 / 1024, "total": mem.total / 1024 / 1024},
    )
