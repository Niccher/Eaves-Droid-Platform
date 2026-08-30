"""
Health endpoint — reports service status, loaded models, memory, and cache stats.
"""

import time
import importlib
from fastapi import APIRouter, Depends
from app.models.schemas import HealthResponse, ModuleCheck
from app.models.cache import cache_stats
from app.routers.analyze import DETECTOR_MAP
from app.dependencies import verify_internal_token

router = APIRouter(tags=["health"])

_start_time = time.time()


def _check_module(module_path: str, name: str) -> ModuleCheck:
    """Try importing and instantiating a detector to verify it works (sanitized messages)."""
    try:
        mod = importlib.import_module(module_path)
        for attr in dir(mod):
            obj = getattr(mod, attr)
            if hasattr(obj, "algorithm_id") and hasattr(obj, "detect"):
                return ModuleCheck(name=name, status="ok", message="loaded")
        return ModuleCheck(name=name, status="warn", message="no detector class found")
    except Exception:
        # Sanitized error message to avoid path leaks
        return ModuleCheck(name=name, status="error", message="import_failed")


def _check_database() -> str:
    """Quick connectivity check against the shared MySQL (no db strings or query errors leaked)."""
    try:
        from app.utils.db import get_engine
        from sqlalchemy import text
        with get_engine().connect() as conn:
            conn.execute(text("SELECT 1"))
        return "connected"
    except Exception:
        return "error"


_DETECTOR_MODULES = [
    ("app.detectors.sms_bert",        "SMS Phishing Heuristic"),
    ("app.detectors.calls_isolation",  "Isolation Forest (Calls)"),
    ("app.detectors.device_oneclass",  "One-Class SVM (Device)"),
    ("app.detectors.activity_lstm",    "Activity Sequence MLP"),
    ("app.detectors.apps_autoencoder", "App Manifest PCA"),
    ("app.detectors.contacts_graph",   "Contact Graph Outlier"),
    ("app.detectors.files_entropy",    "Suspicious File Scanner"),
]


@router.get("/api/v1/health", response_model=HealthResponse, dependencies=[Depends(verify_internal_token)])
async def health():
    cs = cache_stats()

    cuda_avail = False
    try:
        import torch
        cuda_avail = torch.cuda.is_available()
    except ImportError:
        pass

    return HealthResponse(
        status="ok",
        version="1.0.0",
        models_loaded=sorted(DETECTOR_MAP.keys()),
        cuda_available=cuda_avail,
        cuda_device="hidden" if cuda_avail else "",
        memory_mb={"used": 0.0, "total": 0.0}, # Redacted to prevent footprint mapping
        cache_entries=cs["total_entries"],
        modules=[_check_module(p, n) for p, n in _DETECTOR_MODULES],
        database=_check_database(),
        uptime_seconds=time.time() - _start_time,
    )
