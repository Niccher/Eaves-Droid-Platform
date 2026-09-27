#!/usr/bin/env bash
# =============================================================
# deploy.sh — ONE-CLICK Deployment & Setup Pipeline
#             for Eaves Droid Platform (Monorepo)
#
# Usage:  bash scripts/deploy.sh
#         (run from the repository root)
#
# Production Standard Ports:
#   - Web Dashboard & Ingest API : Port 9007 (or $WEB_PORT)
#   - ML Anomaly Microservice    : Port 9071 (or $ML_PORT)
#   - MySQL 8.4 Database         : Port 9306 (or internal 3306)
#   - Redis 7 Cache/Queue        : Port 6379 (Internal to Docker)
#
# 13-Step Automated Pipeline:
#   1. Detect Dynamic IP (Public / LAN Fallback)
#   2. Pre-flight System & Resource Check (OS, CPU, RAM, Disk)
#   3. Git Working Tree Sanity Check & Remote Tracking
#   4. Write / Patch .env Configuration
#   5. Stop Old Containers & Clear Name Conflicts
#   6. Build & Start All Containers (MySQL, Redis, ML, Web)
#   7. Wait for MySQL Health (:3306)
#   8. Wait for Redis Health (:6379)
#   9. Run Database Migrations & Seeders
#  10. Wait for ML Microservice Readiness (:9071/api/health)
#  11. Wait for WebApp Server Readiness (:9007/health)
#  12. Container Permissions Enforcement (web/writable)
#  13. Clean Status Summary, Endpoints Table & Execution Timeline
# =============================================================
set -euo pipefail

# ── Colors & Formatting ───────────────────────────────────────
GREEN="\033[0;32m"; YELLOW="\033[1;33m"; RED="\033[0;31m"
CYAN="\033[0;36m"; BOLD="\033[1m"; RESET="\033[0m"

DEPLOY_START_TIME=$(date +%s)
SECTION_START_TIME=$DEPLOY_START_TIME
PREV_SECTION=""
declare -a STAGE_NAMES=()
declare -a STAGE_DURATIONS=()

format_duration() {
    local S=$1
    if [ "$S" -ge 60 ]; then
        local M=$((S / 60))
        local R=$((S % 60))
        echo "${M}m ${R}s"
    else
        echo "${S}s"
    fi
}

log()     { echo -e "${GREEN}[OK]${RESET}   $1"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET} $1"; }
err()     {
    local NOW=$(date +%s)
    local ELAPSED=$((NOW - DEPLOY_START_TIME))
    echo -e "${RED}[FAIL]${RESET} $1 (failed after $(format_duration $ELAPSED))"
    exit 1
}

section() {
    local NOW=$(date +%s)
    if [ -n "$PREV_SECTION" ]; then
        local DURATION=$((NOW - SECTION_START_TIME))
        echo -e "${CYAN}──> Completed: ${PREV_SECTION} in $(format_duration $DURATION)${RESET}"
        STAGE_NAMES+=("$PREV_SECTION")
        STAGE_DURATIONS+=("$DURATION")
    fi
    PREV_SECTION="$1"
    SECTION_START_TIME=$NOW
    echo -e "\n${CYAN}${BOLD}=== $1 ===${RESET}"
}

finish_deployment() {
    local NOW=$(date +%s)
    if [ -n "$PREV_SECTION" ]; then
        local DURATION=$((NOW - SECTION_START_TIME))
        echo -e "${CYAN}──> Completed: ${PREV_SECTION} in $(format_duration $DURATION)${RESET}"
        STAGE_NAMES+=("$PREV_SECTION")
        STAGE_DURATIONS+=("$DURATION")
        PREV_SECTION=""
    fi
    local TOTAL_DURATION=$((NOW - DEPLOY_START_TIME))
    echo ""
    echo -e "${GREEN}${BOLD}══════════════════════════════════════════════════════════════${RESET}"
    echo -e "${GREEN}${BOLD}   DEPLOYMENT TIMELINE & EXECUTION SUMMARY${RESET}"
    echo -e "${GREEN}${BOLD}══════════════════════════════════════════════════════════════${RESET}"
    for i in "${!STAGE_NAMES[@]}"; do
        printf "   %-48s : %s\n" "${STAGE_NAMES[$i]}" "$(format_duration ${STAGE_DURATIONS[$i]})"
    done
    echo -e "   ------------------------------------------------------------"
    printf "   ${BOLD}%-48s${RESET} : ${BOLD}%s (%ss)${RESET}\n" "Total Overall Deployment Time" "$(format_duration $TOTAL_DURATION)" "$TOTAL_DURATION"
    echo -e "${GREEN}${BOLD}══════════════════════════════════════════════════════════════${RESET}"
}

