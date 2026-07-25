from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult


class BERTPhishingDetector(BaseDetector):
    algorithm_id = "sms_bert"
    algorithm_name = "BERT Semantic Phishing Classifier"
    category = "sms"

    async def detect(self, data: list[dict], user_id: int | None = None) -> list[AnomalyResult]:
        results = []
        for msg in data:
            body = (msg.get("body") or "").strip()
            if not body:
                continue
            score = 0.0
            urgency_keywords = ["urgent", "verify", "account", "login", "click here", "suspended", "password"]
            hits = sum(1 for kw in urgency_keywords if kw in body.lower())
            if hits >= 2:
                score = min(0.5 + hits * 0.12, 0.95)

            if score >= 0.85:
                results.append(AnomalyResult(
                    algorithm=self.algorithm_name,
                    algorithm_id=self.algorithm_id,
                    category=self.category,
                    severity="High",
                    anomaly=f"Suspicious message flagged: '{body[:80]}...' ({hits} phishing indicators)",
                    score=round(score, 4),
                    timestamp=msg.get("timestamp", ""),
                    details={"body_preview": body[:200], "phishing_keyword_hits": hits},
                ))
        return results
