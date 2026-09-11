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


def _check_database() -> tuple[str, float, int, int]:
    """
    Comprehensive connectivity & table verification check against the shared MySQL database.
    Returns (status, latency_ms, verified_tables_count, total_expected_tables).
    """
    core_tables = [
        "tbl_extracted_sms",
        "tbl_extracted_contacts",
        "tbl_extracted_call_logs",
        "tbl_extracted_locations",
        "tbl_extracted_installed_apps",
        "tbl_extracted_device_files",
        "tbl_system_app_usage",
        "tbl_device_profiles",
        "ml_jobs",
        "ml_results",
    ]
    try:
        from app.utils.db import get_engine
        from sqlalchemy import text
        t0 = time.perf_counter()
        engine = get_engine()
        with engine.connect() as conn:
            conn.execute(text("SELECT 1"))
            db_latency = round((time.perf_counter() - t0) * 1000, 2)
            res = conn.execute(text("SHOW TABLES"))
            existing = {str(row[0]).lower() for row in res.fetchall()}
            verified = sum(1 for tbl in core_tables if tbl.lower() in existing)
            return "connected", db_latency, verified, len(core_tables)
    except Exception as e:
        return "error", 0.0, 0, len(core_tables)


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

    # Real process memory & CPU utilization via psutil
    used_mb = 0.0
    total_mb = 0.0
    cpu_pct = 0.0
    try:
        import psutil
        proc = psutil.Process()
        used_mb = round(proc.memory_info().rss / (1024 * 1024), 1)
        total_mb = round(psutil.virtual_memory().total / (1024 * 1024), 1)
        
        # Calculate process-specific CPU percentage normalized by core count
        cpu_count = psutil.cpu_count() or 1
        proc_cpu = proc.cpu_percent(interval=None) / cpu_count
        cpu_pct = round(min(100.0, max(0.0, proc_cpu)), 1)
    except Exception:
        pass

    db_status, db_latency, db_verified, db_total = _check_database()

    return HealthResponse(
        status="ok",
        version="2.5.1",
        models_loaded=sorted(DETECTOR_MAP.keys()),
        cuda_available=cuda_avail,
        cuda_device="hidden" if cuda_avail else "",
        memory_mb={"used": used_mb, "total": total_mb},
        cpu_percent=cpu_pct,
        cache_entries=cs["total_entries"],
        modules=[_check_module(p, n) for p, n in _DETECTOR_MODULES],
        database=db_status,
        database_latency_ms=db_latency,
        database_tables_verified=db_verified,
        database_total_tables=db_total,
        uptime_seconds=round(time.time() - _start_time, 1),
    )
