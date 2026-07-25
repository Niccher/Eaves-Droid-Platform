"""
Abstract base class for all anomaly detectors.

Each detector implements ``detect(user_id, scope, incremental_since)``.
The caller (the analyze router) never passes raw data — detectors query
the shared MySQL database directly using the column names that match the
PHP web app's schema.
"""

from abc import ABC, abstractmethod
from app.models.schemas import AnomalyResult


class BaseDetector(ABC):
    """
    Every detector must set these three class-level attributes.

    - ``algorithm_id`` — machine name, e.g. ``act_lstm``
    - ``algorithm_name`` — display name, e.g. ``LSTM Sequence Pattern Predictor``
    - ``category`` — category key, e.g. ``activity``
    """

    algorithm_id: str
    algorithm_name: str
    category: str

    @abstractmethod
    async def detect(self, user_id: int, scope: str = "full",
                     incremental_since: str | None = None) -> list[AnomalyResult]:
        """
        Query the database for *user_id*'s data (respecting *scope* /
        *incremental_since*), run the detection logic, and return findings.
        """
        ...
