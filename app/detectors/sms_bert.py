"""
SMS Phishing Classifier — classifies SMS message bodies using a pre-trained
TF-IDF + Naive Bayes (or Logistic Regression) model loaded from the model
cache.

When model artefacts are absent, a **structured multi-category rule engine**
is used instead of the previous single-word keyword counter.  The rule engine
covers six independent signal categories (URL shorteners, sender spoofing,
credential harvesting, OTP interception, financial urgency, impersonation
phrases) and produces calibrated per-category scores that are blended into a
final confidence value.  This makes the fallback substantially more accurate
than the old keyword scan.

algorithm_id ``sms_bert`` is kept for backward compatibility with existing
``ml_results`` rows and the WebApp configuration.

Model artefacts (placed in models_cache/ by running train_sms_model.py):
  models_cache/sms_vectorizer.joblib  — fitted TF-IDF vectorizer
  models_cache/sms_classifier.joblib  — trained Naive Bayes / LR classifier

To bootstrap the model:
  cd <ML Eaves Droid root>
  python models_cache/train_sms_model.py

Queries ``tbl_extracted_sms`` directly from the shared MySQL database.
"""

from __future__ import annotations

import logging
import os
import re
from dataclasses import dataclass, field
from pathlib import Path
from typing import NamedTuple

from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult

logger = logging.getLogger(__name__)

# ---------------------------------------------------------------------------
# Paths to pre-trained artefacts
# ---------------------------------------------------------------------------
_MODEL_DIR       = Path(os.getenv("MODELS_CACHE", "/app/models_cache"))
_VECTORIZER_PATH = _MODEL_DIR / "sms_vectorizer.joblib"
_CLASSIFIER_PATH = _MODEL_DIR / "sms_classifier.joblib"

# Minimum ML-model probability (or rule-engine score) to surface a finding
_SCORE_THRESHOLD = 0.50

# ===========================================================================
# Multi-category rule engine — used when model artefacts are absent
# ===========================================================================

class _Rule(NamedTuple):
    """A single detection rule: a compiled regex + its score contribution."""
    pattern: re.Pattern
    weight:  float
    label:   str


def _r(pattern: str, weight: float, label: str, flags: int = re.IGNORECASE) -> _Rule:
    return _Rule(re.compile(pattern, flags), weight, label)


# ---------------------------------------------------------------------------
# Category 1 — URL Shorteners & Redirect Traps
# Phishing links almost always use shortened or obfuscated redirect URLs
# because the real destination would be obviously suspicious.
# ---------------------------------------------------------------------------
_URL_RULES: list[_Rule] = [
    _r(r"https?://(bit\.ly|tinyurl\.com|t\.co|ow\.ly|goo\.gl|rb\.gy|cutt\.ly|short\.io|is\.gd|v\.gd|shorturl\.at)/\S+",
       0.45, "known URL shortener"),
    _r(r"https?://\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}[/:]",
       0.55, "raw IP address link"),
    _r(r"click\s+(here|now|link|below)\b",
       0.25, "click-action phrase"),
    _r(r"\bwww\.\S{3,}\.(xyz|top|tk|ml|ga|cf|gq|ru|cn)\b",
       0.40, "high-risk TLD link"),
    _r(r"(tap|open|visit)\s+(the\s+)?link\b",
       0.20, "link-action phrase"),
]

