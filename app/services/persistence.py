from app.models.schemas import AnomalyResult
from app.utils.db import insert_result, update_tracking, get_engine
from sqlalchemy import text

CATEGORY_TABLE: dict[str, tuple[str, str]] = {
    "sms":         ("tbl_extracted_sms",           "counter"),
    "contacts":    ("tbl_extracted_contacts",      "counter"),
    "call_logs":   ("tbl_extracted_call_logs",     "counter"),
    "locations":   ("tbl_extracted_locations",     "counter"),
    "apps":        ("tbl_extracted_installed_apps","counter"),
    "files":       ("tbl_extracted_device_files",  "id"),
    "activity":    ("tbl_system_app_usage",        "id"),
    "device_info": ("tbl_device_profiles",         "counter"),
}


def _resolve_device_checksums(user_id: int) -> list[str]:
    """Return distinct device checksums for *user_id* from token/upload tables.

    ``tbl_device_profiles`` is keyed by ``device_id`` (a checksum string), not
    by ``owner_id``, so we need this resolver before counting or tracking rows.
    """
    sql = text("""
        SELECT DISTINCT device_checksum FROM tbl_user_api_tokens
        WHERE owner_id = :uid AND device_checksum IS NOT NULL AND device_checksum != ''
        UNION
        SELECT DISTINCT device_checksum FROM tbl_uploaded_files
        WHERE token_owner_id = :uid AND device_checksum IS NOT NULL AND device_checksum != ''
    """)
    with get_engine().connect() as conn:
        rows = conn.execute(sql, {"uid": user_id}).fetchall()
    return [r[0] for r in rows if r[0]]


def _count_user_rows(table: str, pk: str, user_id: int) -> int:
    """Return total row count for *user_id* in *table*.

    Special-cases ``tbl_device_profiles`` whose owner column is ``device_id``
    (a checksum) rather than ``owner_id``.
    """
    if table == "tbl_device_profiles":
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

    Special-cases ``tbl_device_profiles`` (keyed by checksum, not owner_id).
    """
    if table == "tbl_device_profiles":
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
