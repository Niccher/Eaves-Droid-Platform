"""
Graph Relation Outlier Model — builds a NetworkX graph from the user's
contacts where edges represent shared phone-number prefixes or name
similarity, then flags orphaned (degree 0) and low-connectivity nodes.

Queries ``tbl_contacts`` directly from the shared MySQL database.
"""

import json
import networkx as nx
import numpy as np
from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult


class GraphContactDetector(BaseDetector):
    algorithm_id = "contacts_graph"
    algorithm_name = "Graph Relation Outlier Model (GCN)"
    category = "contacts"

    async def detect(self, user_id: int, scope: str = "full",
                     incremental_since: str | None = None) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine

        where = "owner_id = :uid"
        params: dict = {"uid": user_id}
        if scope == "incremental" and incremental_since:
            where += " AND created_at > :since"
            params["since"] = incremental_since

        sql = text(f"""
            SELECT display_name, phone_numbers
            FROM tbl_contacts
            WHERE {where}
            LIMIT 2000
        """)
        with get_engine().connect() as conn:
            rows = conn.execute(sql, params).mappings().fetchall()

        if len(rows) < 5:
            return []

        G = nx.Graph()

        for row in rows:
            name = (row["display_name"] or "").strip()
            numbers_raw = row["phone_numbers"]
            phone = ""
            if isinstance(numbers_raw, str):
                try:
                    nums = json.loads(numbers_raw)
                    phone = str(nums[0]) if isinstance(nums, list) and nums else ""
                except Exception:
                    phone = numbers_raw
            elif isinstance(numbers_raw, (list, tuple)):
                phone = str(numbers_raw[0]) if numbers_raw else ""

            node_id = phone if phone else f"no-phone-{len(G.nodes)}"
            G.add_node(node_id, label=name, phone=phone)

        # Edge-building: connect contacts sharing the same name prefix or phone prefix
        nodes = list(G.nodes(data=True))
        for i, (nid, attrs) in enumerate(nodes):
            name_pref = (attrs.get("label") or "")[:3].lower()
            phone_pref = (attrs.get("phone") or "")[:4]
            for j in range(i + 1, len(nodes)):
                oid, oattrs = nodes[j]
                w = 0.0
                op = (oattrs.get("phone") or "")[:4]
                on = (oattrs.get("label") or "")[:3].lower()
                if name_pref and name_pref == on:
                    w += 0.5
                if phone_pref and phone_pref == op:
                    w += 0.5
                if w > 0:
                    G.add_edge(nid, oid, weight=w)

        # Fallback: if no edges yet, try 6-digit phone prefix matching
        if G.number_of_edges() == 0:
            for i, (n1, a1) in enumerate(nodes):
                p1 = (a1.get("phone") or "")[:6]
                if not p1:
                    continue
                for j in range(i + 1, len(nodes)):
                    p2 = (nodes[j][1].get("phone") or "")[:6]
                    if p1 == p2:
                        G.add_edge(n1, nodes[j][0], weight=0.3)

        degrees = np.array([d for _, d in G.degree()])
        if len(degrees) < 3:
            return []

        mean_deg = float(np.mean(degrees))
        std_deg = float(np.std(degrees)) or 1.0

        results = []
        for node_id, d in G.degree():
            attrs = G.nodes[node_id]
            label = attrs.get("label") or node_id
            if d == 0:
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="High",
                    anomaly=f"Orphaned contact '{label}' — no relational edges",
                    score=0.95,
                    event_timestamp="",
                    details={
                        "contact": label,
                        "phone": attrs.get("phone", ""),
                        "degree": 0,
                        "reason": "no_edges",
                    },
                ))
            elif 0 < d < max(2, mean_deg - std_deg):
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="Medium",
                    anomaly=f"Low-connectivity contact '{label}' (degree={d}, mean={mean_deg:.1f})",
                    score=round(float(max(0, 1.0 - d / (mean_deg + 1))), 4),
                    event_timestamp="",
                    details={
                        "contact": label,
                        "phone": attrs.get("phone", ""),
                        "degree": d,
                        "mean_degree": round(mean_deg, 1),
                    },
                ))

        return results[:10]
