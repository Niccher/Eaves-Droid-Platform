import hashlib
import json
import os
import time
import joblib
from pathlib import Path
from app.config import settings

CACHE_TTL_SECONDS = 3600  # 1 hour default


def _cache_dir() -> Path:
    d = Path(settings.models_cache)
    d.mkdir(parents=True, exist_ok=True)
    return d


def _hash_key(algorithms: list[str], user_id: int | None, data: dict, data_version: str | None = None) -> str:
    raw = json.dumps(
        {"algorithms": sorted(algorithms), "user_id": user_id, "data": data, "data_version": data_version},
        sort_keys=True, default=str,
    )
    return hashlib.sha256(raw.encode()).hexdigest()


def _model_path(key: str) -> Path:
    return _cache_dir() / f"{key}.joblib"


def _meta_path(key: str) -> Path:
    return _cache_dir() / f"{key}.meta.json"


def load_cached(algorithms: list[str], user_id: int | None, data: dict,
                data_version: str | None = None, force_retrain: bool = False) -> list | None:
    if force_retrain:
        return None
    key = _hash_key(algorithms, user_id, data, data_version)
    mpath = _meta_path(key)
    dpath = _model_path(key)
    if not mpath.exists() or not dpath.exists():
        return None
    try:
        meta = json.loads(mpath.read_text())
        if time.time() - meta["cached_at"] > CACHE_TTL_SECONDS:
            mpath.unlink(missing_ok=True)
            dpath.unlink(missing_ok=True)
            return None
        return joblib.load(dpath)
    except Exception:
        mpath.unlink(missing_ok=True)
        dpath.unlink(missing_ok=True)
        return None


def save_cache(algorithms: list[str], user_id: int | None, data: dict,
               results: list, data_version: str | None = None) -> None:
    key = _hash_key(algorithms, user_id, data, data_version)
    joblib.dump(results, _model_path(key))
    _meta_path(key).write_text(json.dumps({
        "cached_at": time.time(),
        "algorithms": sorted(algorithms),
        "user_id": user_id,
        "data_version": data_version,
        "result_count": len(results),
    }))


def clear_cache(algorithm_id: str | None = None) -> int:
    purged = 0
    for f in _cache_dir().glob("*.joblib"):
        if algorithm_id is None:
            f.unlink()
            _cache_dir().joinpath(f.stem + ".meta.json").unlink(missing_ok=True)
            purged += 1
    return purged


def cache_stats() -> dict:
    total = 0
    entries = []
    for f in _cache_dir().glob("*.joblib"):
        total += 1
        mpath = _cache_dir().joinpath(f.stem + ".meta.json")
        if mpath.exists():
            meta = json.loads(mpath.read_text())
            entries.append(meta)
    return {"total_entries": total, "ttl_seconds": CACHE_TTL_SECONDS, "entries": entries[:50]}
