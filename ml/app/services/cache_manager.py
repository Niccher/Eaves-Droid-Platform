import hashlib
import json
import logging
from app.models.schemas import AnalyzeRequest, AnomalyResult
from app.models.registry import get_algorithm_info
from app.services.persistence import CATEGORY_TABLE, _max_id

logger = logging.getLogger(__name__)


def _data_version_for_user(algorithms: list[str], user_id: int) -> str:
    """Compute a hash of current max-IDs for all categories touched by *algorithms*.

    Used as the ``data_version`` cache key so the cache is invalidated when any
    relevant table grows.
    """
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
