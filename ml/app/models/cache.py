import hashlib
import json
import time
import logging
import redis
from app.config import settings

logger = logging.getLogger(__name__)

CACHE_TTL_SECONDS = 3600  # 1 hour default

def get_redis_client() -> redis.Redis:
    return redis.Redis(
        host=settings.redis_host,
        port=settings.redis_port,
        db=settings.redis_db,
        decode_responses=True
    )

def _hash_key(algorithms: list[str], user_id: int | None, data: dict, data_version: str | None = None) -> str:
    raw = json.dumps(
        {"algorithms": sorted(algorithms), "user_id": user_id, "data": data, "data_version": data_version},
        sort_keys=True, default=str,
    )
    return hashlib.sha256(raw.encode()).hexdigest()

def _redis_key(key: str) -> str:
    return f"ml_cache:{key}"

def load_cached(algorithms: list[str], user_id: int | None, data: dict,
                data_version: str | None = None, force_retrain: bool = False) -> list | None:
    if force_retrain:
        return None
    key = _hash_key(algorithms, user_id, data, data_version)
    r_key = _redis_key(key)
    
    try:
        client = get_redis_client()
        cached_data = client.get(r_key)
        if not cached_data:
            return None
            
        payload = json.loads(cached_data)
        return payload.get("results")
    except Exception as e:
        logger.warning(f"Failed to load from Redis cache: {e}")
        return None

def save_cache(algorithms: list[str], user_id: int | None, data: dict,
               results: list, data_version: str | None = None) -> None:
    key = _hash_key(algorithms, user_id, data, data_version)
    r_key = _redis_key(key)
    
    payload = {
        "cached_at": time.time(),
        "algorithms": sorted(algorithms),
        "user_id": user_id,
        "data_version": data_version,
        "result_count": len(results),
        "results": results
    }
    
    try:
        client = get_redis_client()
        client.setex(r_key, CACHE_TTL_SECONDS, json.dumps(payload))
    except Exception as e:
        logger.warning(f"Failed to save to Redis cache: {e}")

def clear_cache(algorithm_id: str | None = None) -> int:
    try:
        client = get_redis_client()
        keys = client.keys("ml_cache:*")
        purged = 0
        for k in keys:
            if algorithm_id is None:
                client.delete(k)
                purged += 1
            else:
                data = client.get(k)
                if data:
                    payload = json.loads(data)
                    if algorithm_id in payload.get("algorithms", []):
                        client.delete(k)
                        purged += 1
        return purged
    except Exception as e:
        logger.warning(f"Failed to clear Redis cache: {e}")
        return 0

def cache_stats() -> dict:
    try:
        client = get_redis_client()
        keys = client.keys("ml_cache:*")
        entries = []
        for k in keys[:50]:
            data = client.get(k)
            if data:
                payload = json.loads(data)
                payload.pop("results", None)
                entries.append(payload)
                
        return {"total_entries": len(keys), "ttl_seconds": CACHE_TTL_SECONDS, "entries": entries}
    except Exception as e:
        logger.warning(f"Failed to get Redis cache stats: {e}")
        return {"total_entries": 0, "ttl_seconds": CACHE_TTL_SECONDS, "entries": []}
