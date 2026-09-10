import os
import logging
import httpx
from typing import Optional

logger = logging.getLogger(__name__)

_llama = None
_LLAMA_MODEL_PATH = os.getenv("LLAMA_MODEL_PATH", "models/Meta-Llama-3-8B-Instruct.Q4_K_M.gguf")

def get_llama():
    global _llama
    if _llama is None:
        if os.path.exists(_LLAMA_MODEL_PATH):
            try:
                from llama_cpp import Llama
                logger.info(f"Loading local LLM from {_LLAMA_MODEL_PATH}")
                # We offload everything we can to CPU since Railway provides good RAM
                _llama = Llama(model_path=_LLAMA_MODEL_PATH, n_ctx=8192, n_threads=8, verbose=False)
            except Exception as e:
                logger.error(f"Failed to load llama.cpp: {e}")
                return None
        else:
            logger.info(f"Local LLM not found at {_LLAMA_MODEL_PATH}. Falling back to external API.")
    return _llama

async def generate_text(system_prompt: str, user_prompt: str) -> str:
    """
    Generates text using the local llama.cpp model if available.
    Falls back to Gemini/DeepSeek API if local model is missing or fails.
    """
    llm = get_llama()
    
    if llm is not None:
        try:
            logger.info("Using local LLM via llama-cpp-python.")
            response = llm.create_chat_completion(
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": user_prompt}
                ],
                max_tokens=1024,
                temperature=0.2,
            )
            return response["choices"][0]["message"]["content"]
        except Exception as e:
            logger.error(f"Local LLM generation failed: {e}. Falling back to external API.")
            
    # Fallback to External API (Gemini or DeepSeek)
    return await _generate_text_external(system_prompt, user_prompt)

async def _generate_text_external(system_prompt: str, user_prompt: str) -> str:
    """Fallback logic using Gemini API (or similar external provider)."""
    api_key = os.getenv("GEMINI_API_KEY")
    if not api_key:
        logger.warning("No local model and no GEMINI_API_KEY configured. Returning error message.")
        return '{"error": "LLM not configured (missing local model and API key)"}'
        
    try:
        async with httpx.AsyncClient() as client:
            url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={api_key}"
            payload = {
                "system_instruction": {
                    "parts": [{"text": system_prompt}]
                },
                "contents": [
                    {
                        "role": "user",
                        "parts": [{"text": user_prompt}]
                    }
                ]
            }
            resp = await client.post(url, json=payload, timeout=60.0)
            if resp.status_code == 200:
                data = resp.json()
                return data["candidates"][0]["content"]["parts"][0]["text"]
            else:
                logger.error(f"Gemini API returned {resp.status_code}: {resp.text}")
                return '{"error": "External LLM API failure"}'
    except Exception as e:
        logger.error(f"External LLM API exception: {e}")
        return '{"error": "External LLM connection error"}'
