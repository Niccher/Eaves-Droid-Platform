from fastapi import APIRouter, HTTPException, Depends, BackgroundTasks
from pydantic import BaseModel
from typing import List
from app.services.llm_manager import manager
from app.dependencies import verify_internal_token

router = APIRouter(prefix="/api/v1/models/llm", tags=["llm_admin"], dependencies=[Depends(verify_internal_token)])

class LLMModelResponse(BaseModel):
    filename: str
    size_mb: float
    is_active: bool

class SetActiveRequest(BaseModel):
    filename: str

class DownloadRequest(BaseModel):
    url: str
    target_filename: str

@router.get("", response_model=List[LLMModelResponse])
async def list_models():
    """Lists local .gguf models and shows which one is active."""
    return manager.list_local_models()

@router.post("/active")
async def set_active_model(req: SetActiveRequest):
    """Sets the active model."""
    success = manager.set_active_model(req.filename)
    if not success:
        raise HTTPException(status_code=400, detail="Failed to set model. Check if file exists.")
    return {"message": "Active model updated successfully", "active_model": req.filename}

@router.post("/download")
async def download_model(req: DownloadRequest, background_tasks: BackgroundTasks):
    """Starts a background task to download a model from a URL."""
    if not req.target_filename.endswith(".gguf"):
        raise HTTPException(status_code=400, detail="Target filename must end with .gguf")
        
    background_tasks.add_task(manager.download_model, req.url, req.target_filename)
    return {"message": "Download started in background"}
