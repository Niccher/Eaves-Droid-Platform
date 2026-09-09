"""
Database utility — provides a shared SQLAlchemy engine connected to the
same MySQL database the PHP web app uses.

All queries are synchronous and run in-process.  The FastAPI router calls
these functions directly (no need for async since the number of queries
per job is small and the GIL releases during I/O wait).
"""

import json
import numpy as np
import os
import urllib.parse
from sqlalchemy import create_engine, text
from sqlalchemy.engine import Engine
from app.config import settings

_engine: Engine | None = None


def json_safe(obj):
    """Recursively convert numpy/pandas scalars to plain Python types."""
    if isinstance(obj, dict):
        return {k: json_safe(v) for k, v in obj.items()}
    if isinstance(obj, (list, tuple)):
        return [json_safe(v) for v in obj]
    if isinstance(obj, np.integer):
        return int(obj)
    if isinstance(obj, np.floating):
        return float(obj)
    if isinstance(obj, np.bool_):
        return bool(obj)
    if isinstance(obj, np.ndarray):
        return obj.tolist()
    return obj


def get_engine() -> Engine:
    """
    Returns a shared SQLAlchemy Engine connected to the MySQL database.
    Supports Railway (MYSQL_URL, MYSQLHOST, etc.) and Docker environment variables.
    """
    global _engine
    if _engine is None:
        db_url = os.getenv("MYSQL_URL") or os.getenv("DATABASE_URL") or os.getenv("DB_URL")
        if db_url:
            if db_url.startswith("mysql://"):
                db_url = db_url.replace("mysql://", "mysql+pymysql://", 1)
            elif not db_url.startswith("mysql+pymysql://"):
                db_url = f"mysql+pymysql://{db_url.split('://', 1)[-1]}"
            dsn = db_url
        else:
            host = os.getenv("MYSQLHOST") or os.getenv("MYSQL_HOST") or os.getenv("DB_HOST") or settings.db_host
            port = int(os.getenv("MYSQLPORT") or os.getenv("MYSQL_PORT") or os.getenv("DB_PORT") or settings.db_port)
            user = os.getenv("MYSQLUSER") or os.getenv("MYSQL_USER") or os.getenv("DB_USER") or settings.db_user
            password = os.getenv("MYSQLPASSWORD") or os.getenv("MYSQL_PASSWORD") or os.getenv("DB_PASSWORD") or settings.db_password
            database = os.getenv("MYSQLDATABASE") or os.getenv("MYSQL_DATABASE") or os.getenv("DB_NAME") or settings.db_name

            escaped_user = urllib.parse.quote_plus(user)
            escaped_pass = urllib.parse.quote_plus(password)
            dsn = f"mysql+pymysql://{escaped_user}:{escaped_pass}@{host}:{port}/{database}?charset=utf8mb4"

        _engine = create_engine(
            dsn,
            pool_pre_ping=True,
            pool_recycle=300,
            pool_size=5,
            max_overflow=5,
            connect_args={"connect_timeout": 5}
        )
    return _engine


# ── ml_jobs helpers ──────────────────────────────────────────────────────────


def update_job_status(job_id: int, status: str, **extra) -> None:
    """
    Updates a row in ml_jobs with the given status and any extra columns.

    Extra kwargs are joined as ``SET col=:val`` pairs so callers can
    set ``results_count=7``, ``timing_ms=1234``, ``error_msg='...'``, etc.
    """
    sets = ["status = :status"]
    params: dict = {"job_id": job_id, "status": status}

    if status == "running":
        sets.append("started_at = NOW()")
    elif status in ("completed", "failed"):
        sets.append("completed_at = NOW()")

    for col, val in extra.items():
        sets.append(f"{col} = :{col}")
        params[col] = val

    sql = f"UPDATE ml_jobs SET {', '.join(sets)} WHERE id = :job_id"
    with get_engine().connect() as conn:
        conn.execute(text(sql), params)
        conn.commit()


# ── ml_results helpers ────────────────────────────────────────────────────────


def insert_result(job_id: int, user_id: int, category: str, algorithm: str,
                  algorithm_id: str, severity: str, anomaly: str,
                  score: float, event_timestamp: str | None = None,
                  details: dict | None = None) -> None:
    """
    Inserts one anomaly finding row into ml_results.
    Called by the analyze router once per finding returned by a detector.
    """
    sql = """
        INSERT INTO ml_results
            (job_id, user_id, category, algorithm, algorithm_id,
             severity, anomaly, score, event_timestamp, details)
        VALUES
            (:job_id, :user_id, :category, :algorithm, :algorithm_id,
             :severity, :anomaly, :score, :event_timestamp, :details)
    """
    with get_engine().connect() as conn:
        conn.execute(text(sql), {
            "job_id": job_id,
            "user_id": user_id,
            "category": category,
            "algorithm": algorithm,
            "algorithm_id": algorithm_id,
            "severity": severity,
            "anomaly": anomaly,
            "score": score,
            "event_timestamp": event_timestamp,
            "details": json.dumps(json_safe(details or {})),
        })
        conn.commit()


# ── ml_analysis_tracking helpers ──────────────────────────────────────────────


def update_tracking(user_id: int, category: str, last_id: int = 0,
                    total_analyzed: int = 0) -> None:
    """
    Upserts a row in ml_analysis_tracking so the PHP side knows how many
    records were covered by the last full or incremental analysis.
    """
    sql = """
        INSERT INTO ml_analysis_tracking
            (user_id, category, last_id, total_analyzed, last_analyzed_at)
        VALUES
            (:user_id, :category, :last_id, :total, NOW())
        ON DUPLICATE KEY UPDATE
            last_id         = VALUES(last_id),
            total_analyzed  = VALUES(total_analyzed),
            last_analyzed_at = VALUES(last_analyzed_at)
    """
    with get_engine().connect() as conn:
        conn.execute(text(sql), {
            "user_id": user_id,
            "category": category,
            "last_id": last_id,
            "total": total_analyzed,
        })
        conn.commit()
