from fastapi import APIRouter
from app.models.schemas import ModelInfo
from app.models.registry import list_available_algorithms

router = APIRouter(tags=["models"])


@router.get("/api/models", response_model=list[ModelInfo])
async def list_models():
    return [ModelInfo(**alg) for alg in list_available_algorithms()]
