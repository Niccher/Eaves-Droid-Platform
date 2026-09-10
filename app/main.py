"""
FastAPI application entry point for the ML Eaves Droid anomaly detection backend.

The PHP web app POSTs lightweight ``{job_id, user_id, algorithms, scope}``
payloads to ``/api/analyze``.  This backend queries the shared MySQL database
directly and stores findings into ``ml_results`` — no HTTP data blobs.
"""

import os
from contextlib import asynccontextmanager
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from prometheus_client import make_asgi_app

from app.routers import health, analyze, models_info, llm
from app.config import settings


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Load all 7 detectors into the analyze module's DETECTOR_MAP on startup."""
    analyze.load_detectors()
    yield


# Determine if we are in production
is_prod = os.getenv("ENV", "development").lower() == "production"

app = FastAPI(
    title="ML Eaves Droid",
    description="Python ML backend for Eaves Droid anomaly detection",
    version="2.5.0",
    lifespan=lifespan,
    docs_url=None if is_prod else "/docs",
    redoc_url=None if is_prod else "/redoc",
)

# Apply flexible CORS using dynamic config allowed_origin (comma-separated or wildcard)
origins = [o.strip() for o in settings.allowed_origin.split(",")] if settings.allowed_origin != "*" else ["*"]

app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(health.router)
app.include_router(analyze.router)
app.include_router(models_info.router)
app.include_router(llm.router)

# Prometheus metrics exposed at /metrics for operational monitoring.
metrics_app = make_asgi_app()
app.mount("/metrics", metrics_app)


@app.get("/")
async def root():
    """Service landing page — returns endpoint URLs for discovery."""
    return {
        "service": "ML Eaves Droid",
        "version": "1.0.0",
        "docs": "/docs",
        "health": "/api/v1/health",
        "models": "/api/v1/models",
        "analyze": "/api/v1/analysis-jobs",
    }
