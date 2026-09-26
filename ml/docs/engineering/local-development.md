# Engineering: Local Development Setup

Guide for setting up a native Python development environment for ML Eaves Droid.

---

## 1. Environment Setup

* **Python 3.12+**
* Virtual environment tool (`venv` or `uv`)

### Setup Steps
```bash
# 1. Create and activate a virtual environment
python3 -m venv venv
source venv/bin/activate

# 2. Install dependencies
pip install --upgrade pip
pip install -r requirements.txt

# 3. Copy environment variables
cp .env.example .env
```

### Running with Live Reload
```bash
uvicorn app.main:app --reload --host 0.0.0.0 --port 9070
```
Open [http://localhost:9070/docs](http://localhost:9070/docs) to access Swagger UI.