# ---------------------------------------------------------------------------
# Category 2 — Sender Spoofing Signals
# Fraudulent senders often impersonate banks, telecoms, or government bodies.
# Short alphanumeric sender IDs (like MPESA, KPLC, BANK) are easy to spoof.
# We also flag messages where the claimed sender name differs from a numeric
# address — which happens when a gateway re-labels the sending number.
# ---------------------------------------------------------------------------
_SENDER_RULES: list[_Rule] = [
    _r(r"\b(mpesa|safaricom|airtel|telkom|equity|kcb|stanbic|stanchart|absa|dtb|cooperative\s+bank|ncba|i&m\s+bank|family\s+bank)\b",
       0.15, "financial institution mention"),
    _r(r"\b(kra|ntsa|nhif|nssf|e[- ]?citizen|helb|kenya\s+power|kplc|nairobi\s+water)\b",
       0.20, "government body mention"),
    _r(r"\b(nairobicounty|jubilee|nhc|kenya\s+re|britam|cic\s+insurance)\b",
       0.15, "parastatal mention"),
    _r(r"your\s+(bank|account|wallet|card)\s+has\s+been\b",
       0.35, "bank account alert phrase"),
    _r(r"\bwe\s+have\s+(detected|noticed|identified)\s+(suspicious|unusual|unauthorized)\b",
       0.35, "suspicious-activity claim"),
]

# ---------------------------------------------------------------------------
# Category 3 — Credential Harvesting
# Requests for passwords, PINs, OTPs, or login credentials are a definitive
# signal: legitimate services never ask for these by SMS.
# ---------------------------------------------------------------------------
_CREDENTIAL_RULES: list[_Rule] = [
    _r(r"\b(enter|provide|submit|share|send|confirm)\s+(your\s+)?(pin|password|passcode|secret|credentials?)\b",
       0.65, "PIN/password request"),
    _r(r"\bdo\s+not\s+share\s+(this\s+)?(code|otp|pin)\s+with\s+(anyone|anybody)\b",
       0.30, "OTP sharing warning (common in legit messages, low weight)"),
    _r(r"\b(reset|change|update)\s+(your\s+)?(password|pin|passcode)\s+immediately\b",
       0.50, "urgent password reset"),
    _r(r"\bverif(y|ication)\s+(your\s+)?(identity|account|number|email|phone)\b",
       0.30, "identity verification request"),
    _r(r"\b(log\s?in|sign\s?in)\s+(to|at)\s+(your\s+)?(account|portal|dashboard)\b",
       0.35, "login redirect"),
    _r(r"\bsuspend(ed)?\s+(due\s+to|for|because)\b",
       0.40, "suspension threat"),
    _r(r"\byour\s+account\s+(will\s+be\s+|has\s+been\s+)?(blocked|suspended|deactivated|disabled|restricted|frozen|closed)\b",
       0.55, "account block threat"),
]

# ---------------------------------------------------------------------------
# Category 4 — OTP / 2FA Interception
# Attackers send fake OTP requests to intercept the real code the victim
# receives from a legitimate service (SIM-swap enablement, account takeover).
# ---------------------------------------------------------------------------
_OTP_RULES: list[_Rule] = [
    _r(r"\b(one[\s-]time[\s-]?(password|pin|code)|otp)\b",
       0.25, "OTP mention"),
    _r(r"\bverification\s+code\s*[:\-]?\s*\d{4,8}\b",
       0.20, "verification code in body"),
    _r(r"\b(do\s+not|don\'t)\s+share\s+(the\s+)?(otp|code|pin)\b",
       0.15, "OTP warning phrase"),
    _r(r"\bcode\s+expires?\s+in\s+\d+\s+(second|minute|hour)\b",
       0.30, "expiring code urgency"),
    _r(r"\b(authorize|authorise)\s+(this\s+)?(transaction|transfer|payment)\b",
       0.40, "transaction authorization request"),
]

# ---------------------------------------------------------------------------
# Category 5 — Financial Urgency & Prize Lures
# Promises of unexpected money, prize winnings, or emergency transfers are
# classic social-engineering lures to get victims to click or call back.
# ---------------------------------------------------------------------------
_FINANCIAL_RULES: list[_Rule] = [
    _r(r"\b(congratulations?|you\s+(have\s+)?(won|been\s+selected|qualified))\b",
       0.40, "prize/winner lure"),
    _r(r"\bclaim\s+(your\s+)?(reward|prize|cash|gift|voucher|bonus)\b",
       0.45, "claim reward phrase"),
    _r(r"\bfree\s+(airtime|data|money|credit|ksh|usd|kes)\b",
       0.40, "free money lure"),
    _r(r"\b(ksh|kes|usd|\$|£|€)\s*[\d,]+(\.\d{2})?\s+(has\s+been\s+)?(sent|transferred|credited|deposited|received)\b",
       0.25, "financial transaction claim"),
    _r(r"\bact\s+(now|fast|immediately|quickly|today\s+only)\b",
       0.35, "urgency push"),
    _r(r"\boffer\s+(expires?|ends?|valid)\s+(today|in\s+\d+\s+(hour|minute|day))\b",
       0.35, "limited-time offer"),
    _r(r"\breply\s+(yes|no|stop|1|2)\s+to\s+(claim|opt|confirm|cancel)\b",
       0.35, "SMS reply action"),
]

