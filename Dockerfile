FROM python:3.12-slim

WORKDIR /app

# Install build deps (gcc needed for some scikit-learn/sqlalchemy wheels on slim)
RUN apt-get update && apt-get install -y --no-install-recommends \
    gcc \
    g++ \
    libgomp1 \
    && rm -rf /var/lib/apt/lists/*

# Install CPU-only PyTorch from the official CPU wheel index.
# This avoids downloading ~1 GB of CUDA libraries and cuts build time in half.
#RUN pip install --no-cache-dir \
RUN pip install --no-cache-dir \
    --extra-index-url https://download.pytorch.org/whl/cpu \
    torch==2.5.1

# Install the remaining dependencies (all lightweight, no CUDA needed)
COPY requirements.txt .
#RUN pip install --no-cache-dir -r requirements.txt
RUN pip install -r requirements.txt

COPY . .

EXPOSE 8000 8501 9090

CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8000", "--reload"]
