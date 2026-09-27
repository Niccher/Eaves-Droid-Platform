#!/usr/bin/env bash
# =============================================================
# gcp_setup.sh — Automated Google Cloud Platform Compute Engine
#                Provisioning & Bootstrap Script
#                for Eaves Droid Platform Monorepo
#
# Target OS: Ubuntu 22.04 LTS / Ubuntu 24.04 LTS (Debian-compatible)
# Instance recommendation: e2-standard-4 (4 vCPU, 16 GB RAM, 50 GB SSD)
#
# Usage:
#   sudo bash scripts/gcp_setup.sh
#   OR as a GCP Compute Engine Startup Script (metadata startup-script)
# =============================================================
set -euo pipefail

GREEN="\033[0;32m"
YELLOW="\033[1;33m"
RED="\033[0;31m"
CYAN="\033[0;36m"
BOLD="\033[1m"
RESET="\033[0m"

log()  { echo -e "${GREEN}[OK]${RESET}   $1"; }
warn() { echo -e "${YELLOW}[WARN]${RESET} $1"; }
err()  { echo -e "${RED}[FAIL]${RESET} $1"; exit 1; }

echo -e "\n${BOLD}${CYAN}==============================================================${RESET}"
echo -e "${BOLD}${CYAN}   Eaves Droid Platform — GCP Compute Engine Provisioner   ${RESET}"
echo -e "${BOLD}${CYAN}==============================================================${RESET}\n"

# 1. Root check
if [ "$EUID" -ne 0 ]; then
    err "This provisioning script must be run as root (or with sudo)."
fi

# 2. Update System Packages
log "Updating APT package index and installing base dependencies..."
apt-get update -y
DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release \
    git \
    jq \
    ufw \
    net-tools \
    htop \
    unzip

# 3. Install Official Docker Engine & Docker Compose Plugin
if ! command -v docker &> /dev/null; then
    log "Configuring Docker official repository..."
    OS_ID="$(. /etc/os-release && echo "$ID")"
    if [ "$OS_ID" != "ubuntu" ] && [ "$OS_ID" != "debian" ]; then
        OS_ID="debian"
    fi
    DISTRO_CODENAME="$(. /etc/os-release && echo "$VERSION_CODENAME")"

    curl -fsSL "https://download.docker.com/linux/${OS_ID}/gpg" -o /etc/apt/keyrings/docker.asc
    chmod a+r /etc/apt/keyrings/docker.asc

    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/${OS_ID} \
      ${DISTRO_CODENAME} stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

    apt-get update -y
    log "Installing Docker CE, CLI, and Compose plugin..."
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        docker-ce \
        docker-ce-cli \
        containerd.io \
        docker-buildx-plugin \
        docker-compose-plugin
    log "Docker engine installed successfully ($(docker --version))."
else
    log "Docker is already installed ($(docker --version))."
fi

# Ensure docker service is running and enabled
systemctl enable docker
systemctl start docker
log "Docker service is active and enabled on boot."

# 4. Configure User Permissions
TARGET_USER="${SUDO_USER:-$(logname 2>/dev/null || echo "")}"
if [ -n "$TARGET_USER" ] && id "$TARGET_USER" &>/dev/null; then
    usermod -aG docker "$TARGET_USER"
    log "Added user '${TARGET_USER}' to the docker group."
fi

# 5. Configure Firewall (UFW)
log "Configuring Host Firewall (UFW)..."
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp comment 'SSH'
ufw allow 80/tcp comment 'HTTP / Reverse Proxy'
ufw allow 443/tcp comment 'HTTPS / TLS Reverse Proxy'
ufw allow 9007/tcp comment 'Eaves Droid WebApp & Telemetry Ingestion'
ufw allow 9071/tcp comment 'ML Anomaly Detection Microservice'

# Note: Port 9306 (MySQL) and 6379 (Redis) are intentionally restricted to Docker bridge
# and not opened globally in UFW to protect against external attacks.

if ! ufw status | grep -q "Status: active"; then
    echo "y" | ufw enable || warn "Could not automatically enable UFW. Please verify manually."
    log "UFW firewall activated with security rules."
else
    ufw reload
    log "UFW firewall rules reloaded."
fi

# 6. Kernel Memory and Swap Tuning for High-Volume Telemetry
log "Applying kernel optimizations for high-throughput packet and telemetry processing..."
sysctl -w vm.max_map_count=262144 > /dev/null 2>&1 || true
sysctl -w net.core.somaxconn=4096 > /dev/null 2>&1 || true

# Persist sysctl parameters
if ! grep -q "vm.max_map_count" /etc/sysctl.conf; then
    echo "vm.max_map_count=262144" >> /etc/sysctl.conf
    echo "net.core.somaxconn=4096" >> /etc/sysctl.conf
fi

# 7. Setup Complete Summary
PUBLIC_IP=$(curl -s -4 ifconfig.me || curl -s -4 api.ipify.org || echo "<YOUR_GCP_VM_IP>")

echo -e "\n${BOLD}${GREEN}==============================================================${RESET}"
echo -e "${BOLD}${GREEN}   GCP Compute Engine Host Provisioned Successfully!       ${RESET}"
echo -e "${BOLD}${GREEN}==============================================================${RESET}"
echo -e " Host Public IP     : ${CYAN}${PUBLIC_IP}${RESET}"
echo -e " Docker Engine      : ${GREEN}Active & Enabled${RESET}"
echo -e " Monorepo Directory : $(pwd)"
echo -e "\n${BOLD}Next Deployment Step:${RESET}"
echo -e " Run the platform deployment pipeline:"
echo -e "   ${CYAN}bash scripts/deploy.sh${RESET}\n"