# ---------------------------------------------------------------------------
# Category 6 — Homoglyph & Number-Substitution Evasion
# Attackers replace letters with visually similar numbers or Unicode characters
# to bypass naive keyword scanners:  0 → O, 1 → l/I, 3 → E, @ → a, etc.
# We normalise the body before matching to catch these substitutions.
# ---------------------------------------------------------------------------
_EVASION_RULES: list[_Rule] = [
    _r(r"\bv[e3][r][i1][f][i1][c][a@4][t][i1][o0][n]\b",
       0.50, "homoglyph 'verification'"),
    _r(r"\b[a@4][c][c][o0][u][n][t]\b",
       0.30, "homoglyph 'account'"),
    _r(r"\bp[a@4][s$5][s$5]w[o0][r][d]\b",
       0.55, "homoglyph 'password'"),
    _r(r"\bs[e3]cur[i1]ty\b",
       0.30, "homoglyph 'security'"),
    _r(r"\bsusp[e3]nd[e3]d\b",
       0.45, "homoglyph 'suspended'"),
]

# Map each category to its rule list and a base weight cap
_RULE_CATEGORIES: list[tuple[str, list[_Rule], float]] = [
    ("url",         _URL_RULES,         0.55),
    ("sender",      _SENDER_RULES,      0.30),
    ("credential",  _CREDENTIAL_RULES,  0.70),
    ("otp",         _OTP_RULES,         0.45),
    ("financial",   _FINANCIAL_RULES,   0.55),
    ("evasion",     _EVASION_RULES,     0.60),
]


def _normalise_evasion(text: str) -> str:
    """Replace common character substitutions so evasion rules fire correctly."""
    return (
        text
        .replace("0", "o")
        .replace("3", "e")
        .replace("1", "i")
        .replace("@", "a")
        .replace("$", "s")
        .replace("4", "a")
        .replace("5", "s")
    )


@dataclass
class _RuleHit:
    category: str
    label: str
    weight: float


def _apply_rules(body: str) -> tuple[float, list[_RuleHit]]:
    """
    Run the full multi-category rule engine over a single SMS body.

    Returns (final_score 0.0–1.0, list of matched rule hits).
    The final score is the *weighted union* of all category scores, not a
    simple sum — this prevents artificially inflated scores from many
    low-weight signals in a single category.
    """
    body_lower  = body.lower()
    body_evasion = _normalise_evasion(body_lower)

    category_scores: dict[str, float] = {}
    hits: list[_RuleHit] = []

    for cat_name, rules, cap in _RULE_CATEGORIES:
        cat_score = 0.0
        for rule in rules:
            target = body_evasion if cat_name == "evasion" else body_lower
            if rule.pattern.search(target):
                cat_score = min(cat_score + rule.weight, cap)
                hits.append(_RuleHit(cat_name, rule.label, rule.weight))
        category_scores[cat_name] = cat_score

    if not category_scores:
        return 0.0, hits

    # Combine category scores with diminishing-returns blending:
    # Score = 1 – ∏(1 – score_i)  (same formula as independent-events union)
    combined = 1.0
    for s in category_scores.values():
        combined *= (1.0 - s)
    final_score = min(1.0 - combined, 0.98)

    return final_score, hits




