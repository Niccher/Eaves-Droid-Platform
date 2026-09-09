# ADR 0004: Dual-Engine ML Architecture with PHP-ML Failover

## Status
Accepted

## Context & Problem Statement
Heavy anomaly detection algorithms (Isolation Forest, One-Class SVM, Deep Autoencoders) require high performance and specialized scientific libraries (scikit-learn, PyOD, PyTorch). However, running a Python microservice adds an operational dependency that might be stopped or restarting during high availability periods.

## Decision Drivers
* High-accuracy multi-dimensional anomaly detection.
* High system availability and zero user-facing downtime.
* Self-healing failover mechanics.

## Considered Options
1. Run all machine learning purely in PHP using PHP-ML.
2. Rely solely on a standalone Python microservice without fallback.
3. Hybrid architecture: Primary Python FastAPI microservice with automatic synchronous PHP-ML failover.

## Decision Outcome
Chosen Option 3. The WebApp defaults to dispatching asynchronous anomaly jobs to the Python FastAPI microservice (`ML Eaves Droid`). If the microservice fails health checks or connection attempts, `AnomaliesModel` automatically engages in-process PHP-ML statistical clustering without throwing errors to the end user.

## Consequences
* **Positive:** Combines the high performance and model breadth of Python with the indestructible self-healing reliability of in-process PHP.
* **Negative:** Slight algorithmic variation between Python and PHP-ML implementations.
