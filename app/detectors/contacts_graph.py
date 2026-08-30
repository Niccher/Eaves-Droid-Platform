"""
Contact Graph Outlier Model — builds a weighted NetworkX interaction graph
where edges are constructed from real call and SMS counts between the user and
each contact.  Louvain community detection partitions contacts into social
clusters (family / work / services), then flags:

  - Orphaned contacts  : stored in address book but zero interaction recorded.
  - Community outliers : high-interaction contacts that belong to no detected
                         community cluster.

Note: Louvain is provided by ``networkx.algorithms.community`` (NetworkX ≥3.0).
The ``algorithm_id`` (``contacts_graph``) is kept for backward compatibility.

Queries ``tbl_extracted_contacts``, ``tbl_logs``, and ``tbl_extracted_sms``
directly from the shared MySQL database.
"""

from __future__ import annotations

import logging
import json

import networkx as nx
import numpy as np

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult

logger = logging.getLogger(__name__)

# Edge-weight blend coefficients  (w1·calls + w2·sms + w3·avg_duration)
_W_CALLS = 1.0
_W_SMS   = 0.5
_W_DUR   = 0.3


def _normalise(value: float, max_val: float) -> float:
    return value / max_val if max_val > 0 else 0.0


class GraphContactDetector(BaseDetector):
    algorithm_id   = "contacts_graph"
    algorithm_name = "Contact Graph Outlier Model (Interaction-Weighted)"
    category       = "contacts"

    async def detect(
        self,
        user_id: int,
        scope: str = "full",
        incremental_since: str | None = None,
        params: dict | None = None,
    ) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine

        query_params: dict = {"uid": user_id}
        where_contacts = "owner_id = :uid"
        where_time = ""
        if scope == "incremental" and incremental_since:
            where_time = " AND created_at > :since"
            query_params["since"] = incremental_since

        # -- 1. Fetch contacts -----------------------------------------------
        with get_engine().connect() as conn:
            contact_rows = conn.execute(
                text(f"""
                    SELECT display_name, phone_numbers,
                           COALESCE(contact_frequency, 0) AS contact_frequency,
                           last_contacted
                    FROM tbl_extracted_contacts
                    WHERE {where_contacts}{where_time}
                    LIMIT 2000
                """),
                query_params,
            ).mappings().fetchall()

            if len(contact_rows) < 5:
                return []

            # -- 2. Aggregate call stats per number --------------------------
            call_stats = conn.execute(
                text("""
                    SELECT
                        phone_number    AS phone,
                        COUNT(*)        AS call_count,
                        SUM(duration_seconds) AS total_duration
                    FROM tbl_extracted_call_logs
                    WHERE owner_id = :uid
                    GROUP BY phone_number
                """),
                {"uid": user_id},
            ).mappings().fetchall()

            # -- 3. Aggregate SMS stats per address --------------------------
            sms_stats = conn.execute(
                text("""
                    SELECT address AS phone, COUNT(*) AS sms_count
                    FROM tbl_extracted_sms
                    WHERE owner_id = :uid
                    GROUP BY address
                """),
                {"uid": user_id},
            ).mappings().fetchall()


        # Build lookup dicts:  normalised phone → metric
        def _clean_phone(p: str) -> str:
            return "".join(c for c in (p or "") if c.isdigit())[-10:] or p

        call_map: dict[str, dict] = {
            _clean_phone(r["phone"]): {
                "calls": int(r["call_count"] or 0),
                "dur":   float(r["total_duration"] or 0),
            }
            for r in call_stats
        }
        sms_map: dict[str, int] = {
            _clean_phone(r["phone"]): int(r["sms_count"] or 0)
            for r in sms_stats
        }

        # Compute max values for normalisation
        max_calls  = max((v["calls"] for v in call_map.values()), default=1) or 1
        max_dur    = max((v["dur"]   for v in call_map.values()), default=1) or 1
        max_sms    = max(sms_map.values(), default=1) or 1
        max_freq   = max(
            (int(r.get("contact_frequency") or 0) for r in contact_rows), default=1
        ) or 1

        # -- 4. Build interaction graph --------------------------------------
        G = nx.Graph()

        # Central node represents the device owner
        G.add_node("__owner__", label="Device Owner", kind="owner")

        for row in contact_rows:
            name = (row["display_name"] or "").strip()
            numbers_raw = row["phone_numbers"]
            phone = ""
            if isinstance(numbers_raw, str):
                try:
                    nums = json.loads(numbers_raw)
                    if isinstance(nums, list) and nums:
                        first = nums[0]
                        phone = str(first.get("number") or first.get("normalized_number") or "") if isinstance(first, dict) else str(first)
                except Exception:
                    phone = numbers_raw
            elif isinstance(numbers_raw, (list, tuple)):
                first = numbers_raw[0] if numbers_raw else ""
                phone = str(first.get("number") or first.get("normalized_number") or "") if isinstance(first, dict) else str(first)

            phone_clean = _clean_phone(phone)
            node_id = phone_clean if phone_clean else f"no-phone-{G.number_of_nodes()}"

            G.add_node(node_id, label=name, phone=phone_clean)

            # Calculate edge weight from real interaction data
            c_data   = call_map.get(phone_clean, {"calls": 0, "dur": 0.0})
            sms_c    = sms_map.get(phone_clean, 0)
            freq     = int(row.get("contact_frequency") or 0)
            weight = (
                _W_CALLS * _normalise(c_data["calls"], max_calls)
                + _W_SMS  * _normalise(sms_c,          max_sms)
                + _W_DUR  * _normalise(c_data["dur"],   max_dur)
                + 0.2     * _normalise(freq,            max_freq)  # supplementary: contact_frequency
            )
            G.add_edge("__owner__", node_id, weight=weight)


        # -- 5. Louvain community detection ----------------------------------
        # Uses networkx.community.louvain_communities (NetworkX ≥ 3.0)
        try:
            from networkx.algorithms.community import louvain_communities
            communities: list[set] = louvain_communities(G, weight="weight", seed=42)
        except Exception as exc:
            logger.warning("contacts_graph: Louvain failed (%s); skipping community step", exc)
            communities = []

        # Map each node → community index
        node_community: dict[str, int] = {}
        for ci, community in enumerate(communities):
            for node in community:
                node_community[node] = ci

        # -- 6. Flag anomalies -----------------------------------------------
        results: list[AnomalyResult] = []

        for node_id, attrs in G.nodes(data=True):
            if node_id == "__owner__":
                continue

            label = attrs.get("label") or node_id
            phone = attrs.get("phone", "")

            edge_data   = G.edges["__owner__", node_id] if G.has_edge("__owner__", node_id) else {}
            weight      = float(edge_data.get("weight", 0.0))
            in_community = node_id in node_community

            # Case A: stored contact with zero interaction weight → orphan
            if weight == 0.0:
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="High",
                    anomaly=f"Orphaned contact '{label}' — no recorded call or SMS interaction",
                    score=0.95,
                    event_timestamp="",
                    details={
                        "contact": label,
                        "phone": phone,
                        "interaction_weight": 0.0,
                        "in_community": False,
                        "reason": "zero_interaction",
                    },
                ))

            # Case B: non-zero interaction but no community membership → unknown high-volume contact
            elif weight > 0.3 and not in_community:
                score = round(min(0.55 + weight * 0.4, 0.92), 4)
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="Medium",
                    anomaly=(
                        f"High-interaction contact '{label}' is not in any detected social community "
                        f"(interaction score={weight:.2f})"
                    ),
                    score=score,
                    event_timestamp="",
                    details={
                        "contact": label,
                        "phone": phone,
                        "interaction_weight": round(weight, 4),
                        "in_community": False,
                        "reason": "community_outlier",
                    },
                ))

        # Sort by score descending and cap at 10 results
        results.sort(key=lambda r: -r.score)
        return results[:10]