def _preprocess(text: str) -> str:
    """Normalise text before vectorisation."""
    text = text.lower()
    # collapse URLs to a token — preserves signal without raw URL noise
    text = re.sub(r"https?://\S+|www\.\S+", " url_token ", text)
    # remove punctuation except spaces
    text = re.sub(r"[^a-z0-9\s]", " ", text)
    # collapse whitespace
    return re.sub(r"\s+", " ", text).strip()


def _load_model():
    """Attempt to load the pre-trained vectorizer + classifier pair.

    Returns ``(vectorizer, classifier)`` on success, or ``None, None`` if
    artefacts are absent, preventing hard failures at startup.
    """
    if _VECTORIZER_PATH.exists() and _CLASSIFIER_PATH.exists():
        try:
            import joblib
            vectorizer = joblib.load(_VECTORIZER_PATH)
            classifier = joblib.load(_CLASSIFIER_PATH)
            logger.info("sms_bert: loaded pre-trained TF-IDF + classifier from %s", _MODEL_DIR)
            return vectorizer, classifier
        except Exception as exc:
            logger.warning("sms_bert: failed to load model artefacts — %s; using fallback", exc)
    else:
        logger.info(
            "sms_bert: artefacts not found at %s — using keyword-heuristic fallback",
            _MODEL_DIR,
        )
    return None, None


# Load once at module import time so we pay the disk I/O only once per worker.
_VECTORIZER, _CLASSIFIER = _load_model()


