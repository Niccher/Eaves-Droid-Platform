"""
Health endpoint — reports service status, loaded models, memory, and cache stats.
"""

import time
import psutil
import importlib
from fastapi import APIRouter
from app.models.schemas import HealthResponse, ModuleCheck
from app.models.cache import cache_stats
from app.routers.analyze import DETECTOR_MAP

router = APIRouter(tags=["health"])

_start_time = time.time()


def _check_module(module_path: str, name: str) -> ModuleCheck:
    """Try importing and instantiating a detector to verify it works."""
    try:
        mod = importlib.import_module(module_path)
        for attr in dir(mod):
            obj = getattr(mod, attr)
            if hasattr(obj, "algorithm_id") and hasattr(obj, "detect"):
                return ModuleCheck(name=name, status="ok", message="loaded")
        return ModuleCheck(name=name, status="warn", message="no detector class found")
    except Exception as e:
        return ModuleCheck(name=name, status="error", message=str(e))


def _check_database() -> str:
    """Quick connectivity check against the shared MySQL."""
    try:
        from app.utils.db import get_engine
        from sqlalchemy import text
        with get_engine().connect() as conn:
            conn.execute(text("SELECT 1"))
        return "connected"
    except Exception as e:
        return f"error: {e}"


_DETECTOR_MODULES = [
    ("app.detectors.sms_bert",        "BERT Phishing"),
    ("app.detectors.calls_isolation",  "Isolation Forest (Calls)"),
    ("app.detectors.device_oneclass",  "One-Class SVM (Device)"),
    ("app.detectors.activity_lstm",    "LSTM Sequence (Activity)"),
    ("app.detectors.apps_autoencoder", "Autoencoder (Apps)"),
    ("app.detectors.contacts_graph",   "Graph (Contacts)"),
    ("app.detectors.files_entropy",    "File Entropy"),
]


@router.get("/api/health", response_model=HealthResponse)
async def health():
    mem = psutil.virtual_memory()
    cs = cache_stats()

    cuda_avail = False
    cuda_dev = ""
    try:
        import torch
        cuda_avail = torch.cuda.is_available()
        if cuda_avail:
            cuda_dev = torch.cuda.get_device_name(0)
    except ImportError:
        pass

    return HealthResponse(
        status="ok",
        version="1.0.0",
        models_loaded=sorted(DETECTOR_MAP.keys()),
        cuda_available=cuda_avail,
        cuda_device=cuda_dev,
        memory_mb={"used": mem.used / 1024 / 1024, "total": mem.total / 1024 / 1024},
        cache_entries=cs["total_entries"],
        modules=[_check_module(p, n) for p, n in _DETECTOR_MODULES],
        database=_check_database(),
        uptime_seconds=time.time() - _start_time,
    )
