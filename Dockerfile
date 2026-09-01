# syntax=docker/dockerfile:1

# ══════════════════════════════════════════════════════════════════════════════
# Stage 1 — builder: compiles binary extensions (gcc / g++ stay here only)
# ══════════════════════════════════════════════════════════════════════════════
FROM python:3.12-slim AS builder

RUN --mount=type=cache,id=ml-apt-cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,id=ml-apt-lib,target=/var/lib/apt,sharing=locked \
    apt-get update && apt-get install -y --no-install-recommends \
        gcc \
        g++ \
        libgomp1

WORKDIR /app
COPY requirements.txt .

# Install into a prefix directory so we can COPY just the packages to runtime.
# BuildKit pip cache means the download only happens once across all builds.
RUN --mount=type=cache,id=ml-pip-cache,target=/root/.cache/pip \
    pip install --prefix=/install --no-warn-script-location -r requirements.txt

# ══════════════════════════════════════════════════════════════════════════════
# Stage 2 — runtime: slim final image, no compilers in the layer
# ══════════════════════════════════════════════════════════════════════════════
FROM python:3.12-slim AS runtime

# Only the runtime shared lib is needed (no gcc/g++)
RUN --mount=type=cache,id=ml-runtime-apt-cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,id=ml-runtime-apt-lib,target=/var/lib/apt,sharing=locked \
    apt-get update && apt-get install -y --no-install-recommends \
        libgomp1

WORKDIR /app

# Copy compiled packages from builder
COPY --from=builder /install /usr/local

# Copy application code (changes most frequently — kept last for cache efficiency)
COPY . .

EXPOSE 9070 9072 9073

# --reload kept intentionally for instant code updates during development
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "9070", "--reload"]
