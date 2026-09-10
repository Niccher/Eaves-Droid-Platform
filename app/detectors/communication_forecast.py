from __future__ import annotations

import logging
from datetime import datetime
import pandas as pd
from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult
from sqlalchemy import text
from app.utils.db import get_engine

logger = logging.getLogger(__name__)

class CommunicationForecaster(BaseDetector):
    algorithm_id   = "comm_forecast"
    algorithm_name = "Communication Volume Forecaster"
    category       = "contacts"

    async def detect(
        self,
        user_id: int,
        scope: str = "full",
        incremental_since: str | None = None,
        params: dict | None = None,
    ) -> list[AnomalyResult]:
        try:
            from statsmodels.tsa.holtwinters import ExponentialSmoothing
        except ImportError:
            logger.error("statsmodels not installed, skipping CommunicationForecaster")
            return []

        # Fetch all calls and SMS for the user to build a time series
        with get_engine().connect() as conn:
            # We group by hour
            rows = conn.execute(text("""
                SELECT 
                    DATE_FORMAT(FROM_UNIXTIME(date / 1000), '%Y-%m-%d %H:00:00') as hr,
                    COUNT(*) as vol
                FROM tbl_extracted_call_logs
                WHERE token_owner_id = :uid
                GROUP BY hr
                ORDER BY hr ASC
            """), {"uid": user_id}).fetchall()

        if len(rows) < 48:
            # Not enough data (need at least 2 days to even attempt a weak forecast)
            return []

        df = pd.DataFrame(rows, columns=['hr', 'vol'])
        df['hr'] = pd.to_datetime(df['hr'])
        df.set_index('hr', inplace=True)
        # Resample to fill missing hours with 0
        df = df.resample('H').sum().fillna(0)

        # Train Holt-Winters model (Daily seasonality = 24 hours)
        try:
            model = ExponentialSmoothing(df['vol'], seasonal_periods=24, trend='add', seasonal='add', initialization_method="estimated")
            fit_model = model.fit()
        except Exception as e:
            logger.error(f"Holt-Winters fitting failed: {e}")
            return []

        # Get the most recent hour's actual volume
        last_hour = df.index[-1]
        actual_vol = df['vol'].iloc[-1]
        
        # Get the predicted volume for the last hour
        try:
            fitted_values = fit_model.fittedvalues
            expected_vol = fitted_values.iloc[-1]
        except Exception:
            return []

        # Calculate error. We set a threshold for anomaly if actual is way higher than expected
        # e.g., if expected is 2 and actual is 20
        upper_bound = max(expected_vol + 5, expected_vol * 3) # heuristic bound
        
        results = []
        if actual_vol > upper_bound and actual_vol > 10:
            score = min(1.0, (actual_vol - expected_vol) / 50.0 + 0.5)
            results.append(AnomalyResult(
                algorithm=self.algorithm_name,
                algorithm_id=self.algorithm_id,
                category=self.category,
                severity="High" if score > 0.8 else "Medium",
                anomaly=f"Unexpected communication spike: {int(actual_vol)} events logged vs {int(expected_vol)} expected for {last_hour.strftime('%I %p')}",
                score=round(score, 4),
                event_timestamp=last_hour.isoformat(),
                details={
                    "actual_volume": int(actual_vol),
                    "expected_volume": int(expected_vol),
                    "upper_bound": round(upper_bound, 2),
                    "hour": last_hour.isoformat()
                }
            ))

        return results
