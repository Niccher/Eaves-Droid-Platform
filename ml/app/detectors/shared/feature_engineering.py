import numpy as np
from typing import Any


def hour_to_sin_cos(hour: int) -> tuple[float, float]:
    rad = 2 * np.pi * hour / 24
    return (float(np.sin(rad)), float(np.cos(rad)))


def day_of_week_sin_cos(dow: int) -> tuple[float, float]:
    rad = 2 * np.pi * dow / 7
    return (float(np.sin(rad)), float(np.cos(rad)))


def extract_time_features(timestamps: list[str]) -> np.ndarray:
    features = []
    for ts in timestamps:
        try:
            dt = __import__("datetime").datetime.fromisoformat(ts)
            h_sin, h_cos = hour_to_sin_cos(dt.hour)
            d_sin, d_cos = day_of_week_sin_cos(dt.weekday())
            features.append([h_sin, h_cos, d_sin, d_cos])
        except Exception:
            features.append([0.0, 0.0, 0.0, 0.0])
    return np.array(features)


def normalize_features(arr: np.ndarray) -> np.ndarray:
    means = np.nanmean(arr, axis=0)
    stds = np.nanstd(arr, axis=0)
    stds[stds == 0] = 1.0
    return (arr - means) / stds
