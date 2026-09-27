# syntax=docker/dockerfile:1

# ══════════════════════════════════════════════════════════════════════════════
# Stage 1 — builder: compiles binary extensions (gcc / g++ stay here only)
# ══════════════════════════════════════════════════════════════════════════════
FROM python:3.12-slim AS builder

RUN apt-get update && apt-get install -y --no-install-recommends \
        gcc \
        g++ \
        cmake \
        make \
        libgomp1 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY requirements.txt .

# Install with pre-built wheel repository for llama-cpp-python to avoid 15-minute C++ compile
RUN pip install --prefix=/install --no-warn-script-location \
    --extra-index-url https://abetlen.github.io/llama-cpp-python/whl/cpu \
    -r requirements.txt

# ══════════════════════════════════════════════════════════════════════════════
# Stage 2 — runtime: slim final image, no compilers in the layer
# ══════════════════════════════════════════════════════════════════════════════
FROM python:3.12-slim AS runtime

# Only the runtime shared lib is needed (no gcc/g++)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libgomp1 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy compiled packages from builder
COPY --from=builder /install /usr/local

# Copy application code (changes most frequently — kept last for cache efficiency)
COPY . .

EXPOSE 9070 9072 9073

# --reload kept intentionally for instant code updates during development
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "9070", "--reload"]
