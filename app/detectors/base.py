from abc import ABC, abstractmethod
from app.models.schemas import AnomalyResult


class BaseDetector(ABC):
    algorithm_id: str
    algorithm_name: str
    category: str

    @abstractmethod
    async def detect(self, data: list[dict], user_id: int | None = None) -> list[AnomalyResult]:
        ...
