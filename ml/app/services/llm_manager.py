import os
import json
import logging
import asyncio
import httpx
import aiofiles
from pathlib import Path

logger = logging.getLogger(__name__)

CONFIG_PATH = os.getenv("LLM_CONFIG_PATH", "models/llm_config.json")
MODELS_DIR = Path("models")

class LLMManager:
    def __init__(self):
        MODELS_DIR.mkdir(exist_ok=True)
        self._ensure_config()

    def _ensure_config(self):
        if not os.path.exists(CONFIG_PATH):
            default_config = {
                "active_model": "Meta-Llama-3-8B-Instruct.Q4_K_M.gguf"
            }
            with open(CONFIG_PATH, "w") as f:
                json.dump(default_config, f)

    def get_active_model_path(self) -> str:
        try:
            with open(CONFIG_PATH, "r") as f:
                config = json.load(f)
                filename = config.get("active_model")
                if filename:
                    return str(MODELS_DIR / filename)
        except Exception as e:
            logger.error(f"Error reading LLM config: {e}")
        return str(MODELS_DIR / "Meta-Llama-3-8B-Instruct.Q4_K_M.gguf")

    def set_active_model(self, filename: str) -> bool:
        if not (MODELS_DIR / filename).exists():
            return False
        
        try:
            with open(CONFIG_PATH, "w") as f:
                json.dump({"active_model": filename}, f)
            return True
        except Exception as e:
            logger.error(f"Error saving LLM config: {e}")
            return False

    def list_local_models(self) -> list[dict]:
        models = []
        active_path = self.get_active_model_path()
        for p in MODELS_DIR.glob("*.gguf"):
            try:
                size_mb = p.stat().st_size / (1024 * 1024)
                models.append({
                    "filename": p.name,
                    "size_mb": round(size_mb, 2),
                    "is_active": (str(p) == active_path)
                })
            except Exception:
                continue
        return models

    async def download_model(self, url: str, target_filename: str):
        """Asynchronously downloads a model and saves it to the models directory."""
        target_path = MODELS_DIR / target_filename
        temp_path = MODELS_DIR / f"{target_filename}.downloading"
        
        try:
            async with httpx.AsyncClient() as client:
                async with client.stream('GET', url, follow_redirects=True) as response:
                    response.raise_for_status()
                    async with aiofiles.open(temp_path, 'wb') as f:
                        async for chunk in response.aiter_bytes(chunk_size=8192):
                            await f.write(chunk)
            
            # Download complete, rename
            temp_path.rename(target_path)
            logger.info(f"Successfully downloaded {target_filename}")
        except Exception as e:
            logger.error(f"Failed to download {target_filename}: {e}")
            if temp_path.exists():
                temp_path.unlink()

manager = LLMManager()
