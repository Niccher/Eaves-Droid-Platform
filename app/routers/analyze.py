"""
Analyze router — the core of the ML backend.

The PHP side creates a row in ``ml_jobs``, then POSTs a tiny JSON payload
to this endpoint.  The backend:

1. Updates the job status to ``running``.
2. Checks the model cache; if a valid cache entry exists for the given
   algorithm set and data version, it short-circuits and returns cached
   results immediately.
3. For each requested algorithm, finds the registered detector and calls
   ``detector.detect(user_id, scope, incremental_since)``.  Each detector
   queries the shared MySQL database directly and returns a list of
   ``AnomalyResult`` objects.  Errors are isolated per-algorithm so a
   single failing detector never takes down the whole job.
4. Persists every finding into ``ml_results`` so the PHP side can read
   them via ``fetchJobResults()``.
5. On completion, updates ``ml_analysis_tracking`` so the PHP UI can show
   *"X new entries since last analysis"*.
6. Saves a fresh cache entry for full-scope runs, then marks the job as
   ``completed`` and returns a light response with only the result count
   and timing.
"""

import uuid
import time
import logging
from fastapi import APIRouter, HTTPException
from app.models.schemas import AnalyzeRequest, AnalyzeResponse, AnomalyResult
from app.models.registry import get_algorithm_info, ALGORITHM_REGISTRY
from app.utils.db import update_job_status, insert_result, update_tracking

logger = logging.getLogger(__name__)
router = APIRouter(tags=["analyze"])

DETECTOR_MAP: dict[str, str] = {}

CATEGORY_TABLE: dict[str, tuple[str, str]] = {
    "sms":         ("tbl_sms",         "counter"),
    "contacts":    ("tbl_contacts",    "counter"),
    "call_logs":   ("tbl_logs",        "counter"),
    "locations":   ("tbl_location",    "counter"),
    "apps":        ("tbl_apps",        "counter"),
    "files":       ("tbl_device_files","id"),
    "activity":    ("tbl_app_usage",   "id"),
    "device_info": ("tbl_device_profile", "counter"),
}


def load_detectors():
    """Instantiate all detector classes at startup.

    Called once from ``app/main.py`` lifespan.  Each detector is stored in
    ``DETECTOR_MAP`` keyed by its ``algorithm_id`` so the analyze endpoint
    can dispatch work by algorithm name.
    """
    from app.detectors.sms_bert import SmsPhishingHeuristicDetector
    from app.detectors.calls_isolation import CallsIsolationDetector
    from app.detectors.device_oneclass import DeviceOneClassDetector
    from app.detectors.activity_lstm import ActivitySequenceDetector
    from app.detectors.apps_autoencoder import AppManifestAnomalyDetector
    from app.detectors.contacts_graph import GraphContactDetector
    from app.detectors.files_entropy import SuspiciousFileScanner

    for d in [
        SmsPhishingHeuristicDetector(),
        CallsIsolationDetector(),
        DeviceOneClassDetector(),
        ActivitySequenceDetector(),
        AppManifestAnomalyDetector(),
        GraphContactDetector(),
        SuspiciousFileScanner(),
    ]:
        DETECTOR_MAP[d.algorithm_id] = d
    logger.info("Loaded %d detectors", len(DETECTOR_MAP))


