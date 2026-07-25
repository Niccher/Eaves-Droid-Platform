from pydantic import BaseModel
from typing import Any


class AnalyzeRequest(BaseModel):
    algorithms: list[str]
    user_id: int | None = None
    data: dict[str, list[dict[str, Any]]] = {}


class AnomalyResult(BaseModel):
    algorithm: str
    algorithm_id: str
    category: str
    severity: str
    anomaly: str
    score: float
    timestamp: str
    details: dict[str, Any] = {}


class AnalyzeResponse(BaseModel):
    status: str
    run_id: str
    results: list[AnomalyResult]
    timing_ms: float
    engine_note: str = "python"


class HealthResponse(BaseModel):
    status: str
    version: str
    models_loaded: list[str]
    cuda_available: bool
    memory_mb: dict[str, float]


class ModelInfo(BaseModel):
    id: str
    name: str
    category: str
    description: str
    engine: str = "python"
    parameters: dict[str, Any] = {}
