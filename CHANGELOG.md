# Changelog — ML Eaves Droid Engine

All notable changes to the Python ML Inference and Anomaly Detection Engine will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.8.0] - 2026-09-01 — Railway Cloud Deployment, Dynamic CORS & Multi-Worker Production

### Added
- **Railway Cloud Deployment Configuration**: Added `railway.toml` specifying `uvicorn app.main:app --host 0.0.0.0 --port ${PORT:-9070} --workers 2` for multi-worker production concurrency without development file watcher overhead.
- **Flexible Dynamic CORS Middleware**: Updated `main.py` CORS middleware to support comma-separated allowed origins or wildcards in `ALLOWED_ORIGIN`.
- **Production Environment Template**: Added `.env.example` pre-configured with `ENV=production`, `LOG_LEVEL=info`, and `WORKERS=2`.

---

## [2.5.0] - 2026-08-20 — Analysis Suites Feature-Gating & Telemetry Alignment

### Changed
- **Platform Version Synchronization**: Synchronized Fast-API ML engine version to `2.5.0` aligned with Eaves Droid WebApp and Android Client.
- **Model Result Scoring Pipeline**: Optimized anomaly scoring and feature extraction pipelines for 11 tiered analysis suites (Storage, Apps, Lifestyle, Social, Privacy, Subscriptions, Sentiment, Finance, Location, Hotspots, Report).
- **Cache Management**: Enhanced persistence layer for feature vectors and result payload caching.

---

## [2.4.0] - 2026-08-19 — Initial Version Alignment

### Added
- **FastAPI Engine Setup**: Standardized 7 ML anomaly detection models (Isolation Forest, Autoencoders, LSTM, One-Class SVM).
- **Centralized Version Manifest**: Added `VERSION.json` version descriptor.