@router.post("/api/analyze", response_model=AnalyzeResponse)
async def analyze(req: AnalyzeRequest):
    if not req.algorithms:
        raise HTTPException(status_code=400, detail="No algorithms specified")

    job_id = req.job_id
    start = time.perf_counter()

    update_job_status(job_id, "running")

    # ── Cache check (full-scope runs only) ─────────────────────────────────
    # For full-scope runs we compute a data_version from the latest max-ID
    # across the categories touched by the requested algorithms.  If a valid
    # cache entry exists we return it immediately (still marking the job
    # completed so PHP polls once and sees a result).
    cached_results: list[dict] | None = None
    if req.scope == "full":
        cached_results = _try_load_cache(req)
    if cached_results is not None:
        _persist_results(job_id, req.user_id, cached_results)
        _update_tracking_for_categories(
            {r["category"] for r in cached_results}, req.user_id
        )
        elapsed = (time.perf_counter() - start) * 1000
        update_job_status(
            job_id, "completed",
            results_count=len(cached_results),
            timing_ms=round(elapsed),
        )
        return AnalyzeResponse(
            status="completed",
            job_id=job_id,
            results_count=len(cached_results),
            timing_ms=round(elapsed, 2),
        )

    # ── Run requested detectors ────────────────────────────────────────────
    results: list[AnomalyResult] = []
    affected_categories: set[str] = set()
    per_alg_errors: list[str] = []

    for alg_id in req.algorithms:
        meta = get_algorithm_info(alg_id)
        if meta is None:
            per_alg_errors.append(f"{alg_id}: unknown algorithm")
            continue

        detector = DETECTOR_MAP.get(alg_id)
        if detector is None:
            per_alg_errors.append(f"{alg_id}: no registered detector")
            continue

        try:
            found = await detector.detect(
                user_id=req.user_id,
                scope=req.scope,
                incremental_since=req.incremental_since,
                params=req.params,
            )
            results.extend(found)
            affected_categories.add(meta["category"])
        except Exception as exc:
            logger.exception("Detector %s failed for user %d", alg_id, req.user_id)
            per_alg_errors.append(f"{alg_id}: {exc}")

    # ── Persist findings ───────────────────────────────────────────────────
    _persist_results(job_id, req.user_id, results)
    _update_tracking_for_categories(affected_categories, req.user_id)

    # ── Cache results for future full-scope runs ───────────────────────────
    if req.scope == "full" and results:
        _try_save_cache(req, results)

    elapsed = (time.perf_counter() - start) * 1000
    error_msg = "; ".join(per_alg_errors) if per_alg_errors else None

    update_job_status(
        job_id, "completed",
        results_count=len(results),
        timing_ms=round(elapsed),
        error_msg=error_msg,
    )

    return AnalyzeResponse(
        status="completed",
        job_id=job_id,
        results_count=len(results),
        timing_ms=round(elapsed, 2),
        error=error_msg,
    )


# ── internal helpers ──────────────────────────────────────────────────────────


def _persist_results(job_id: int, user_id: int,
                     results: list | list[AnomalyResult]) -> None:
    """Insert every finding into ``ml_results`` so PHP can read them."""
    for r in results:
        if isinstance(r, dict):
            insert_result(
                job_id=job_id,
                user_id=user_id,
                category=r.get("category", ""),
                algorithm=r.get("algorithm", ""),
                algorithm_id=r.get("algorithm_id", ""),
                severity=r.get("severity", "Low"),
                anomaly=r.get("anomaly", ""),
                score=r.get("score", 0.0),
                event_timestamp=r.get("event_timestamp") or None,
                details=r.get("details"),
            )
        else:
            insert_result(
                job_id=job_id,
                user_id=user_id,
                category=r.category,
                algorithm=r.algorithm,
                algorithm_id=r.algorithm_id,
                severity=r.severity,
                anomaly=r.anomaly,
                score=r.score,
                event_timestamp=r.event_timestamp or None,
                details=r.details,
            )


def _resolve_device_checksums(user_id: int) -> list[str]:
    """Return distinct device checksums for *user_id* from token/upload tables.

    ``tbl_device_profile`` is keyed by ``device_id`` (a checksum string), not
    by ``owner_id``, so we need this resolver before counting or tracking rows.
    """
    from app.utils.db import get_engine
    from sqlalchemy import text

    sql = text("""
        SELECT DISTINCT device_checksum FROM tbl_tokens
        WHERE owner_id = :uid AND device_checksum IS NOT NULL AND device_checksum != ''
        UNION
        SELECT DISTINCT device_checksum FROM uploaded_files
        WHERE token_owner_id = :uid AND device_checksum IS NOT NULL AND device_checksum != ''
    """)
    with get_engine().connect() as conn:
        rows = conn.execute(sql, {"uid": user_id}).fetchall()
    return [r[0] for r in rows if r[0]]


