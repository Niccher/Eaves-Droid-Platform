import numpy as np
from sklearn.ensemble import IsolationForest


def run_iforest(
    X: np.ndarray,
    n_estimators: int = 200,
    max_samples: int | str = "auto",
    contamination: float = 0.05,
) -> tuple[np.ndarray, np.ndarray]:
    model = IsolationForest(
        n_estimators=n_estimators,
        max_samples=max_samples,
        contamination=contamination,
        random_state=42,
        n_jobs=-1,
    )
    preds = model.fit_predict(X)
    scores = model.score_samples(X)
    return preds, scores
