"""
Database utility — provides a shared SQLAlchemy engine connected to the
same MySQL database the PHP web app uses.

All queries are synchronous and run in-process.  The FastAPI router calls
these functions directly (no need for async since the number of queries
per job is small and the GIL releases during I/O wait).
"""

from sqlalchemy import create_engine, text
from sqlalchemy.engine import Engine
from app.config import settings

_engine: Engine | None = None


def get_engine() -> Engine:
    """
    Returns a shared SQLAlchemy Engine connected to db_eaves_droid.

    Connection is lazy — the first call creates the engine; subsequent
    calls return the cached instance.  Uses pymysql as the driver.
    """
    global _engine
    if _engine is None:
        dsn = (
            f"mysql+pymysql://{settings.db_user}:{settings.db_password}"
            f"@{settings.db_host}:{settings.db_port}/{settings.db_name}"
            "?charset=utf8mb4"
        )
        _engine = create_engine(dsn, pool_pre_ping=True, pool_size=5)
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
            "details": __import__("json").dumps(details or {}),
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
