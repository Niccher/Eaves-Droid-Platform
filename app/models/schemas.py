"""
Pydantic models for the ML backend API and internal data transfer.
"""

from pydantic import BaseModel
from typing import Any
from datetime import datetime


class AnalyzeRequest(BaseModel):
    """
    Payload the PHP side POSTs to /api/analyze.

    Renamed from the old data-blob approach:
    - ``algorithms`` — list of Python-only algorithm IDs to run
    - ``user_id`` — target user whose data to analyse
    - ``scope`` — ``full`` (all entries) or ``incremental`` (only new)
    - ``incremental_since`` — ISO datetime cutoff for incremental runs
      (PHP reads this from ml_analysis_tracking.last_analyzed_at)
    """
    job_id: int
    user_id: int
    algorithms: list[str]
    scope: str = "full"
    incremental_since: str | None = None
    params: dict[str, str] = {}


class AnomalyResult(BaseModel):
    """
    A single anomaly finding.  Detector.detect() returns a list of these;
    the analyze router persists each one into ml_results.
    """
    algorithm: str
    algorithm_id: str
    category: str
    severity: str
    anomaly: str
    score: float
    event_timestamp: str = ""
    details: dict[str, Any] = {}


class AnalyzeResponse(BaseModel):
    """
    Lightweight response the PHP side receives after the Python backend
    finishes processing a job.  The actual findings are in ml_results;
    this is just a status signal.
    """
    status: str
    job_id: int
    results_count: int
    timing_ms: float
    error: str | None = None


class ModuleCheck(BaseModel):
    name: str
    status: str
    message: str = ""


class HealthResponse(BaseModel):
    status: str
    version: str
    models_loaded: list[str]
    cuda_available: bool
    cuda_device: str = ""
    memory_mb: dict[str, float]
    cpu_percent: float = 0.0
    cache_entries: int = 0
    modules: list[ModuleCheck] = []
    database: str = ""
    database_latency_ms: float = 0.0
    database_tables_verified: int = 0
    database_total_tables: int = 0
    uptime_seconds: float = 0


class ModelInfo(BaseModel):
    id: str
    name: str
    category: str
    description: str
    engine: str = "python"
    parameters: dict[str, Any] = {}
