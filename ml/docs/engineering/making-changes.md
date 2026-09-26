# Engineering: Making Changes & Adding Detectors

Guidelines for implementing new anomaly detectors and modifying service logic.

---

## 1. Step-by-Step Guide: Adding a New Detector

To register a new anomaly detection algorithm:

### Step 1: Create the Detector Class
Create `app/detectors/<algorithm_name>.py` inheriting from `BaseDetector`:
```python
from typing import List, Dict, Any
from app.detectors.base import BaseDetector
from app.models.schemas import AnomalyResult

class MyNewDetector(BaseDetector):
    def __init__(self):
        super().__init__(
            algorithm_id="my_new_detector",
            name="My New Anomaly Detector",
            category="sms"
        )

    def detect(self, user_id: int, db_session, params: Dict[str, Any]) -> List[AnomalyResult]:
        # Query MySQL tables directly via db_session
        # Perform feature extraction and inference
        # Return list of AnomalyResult objects
        return []
```

### Step 2: Register in Algorithm Registry
In `app/models/registry.py`, add your detector's metadata:
```python
ALGORITHM_REGISTRY["my_new_detector"] = {
    "name": "My New Anomaly Detector",
    "category": "sms",
    "description": "Detects unusual pattern spikes",
    "tier": "deep"
}
```

### Step 3: Register in Router Dispatcher
Import and instantiate your detector inside `app/routers/analyze.py::load_detectors()`:
```python
from app.detectors.my_new_detector import MyNewDetector
# Add to detector dictionary
```

### Step 4: Health Probes & WebApp Sync
1. Add module path to `app/routers/health.py::_DETECTOR_MODULES`.
2. Sync the algorithm ID with the CodeIgniter WebApp's `Mod_Anomalies::getAlgorithmCategories()` and `getAlgorithmTiers()`.

---

## 2. Definition of Done

- [ ] New detector adheres to `BaseDetector` contract.
- [ ] No GPU or heavy neural framework dependencies introduced.
- [ ] Unit test added under `tests/`.
- [ ] Schema added/updated in `docs/services/ml.md`.
