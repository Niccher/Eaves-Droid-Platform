"""
App-Transition Markov Chain Detector — models normal application-launch
sequences as a first-order Markov Chain and flags transition subsequences
whose log-likelihood is statistically improbable (Z-score < -2σ).

This replaces the previous MLP-regressor approach, which only predicted the
clock hour of the next activity event and was blind to *which* apps were
opened.  The Markov approach captures sequences like:

    Calculator → System Settings → Developer Options  (very rare, high risk)
    WhatsApp → Instagram → Browser                   (common, low risk)

No external training data is required — the transition matrix is computed
dynamically from the user's own historical usage stored in
``tbl_system_app_usage``.

The ``algorithm_id`` (``act_lstm``) is kept for backward compatibility with
existing ``ml_results`` rows and webapp configuration.
"""

from __future__ import annotations

import logging
import math
from collections import defaultdict

import numpy as np

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult

logger = logging.getLogger(__name__)

# N-gram window length for sequence scoring
_NGRAM = 3

# Smoothing constant (Laplace) to avoid zero probabilities for unseen transitions
_LAPLACE_ALPHA = 0.1

# Minimum number of ordered events required to build a meaningful matrix
_MIN_EVENTS = 15


class ActivitySequenceDetector(BaseDetector):
    algorithm_id   = "act_lstm"
    algorithm_name = "App Transition Markov Chain Detector"
    category       = "activity"

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
        where = "owner_id = :uid"
        if scope == "incremental" and incremental_since:
            where += " AND created_at > :since"
            query_params["since"] = incremental_since

        # -- 1. Fetch app usage events ordered chronologically ---------------
        sql = text(f"""
            SELECT package_name,
                   DATE_FORMAT(FROM_UNIXTIME(last_time_used / 1000),
                               '%Y-%m-%d %H:%i:%s') AS ts
            FROM tbl_system_app_usage
            WHERE {where}
              AND package_name IS NOT NULL
              AND package_name != ''
            ORDER BY last_time_used ASC
            LIMIT 5000
        """)

        with get_engine().connect() as conn:
            rows = conn.execute(sql, query_params).mappings().fetchall()

        if len(rows) < _MIN_EVENTS:
            return []

        packages   = [str(r["package_name"]) for r in rows]
        timestamps = [str(r["ts"] or "")     for r in rows]

        # -- 2. Build transition frequency matrix ----------------------------
        # transition_counts[A][B] = number of times B followed A
        transition_counts: dict[str, dict[str, int]] = defaultdict(lambda: defaultdict(int))
        state_totals:      dict[str, int]             = defaultdict(int)

        for i in range(len(packages) - 1):
            src = packages[i]
            dst = packages[i + 1]
            transition_counts[src][dst] += 1
            state_totals[src] += 1

        all_states = list({p for p in packages})
        V = len(all_states)  # vocabulary size (unique apps)

        def transition_prob(src: str, dst: str) -> float:
            """Laplace-smoothed P(dst | src)."""
            count = transition_counts[src].get(dst, 0)
            total = state_totals.get(src, 0)
            return (count + _LAPLACE_ALPHA) / (total + _LAPLACE_ALPHA * V)

        # -- 3. Score each N-gram window ------------------------------------
        # log-likelihood of a window:  sum of log P(x_t | x_{t-1})
        window_log_likelihoods: list[float] = []
        window_positions:       list[int]   = []

        ngram = int(params.get("markov_ngram", _NGRAM)) if params else _NGRAM
        ngram = max(2, min(ngram, 5))

        for i in range(len(packages) - ngram + 1):
            window = packages[i : i + ngram]
            log_ll = 0.0
            for j in range(len(window) - 1):
                p = transition_prob(window[j], window[j + 1])
                log_ll += math.log(p)  # log prevents underflow
            window_log_likelihoods.append(log_ll)
            window_positions.append(i)

        if not window_log_likelihoods:
            return []

        ll_arr    = np.array(window_log_likelihoods, dtype=np.float64)
        ll_mean   = float(np.mean(ll_arr))
        ll_std    = float(np.std(ll_arr)) or 1.0
        z_scores  = (ll_arr - ll_mean) / ll_std

        # -- 4. Flag anomalous windows (Z < -2) -----------------------------
        threshold = float(params.get("markov_z_threshold", -2.0)) if params else -2.0

        results: list[AnomalyResult] = []
        anomaly_indices = np.where(z_scores < threshold)[0]

        # Sort by most anomalous first
        anomaly_indices = sorted(anomaly_indices, key=lambda i: z_scores[i])

        for ai in anomaly_indices[:10]:
            pos   = window_positions[ai]
            z     = float(z_scores[ai])
            ll    = float(ll_arr[ai])
            window = packages[pos : pos + ngram]
            ts    = timestamps[pos] if pos < len(timestamps) else ""

            # Build a readable representation of the transition chain
            chain_str = " → ".join(
                pkg.split(".")[-1] if "." in pkg else pkg  # use last segment for readability
                for pkg in window
            )

            results.append(AnomalyResult(
                algorithm=self.algorithm_name,
                algorithm_id=self.algorithm_id,
                category=self.category,
                severity="High" if z < -3.0 else "Medium",
                anomaly=(
                    f"Anomalous app-launch sequence detected: [{chain_str}] "
                    f"(log-likelihood={ll:.2f}, Z={z:.2f}σ)"
                ),
                score=round(min(abs(z) / 5.0, 1.0), 4),
                event_timestamp=ts,
                details={
                    "sequence":         window,
                    "readable_chain":   chain_str,
                    "log_likelihood":   round(ll, 4),
                    "z_score":          round(z, 4),
                    "window_size":      ngram,
                    "sequence_start_idx": int(pos),
                },
            ))

        return results