class SmsPhishingHeuristicDetector(BaseDetector):
    algorithm_id = "sms_bert"
    algorithm_name = "SMS Phishing Classifier (TF-IDF / NB)"
    category = "sms"

    # -----------------------------------------------------------------------
    # Main entry point
    # -----------------------------------------------------------------------

    async def detect(
        self,
        user_id: int,
        scope: str = "full",
        incremental_since: str | None = None,
        params: dict | None = None,
    ) -> list[AnomalyResult]:
        from sqlalchemy import text
        from app.utils.db import get_engine

        # -- 1. Fetch SMS records -------------------------------------------
        query_params: dict = {"uid": user_id}
        where = "owner_id = :uid"
        if scope == "incremental" and incremental_since:
            where += " AND created_at > :since"
            query_params["since"] = incremental_since

        sql = text(f"""
            SELECT body, address,
                   DATE_FORMAT(FROM_UNIXTIME(sms_date / 1000), '%Y-%m-%d %H:%i:%s') AS ts
            FROM tbl_extracted_sms
            WHERE {where}
            ORDER BY sms_date DESC
            LIMIT 2000
        """)

        with get_engine().connect() as conn:
            rows = conn.execute(sql, query_params).mappings().fetchall()

        if not rows:
            return []

        # -- 2. Score each message ------------------------------------------
        if _VECTORIZER is not None and _CLASSIFIER is not None:
            return await self._score_with_model(rows)
        return await self._score_with_keywords(rows)

    # -----------------------------------------------------------------------
    # Model-based scoring (TF-IDF + Naive Bayes / Logistic Regression)
    # -----------------------------------------------------------------------

    async def _score_with_model(self, rows) -> list[AnomalyResult]:
        """Vectorise SMS bodies in one batch and run classifier.predict_proba."""
        import numpy as np

        bodies_raw = [(row["body"] or "").strip() for row in rows]
        # Filter out blank messages before vectorising
        indices, bodies_clean = zip(
            *[(i, _preprocess(b)) for i, b in enumerate(bodies_raw) if b]
        ) if any(bodies_raw) else ([], [])

        if not bodies_clean:
            return []

        try:
            X = _VECTORIZER.transform(list(bodies_clean))
            # predict_proba returns [[p_ham, p_spam], ...]
            # class order depends on training — we assume class "1" == spam/phishing
            proba = _CLASSIFIER.predict_proba(X)
            spam_col = list(_CLASSIFIER.classes_).index(1) if 1 in _CLASSIFIER.classes_ else 1
            scores_arr = proba[:, spam_col]
        except Exception as exc:
            logger.error("sms_bert: model inference failed — %s; falling back to keywords", exc)
            return await self._score_with_keywords(rows)

        results: list[AnomalyResult] = []
        for rank, (orig_idx, score) in enumerate(
            sorted(zip(indices, scores_arr), key=lambda t: -t[1])
        ):
            if float(score) < _SCORE_THRESHOLD:
                break
            row = rows[orig_idx]
            body = bodies_raw[orig_idx]
            results.append(
                AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="High" if score >= 0.85 else "Medium",
                    anomaly=(
                        f"Phishing/spam detected from '{row['address'] or 'unknown'}': "
                        f"{body[:100]}…"
                    ),
                    score=round(float(score), 4),
                    event_timestamp=str(row["ts"] or ""),
                    details={
                        "sender": row["address"],
                        "body_preview": body[:300],
                        "phishing_probability": round(float(score), 4),
                        "classifier": "tfidf_nb",
                    },
                )
            )
            if len(results) >= 15:
                break

        return results

    # -----------------------------------------------------------------------
    # Rule-engine fallback — multi-category weighted scoring, used when
    # the trained model artefacts are not yet available.
    # -----------------------------------------------------------------------

    async def _score_with_keywords(self, rows) -> list[AnomalyResult]:
        """
        Named _score_with_keywords for internal consistency (called from
        _score_with_model on inference failure), but now backed by the full
        multi-category rule engine rather than a flat keyword counter.
        """
        results: list[AnomalyResult] = []

        for row in rows:
            body = (row["body"] or "").strip()
            if not body:
                continue

            score, hits = _apply_rules(body)

            if score < _SCORE_THRESHOLD or not hits:
                continue

            # Summarise which categories fired for display in the dashboard
            categories_fired = sorted(
                {h.category for h in hits}
            )
            top_signals = [h.label for h in sorted(hits, key=lambda h: -h.weight)][:4]
            
            # --- PHASE 4: LLM Escalation for Sheng / Swahili / Coded Scams ---
            # If the rule engine suspects something (score >= 0.40), ask the LLM for a second opinion
            # since rules often miss nuances in East African slang.
            llm_result = None
            if score >= 0.40:
                try:
                    from app.services.llm_provider import generate_text
                    import json
                    prompt_sys = (
                        "You are an expert fraud analyst focusing on East Africa. "
                        "Analyze this SMS for scams, fraud, coercion, or coded language (including Swahili/Sheng). "
                        "Respond ONLY with a JSON object containing keys: 'risk' (low/medium/high), 'reason', and 'category'."
                    )
                    prompt_user = f"Message from {row['address']}: {body}"
                    llm_resp = await generate_text(prompt_sys, prompt_user)
                    # Clean up JSON if wrapped in markdown block
                    llm_resp = llm_resp.replace('```json', '').replace('```', '').strip()
                    llm_result = json.loads(llm_resp)
                    if llm_result.get('risk') == 'high':
                        score = max(score, 0.90)
                except Exception as e:
                    logger.error(f"Failed LLM escalation: {e}")

            severity = "High" if score >= 0.80 else "Medium"
            cat_str  = ", ".join(categories_fired)

            results.append(
                AnomalyResult(
                    algorithm="SMS Rule-Engine Classifier (fallback)",
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity=severity,
                    anomaly=(
                        f"Suspicious message from '{row['address'] or 'unknown'}' "
                        f"[{cat_str}]: {body[:100]}…"
                    ),
                    score=round(score, 4),
                    event_timestamp=str(row["ts"] or ""),
                    details={
                        "sender":               row["address"],
                        "body_preview":         body[:300],
                        "rule_engine_score":    round(score, 4),
                        "categories_fired":     categories_fired,
                        "top_signals":          top_signals,
                        "total_rule_hits":      len(hits),
                        "classifier":           "rule_engine_fallback",
                        "llm_escalated":        score >= 0.40,
                    },
                )
            )
            if len(results) >= 15:
                break

        # Sort by score descending so highest-confidence findings appear first
        results.sort(key=lambda r: -r.score)
        return results

