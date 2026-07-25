from contextlib import asynccontextmanager
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from prometheus_client import make_asgi_app

from app.routers import health, analyze, models_info
from app.config import settings


@asynccontextmanager
async def lifespan(app: FastAPI):
    analyze.load_detectors()
    yield


app = FastAPI(
    title="ML Eaves Droid",
    description="Python ML backend for Eaves Droid anomaly detection",
    version="1.0.0",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(health.router)
app.include_router(analyze.router)
app.include_router(models_info.router)

metrics_app = make_asgi_app()
app.mount("/metrics", metrics_app)


@app.get("/")
async def root():
    return {
        "service": "ML Eaves Droid",
        "version": "1.0.0",
        "docs": "/docs",
        "health": "/api/health",
        "models": "/api/models",
        "analyze": "/api/analyze",
    }
