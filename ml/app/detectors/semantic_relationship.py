from __future__ import annotations

import logging
import json
from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult
from sqlalchemy import text
from app.utils.db import get_engine
from app.services.llm_provider import generate_text

logger = logging.getLogger(__name__)

class SemanticRelationshipDetector(BaseDetector):
    algorithm_id   = "semantic_relationship"
    algorithm_name = "LLM Semantic Relationship Tagger"
    category       = "contacts"

    async def detect(
        self,
        user_id: int,
        scope: str = "full",
        incremental_since: str | None = None,
        params: dict | None = None,
    ) -> list[AnomalyResult]:
        
        # Get top 5 most messaged contacts
        with get_engine().connect() as conn:
            top_contacts = conn.execute(text("""
                SELECT address, COUNT(*) as msg_count 
                FROM tbl_extracted_sms 
                WHERE owner_id = :uid 
                GROUP BY address 
                ORDER BY msg_count DESC 
                LIMIT 5
            """), {"uid": user_id}).fetchall()
            
            results = []
            for (address, count) in top_contacts:
                if not address: continue
                
                # Get last 10 messages for context
                msgs = conn.execute(text("""
                    SELECT body, type 
                    FROM tbl_extracted_sms 
                    WHERE owner_id = :uid AND address = :addr 
                    ORDER BY sms_date DESC 
                    LIMIT 10
                """), {"uid": user_id, "addr": address}).fetchall()
                
                if not msgs: continue
                
                # Format transcript for LLM
                transcript = "\n".join([f"{'Sent' if m.type == 2 else 'Received'}: {m.body}" for m in msgs])
                
                prompt_sys = (
                    "You are a behavioral analyst. Based on this SMS transcript between the device owner and a contact, "
                    "tag their relationship (e.g. 'Debt Collector', 'Romantic Partner', 'Family', 'Co-worker', 'Accomplice', 'Scammer'). "
                    "Also assign a risk_score from 0.0 to 1.0 (1.0 being highly illicit, threatening, or suspicious). "
                    "Respond strictly with JSON: {'relationship': '...', 'risk_score': 0.0, 'reason': '...'}"
                )
                
                try:
                    llm_resp = await generate_text(prompt_sys, transcript)
                    llm_resp = llm_resp.replace('```json', '').replace('```', '').strip()
                    llm_data = json.loads(llm_resp)
                    
                    risk = float(llm_data.get("risk_score", 0.0))
                    if risk >= 0.6:
                        results.append(AnomalyResult(
                            algorithm=self.algorithm_name,
                            algorithm_id=self.algorithm_id,
                            category=self.category,
                            severity="High" if risk >= 0.8 else "Medium",
                            anomaly=f"Contact {address} tagged as '{llm_data.get('relationship')}'. Reason: {llm_data.get('reason')}",
                            score=risk,
                            event_timestamp=None, # Overall assessment
                            details={
                                "contact": address,
                                "relationship_tag": llm_data.get('relationship'),
                                "llm_reason": llm_data.get('reason')
                            }
                        ))
                except Exception as e:
                    logger.error(f"Semantic LLM inference failed for {address}: {e}")
            
            return results