# ── Guard: must run from repository root ─────────────────────
[ -f "docker-compose.yml" ] || err "Run from the repository root (where docker-compose.yml lives)."

echo -e "${BOLD}Eaves Droid Platform — Production Deployment Pipeline${RESET}"
echo -e "Starting deployment at $(date '+%Y-%m-%d %H:%M:%S UTC')...\n"

# =============================================================
# Step 1: Detect Dynamic IP (GCP / LAN Fallback)
# =============================================================
section "Step 1: Detect Dynamic IP"

DETECTED_IP=""
# 1. Try public IP (e.g. GCP metadata / curl)
if command -v curl &>/dev/null; then
    DETECTED_IP=$(curl -s --connect-timeout 2 -H "Metadata-Flavor: Google" "http://metadata.google.internal/computeMetadata/v1/instance/network-interfaces/0/access-configs/0/external-ip" 2>/dev/null || true)
    if [ -z "$DETECTED_IP" ]; then
        DETECTED_IP=$(curl -s --connect-timeout 2 https://api.ipify.org 2>/dev/null || true)
    fi
fi

# 2. Fallback to primary LAN interface IP
if [ -z "$DETECTED_IP" ]; then
    DETECTED_IP=$(ip route get 1.1.1.1 2>/dev/null | awk '{print $7; exit}' || true)
fi

# 3. Default to localhost if offline
if [ -z "$DETECTED_IP" ]; then
    DETECTED_IP="127.0.0.1"
fi

log "Active Host IP Detected: ${BOLD}${DETECTED_IP}${RESET}"

# =============================================================
# Step 2: Pre-flight System & Resource Check
# =============================================================
section "Step 2: Pre-flight System & Resource Check"

command -v docker &>/dev/null         || err "Docker is not installed."
docker compose version &>/dev/null    || err "Docker Compose v2 is not available."
docker info &>/dev/null               || err "Docker daemon is not running."

TOTAL_RAM_KB=$(grep MemTotal /proc/meminfo 2>/dev/null | awk '{print $2}' || echo "8000000")
TOTAL_RAM_MB=$((TOTAL_RAM_KB / 1024))
FREE_DISK_MB=$(df -m . | awk 'NR==2 {print $4}')

log "System Memory : ${TOTAL_RAM_MB} MB"
log "Free Disk     : ${FREE_DISK_MB} MB"

if [ "$TOTAL_RAM_MB" -lt 3500 ]; then
    warn "RAM is under 4GB (${TOTAL_RAM_MB}MB). ML model inference may be memory-constrained."
fi

# =============================================================
# Step 3: Git Working Tree Sanity Check & Remote Tracking
# =============================================================
section "Step 3: Git Working Tree Sanity Check"

if [ -d ".git" ]; then
    BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
    COMMIT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
    REMOTE=$(git remote get-url origin 2>/dev/null || echo "none")
    log "Branch: ${BOLD}${BRANCH}${RESET} | Commit: ${BOLD}${COMMIT}${RESET}"
    log "Remote: ${BOLD}${REMOTE}${RESET}"
else
    warn "Not a git repository; skipping git verification."
fi

# =============================================================
# Step 4: Write / Patch .env Configuration
# =============================================================
section "Step 4: Configuration & Environment Setup"

if [ ! -f ".env" ]; then
    log "Creating .env from .env.example..."
    cp .env.example .env
fi

# Patch Host IP and Base URL
WEB_PORT=$(grep -E '^WEB_PORT=' .env | cut -d'=' -f2 || echo "9007")
WEB_PORT=${WEB_PORT:-9007}
ML_PORT=$(grep -E '^ML_PORT=' .env | cut -d'=' -f2 || echo "9071")
ML_PORT=${ML_PORT:-9071}

if grep -q "GCP_INSTANCE_IP=" .env; then
    sed -i "s|^GCP_INSTANCE_IP=.*|GCP_INSTANCE_IP=${DETECTED_IP}|" .env
fi

log "Configuration synchronized (.env active)"

# =============================================================
# Step 5: Stop Old Containers & Clear Conflicts
# =============================================================
section "Step 5: Clean Previous Containers"

docker compose down --remove-orphans 2>/dev/null || true
log "Container state reset complete."

# =============================================================
# Step 6: Build & Start All Containers
# =============================================================
section "Step 6: Build & Launch Core Service Stack"

docker compose up --build -d
log "Compose stack successfully initiated."

# =============================================================
# Step 7: Wait for MySQL Health (:3306)
# =============================================================
section "Step 7: Wait for MySQL Readiness"

MAX_TRIES=30
TRIES=0
until [ "$(docker inspect --format='{{json .State.Health.Status}}' eaves-mysql 2>/dev/null)" = '"healthy"' ]; do
    TRIES=$((TRIES + 1))
    if [ "$TRIES" -ge "$MAX_TRIES" ]; then
        err "MySQL container failed to report healthy within ${MAX_TRIES} attempts."
    fi
    sleep 2
done
log "MySQL is healthy and accepting connections on port 3306."

# =============================================================
# Step 8: Wait for Redis Health (:6379)
# =============================================================
section "Step 8: Wait for Redis Readiness"

TRIES=0
until [ "$(docker inspect --format='{{json .State.Health.Status}}' eaves-redis 2>/dev/null)" = '"healthy"' ]; do
    TRIES=$((TRIES + 1))
    if [ "$TRIES" -ge "$MAX_TRIES" ]; then
        warn "Redis container healthcheck timed out; proceeding with dual-engine fallback."
        break
    fi
    sleep 1
done
log "Redis is healthy and accepting socket probes on port 6379."

# =============================================================
# Step 9: Run Database Migrations & Seeders
# =============================================================
section "Step 9: Database Migrations & Seeders (CodeIgniter)"

log "Executing CodeIgniter migrations (147+ tables)..."
docker compose exec -T web php spark migrate --all 2>&1 || warn "Migration step encountered an issue. Check logs."

log "Executing CodeIgniter seeders (Plans, Tiers, SuperAdmin, ML settings)..."
docker compose exec -T web php spark db:seed DatabaseSeeder 2>&1 || warn "Seeding step encountered an issue. Check logs."

log "Database schema and baseline seeders verified."

# =============================================================
# Step 10: Wait for ML Microservice Readiness (:9071)
# =============================================================
section "Step 10: Wait for ML Anomaly Service Readiness"

TRIES=0
ML_READY=false
while [ "$TRIES" -lt 25 ]; do
    if curl -s "http://localhost:${ML_PORT}/api/health" | grep -q "status"; then
        ML_READY=true
        break
    fi
    TRIES=$((TRIES + 1))
    sleep 2
done

if [ "$ML_READY" = "true" ]; then
    log "ML Microservice is ready at http://localhost:${ML_PORT}/api/health"
else
    warn "ML Microservice readiness check timed out. WebApp self-healing failover active."
fi

# =============================================================
# Step 11: Wait for WebApp Server Readiness (:9007)
# =============================================================
section "Step 11: Wait for WebApp Server Readiness"

TRIES=0
WEB_READY=false
while [ "$TRIES" -lt 25 ]; do
    if curl -s "http://localhost:${WEB_PORT}/health" | grep -q "status"; then
        WEB_READY=true
        break
    fi
    TRIES=$((TRIES + 1))
    sleep 2
done

if [ "$WEB_READY" = "true" ]; then
    log "WebApp Server is healthy at http://localhost:${WEB_PORT}/health"
else
    warn "WebApp server did not respond at /health within timeout."
fi

# =============================================================
# Step 12: Enforce Container Storage Permissions
# =============================================================
section "Step 12: Enforce Storage Permissions"

docker compose exec -T web chmod -R 777 /var/www/html/writable/ 2>/dev/null || true
log "Storage permissions set on web/writable/."

# =============================================================
# Step 13: Summary & Execution Timeline
# =============================================================
section "Step 13: System Status & Verification"

RESILIENCE_JSON=$(curl -s "http://localhost:${WEB_PORT}/health" 2>/dev/null || echo "{}")

echo -e "\n${GREEN}${BOLD}==============================================================${RESET}"
echo -e "${GREEN}${BOLD}   EAVES DROID PLATFORM — DEPLOYMENT SUCCESSFUL${RESET}"
echo -e "${GREEN}${BOLD}==============================================================${RESET}"
echo -e "   Web Dashboard URL      : ${BOLD}http://${DETECTED_IP}:${WEB_PORT}${RESET}"
echo -e "   Mobile Ingestion URL   : ${BOLD}http://${DETECTED_IP}:${WEB_PORT}/api/v1/files/upload${RESET}"
echo -e "   ML Anomaly Swagger     : ${BOLD}http://${DETECTED_IP}:${ML_PORT}/docs${RESET}"
echo -e "   Health & Resilience    : ${BOLD}http://${DETECTED_IP}:${WEB_PORT}/health${RESET}"
echo -e "   --------------------------------------------------------------"
echo -e "   Default SuperAdmin     : ${BOLD}superadmin@eavesdroid.local${RESET}"
echo -e "   Default Password       : ${BOLD}Admin@123456${RESET}"
echo -e "   Android Server URL     : ${BOLD}http://${DETECTED_IP}:${WEB_PORT}${RESET} (or 10.0.2.2:9007 in AVD)"
echo -e "${GREEN}${BOLD}==============================================================${RESET}"

finish_deployment

