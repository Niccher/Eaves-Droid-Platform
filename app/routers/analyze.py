import uuid
import time
from fastapi import APIRouter, HTTPException
from app.models.schemas import AnalyzeRequest, AnalyzeResponse, AnomalyResult
from app.models.registry import get_algorithm_info

router = APIRouter(tags=["analyze"])

DETECTOR_MAP: dict[str, str] = {}


def load_detectors():
    from app.detectors.sms_bert import BERTPhishingDetector
    from app.detectors.calls_isolation import CallsIsolationDetector
    from app.detectors.device_oneclass import DeviceOneClassDetector

    for d in [
        BERTPhishingDetector(),
        CallsIsolationDetector(),
        DeviceOneClassDetector(),
    ]:
        DETECTOR_MAP[d.algorithm_id] = d


@router.post("/api/analyze", response_model=AnalyzeResponse)
async def analyze(req: AnalyzeRequest):
    if not req.algorithms:
        raise HTTPException(status_code=400, detail="No algorithms specified")

    run_id = str(uuid.uuid4())
    start = time.perf_counter()
    results: list[AnomalyResult] = []

    for alg_id in req.algorithms:
        meta = get_algorithm_info(alg_id)
        if meta is None:
            continue

        detector = DETECTOR_MAP.get(alg_id)
        if detector is None:
            continue

        category_data = req.data.get(meta["category"], [])
        found = await detector.detect(category_data, req.user_id)
        results.extend(found)

    elapsed = (time.perf_counter() - start) * 1000

    return AnalyzeResponse(
        status="ok",
        run_id=run_id,
        results=results,
        timing_ms=round(elapsed, 2),
        engine_note="python",
    )
