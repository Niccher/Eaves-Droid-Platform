from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel
from app.services.llm_provider import generate_text
from app.dependencies import verify_internal_token
import logging

logger = logging.getLogger(__name__)

router = APIRouter(tags=["llm"])

class LLMRequest(BaseModel):
    system_prompt: str
    user_prompt: str

class LLMResponse(BaseModel):
    text: str

@router.post("/api/v1/llm/generate", response_model=LLMResponse, dependencies=[Depends(verify_internal_token)])
async def llm_generate(req: LLMRequest):
    try:
        result = await generate_text(req.system_prompt, req.user_prompt)
        return LLMResponse(text=result)
    except Exception as e:
        logger.error(f"LLM generation failed: {e}")
        raise HTTPException(status_code=500, detail="LLM generation failed")
