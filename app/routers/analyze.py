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
from fastapi import APIRouter, HTTPException, Depends
from app.models.schemas import AnalyzeRequest, AnalyzeResponse, AnomalyResult
from app.models.registry import get_algorithm_info, ALGORITHM_REGISTRY
from app.utils.db import update_job_status
from app.services.persistence import _persist_results, _update_tracking_for_categories
from app.services.cache_manager import _try_load_cache, _try_save_cache
from app.dependencies import verify_internal_token

logger = logging.getLogger(__name__)
router = APIRouter(tags=["analyze"])

DETECTOR_MAP: dict[str, str] = {}


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
    from app.detectors.notification_hijack import NotificationHijackDetector
    from app.detectors.background_exfiltration import BackgroundExfiltrationDetector
    from app.detectors.accessibility_abuse import AccessibilityAbuseDetector
    from app.detectors.sleep_disturbance import SleepDisturbanceDetector
    from app.detectors.battery_drain import BatteryDrainDetector
    from app.detectors.communication_spikes import CommunicationSpikesDetector
    from app.detectors.fusion_scorer import FusionScorerDetector
    from app.detectors.behaviour_markov import BehaviourMarkovDetector

    for d in [
        SmsPhishingHeuristicDetector(),
        CallsIsolationDetector(),
        DeviceOneClassDetector(),
        ActivitySequenceDetector(),
        AppManifestAnomalyDetector(),
        GraphContactDetector(),
        SuspiciousFileScanner(),
        NotificationHijackDetector(),
        BackgroundExfiltrationDetector(),
        AccessibilityAbuseDetector(),
        SleepDisturbanceDetector(),
        BatteryDrainDetector(),
        CommunicationSpikesDetector(),
        FusionScorerDetector(),
        BehaviourMarkovDetector(),
    ]:
        DETECTOR_MAP[d.algorithm_id] = d
    logger.info("Loaded %d detectors", len(DETECTOR_MAP))


@router.post("/api/v1/analysis-jobs", response_model=AnalyzeResponse, dependencies=[Depends(verify_internal_token)])
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