def _count_user_rows(table: str, pk: str, user_id: int) -> int:
    """Return total row count for *user_id* in *table*.

    Special-cases ``tbl_device_profile`` whose owner column is ``device_id``
    (a checksum) rather than ``owner_id``.
    """
    from app.utils.db import get_engine
    from sqlalchemy import text

    if table == "tbl_device_profile":
        checksums = _resolve_device_checksums(user_id)
        if not checksums:
            return 0
        ph = ",".join(f":chk{i}" for i in range(len(checksums)))
        params = {f"chk{i}": c for i, c in enumerate(checksums)}
        with get_engine().connect() as conn:
            row = conn.execute(
                text(f"SELECT COUNT({pk}) AS c FROM {table} WHERE device_id IN ({ph})"),
                params,
            ).one_or_none()
        return row[0] if row else 0

    with get_engine().connect() as conn:
        row = conn.execute(
            text(f"SELECT COUNT({pk}) AS c FROM {table} WHERE owner_id = :uid"),
            {"uid": user_id},
        ).one_or_none()
    return row[0] if row else 0


def _max_id(table: str, pk: str, user_id: int) -> int:
    """Return the maximum PK value for *user_id* in *table*.

    Special-cases ``tbl_device_profile`` (keyed by checksum, not owner_id).
    """
    from app.utils.db import get_engine
    from sqlalchemy import text

    if table == "tbl_device_profile":
        checksums = _resolve_device_checksums(user_id)
        if not checksums:
            return 0
        ph = ",".join(f":chk{i}" for i in range(len(checksums)))
        params = {f"chk{i}": c for i, c in enumerate(checksums)}
        with get_engine().connect() as conn:
            row = conn.execute(
                text(f"SELECT MAX({pk}) AS m FROM {table} WHERE device_id IN ({ph})"),
                params,
            ).one_or_none()
        return row[0] if row and row[0] else 0

    with get_engine().connect() as conn:
        row = conn.execute(
            text(f"SELECT MAX({pk}) AS m FROM {table} WHERE owner_id = :uid"),
            {"uid": user_id},
        ).one_or_none()
    return row[0] if row and row[0] else 0


def _update_tracking_for_categories(categories: set[str], user_id: int) -> None:
    """Upsert ``ml_analysis_tracking`` for every category that was analysed."""
    for cat in categories:
        table_info = CATEGORY_TABLE.get(cat)
        if table_info is None:
            continue
        table_name, pk_col = table_info
        count = _count_user_rows(table_name, pk_col, user_id)
        max_id = _max_id(table_name, pk_col, user_id)
        update_tracking(
            user_id=user_id,
            category=cat,
            last_id=max_id,
            total_analyzed=count,
        )


# ── cache helpers ─────────────────────────────────────────────────────────────


def _data_version_for_user(algorithms: list[str], user_id: int) -> str:
    """Compute a hash of current max-IDs for all categories touched by *algorithms*.

    Used as the ``data_version`` cache key so the cache is invalidated when any
    relevant table grows.
    """
    import hashlib, json
    categories = set()
    for alg_id in algorithms:
        meta = get_algorithm_info(alg_id)
        if meta:
            categories.add(meta["category"])
    snapshot = {}
    for cat in categories:
        table_info = CATEGORY_TABLE.get(cat)
        if table_info is None:
            continue
        tbl, pk = table_info
        snapshot[cat] = _max_id(tbl, pk, user_id)
    raw = json.dumps(snapshot, sort_keys=True)
    return hashlib.sha256(raw.encode()).hexdigest()[:16]


def _try_load_cache(req: AnalyzeRequest) -> list[dict] | None:
    """Attempt to load cached results for *req*.  Returns None on miss/expiry."""
    from app.models.cache import load_cached
    dv = _data_version_for_user(req.algorithms, req.user_id)
    cached = load_cached(
        algorithms=sorted(req.algorithms),
        user_id=req.user_id,
        data={},
        data_version=dv,
    )
    if cached:
        logger.info(
            "Cache HIT for user=%d algorithms=%s data_version=%s",
            req.user_id, req.algorithms, dv,
        )
    return cached


def _try_save_cache(req: AnalyzeRequest, results: list[AnomalyResult]) -> None:
    """Persist *results* in the model cache keyed by algorithm set + data version."""
    from app.models.cache import save_cache
    dv = _data_version_for_user(req.algorithms, req.user_id)
    serializable = [
        r.model_dump() if hasattr(r, "model_dump") else dict(r)
        for r in results
    ]
    save_cache(
        algorithms=sorted(req.algorithms),
        user_id=req.user_id,
        data={},
        results=serializable,
        data_version=dv,
    )
    logger.info(
        "Cache SAVED for user=%d algorithms=%s data_version=%s (%d results)",
        req.user_id, req.algorithms, dv, len(serializable),
    )
