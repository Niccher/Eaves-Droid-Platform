# Architecture: Google Cloud Platform (GCP) Deployment Guide

This document details the production deployment architecture for the **Eaves Droid Platform** on **Google Cloud Platform (GCP)** using **Compute Engine (GCE)**, containerized Docker Compose orchestration, Cloud Firewall security policies, and automated provisioning.

---

## 1. Infrastructure Specifications

For high-volume Android forensic telemetry, live packet captures, and real-time ML anomaly detection inference, the following GCP resource baseline is recommended:

| Resource | Recommended Specification | Minimum Specification | Rationale |
| :--- | :--- | :--- | :--- |
| **Instance Type** | `e2-standard-4` (4 vCPUs, 16 GB RAM) | `e2-standard-2` (2 vCPUs, 8 GB RAM) | Sufficient RAM for Redis 7 + MySQL buffer pool + Python ML models |
| **Boot Disk** | 50 GB SSD (`pd-ssd`) | 30 GB Balanced (`pd-balanced`) | Fast I/O for high-frequency telemetry ingestion and GC sweeps |
| **Operating System** | Ubuntu 22.04 LTS or Ubuntu 24.04 LTS x86_64 | Debian 12 Minimal | Native Docker CE and `systemd` compatibility |
| **Network IP** | Static External IPv4 Address | Ephemeral External IPv4 | Prevents DNS churn for mobile agent sync |

---

## 2. GCP VPC Firewall Configuration

Only expose necessary ingress ports to the internet. Internal database and cache communication must remain isolated within the Docker network.

| Port / Protocol | Direction | Source | Service / Purpose | Security Profile |
| :--- | :--- | :--- | :--- | :--- |
| **22 / TCP** | Ingress | Admin IP / Cloud IAP | SSH Terminal Management | Restricted / Cloud IAP recommended |
| **80 / TCP** | Ingress | `0.0.0.0/0` | HTTP / Certbot ACME Challenge | Public (redirects to 443) |
| **443 / TCP** | Ingress | `0.0.0.0/0` | HTTPS (Nginx TLS Reverse Proxy) | Public (TLS 1.3 encrypted) |
| **9007 / TCP** | Ingress | `0.0.0.0/0` | WebApp & Telemetry API | Direct or via Reverse Proxy |
| **9071 / TCP** | Ingress | Internal / Admin | ML Anomaly Microservice | Protected / Internal only |
| **9306 / TCP** | **BLOCKED** | Localhost only | MySQL 8.4 Database | **Never exposed to public internet** |
| **6379 / TCP** | **BLOCKED** | Docker bridge | Redis 7 In-Memory Cache | **Never exposed to public internet** |

### Creating Firewall Rules via Google Cloud CLI (`gcloud`):

```bash
# Allow HTTP/HTTPS traffic
gcloud compute firewall-rules create allow-http-https \
    --direction=INGRESS --priority=1000 --network=default --action=ALLOW \
    --rules=tcp:80,tcp:443 --source-ranges=0.0.0.0/0 \
    --target-tags=eaves-droid-platform

# Allow Eaves Droid WebApp & Ingestion Port
gcloud compute firewall-rules create allow-eaves-droid-webapp \
    --direction=INGRESS --priority=1000 --network=default --action=ALLOW \
    --rules=tcp:9007 --source-ranges=0.0.0.0/0 \
    --target-tags=eaves-droid-platform
```

---

## 3. Automated VM Provisioning with `gcp_setup.sh`

Deploying to a fresh GCP Compute Engine instance takes under 3 minutes using the automated bootstrap script.

### Option A: Via Compute Engine Startup Script (Automated on Creation)

When creating the instance in the GCP Cloud Console or via `gcloud`, provide the startup script in metadata:

```bash
gcloud compute instances create eaves-droid-prod \
    --zone=us-central1-a \
    --machine-type=e2-standard-4 \
    --boot-disk-type=pd-ssd \
    --boot-disk-size=50GB \
    --image-family=ubuntu-2404-lts-amd64 \
    --image-project=ubuntu-os-cloud \
    --tags=eaves-droid-platform \
    --metadata-from-file=startup-script=scripts/gcp_setup.sh
```

### Option B: Post-Boot SSH Provisioning

If connecting to an existing Ubuntu VM:

```bash
# SSH into your VM instance
gcloud compute ssh eaves-droid-prod --zone=us-central1-a

# Clone the unified monorepo
git clone https://github.com/Niccher/Eaves-Droid-Platform.git /opt/eaves-droid-platform
cd /opt/eaves-droid-platform

# Run the host provisioning script (Docker, UFW, Kernel Tuning)
sudo bash scripts/gcp_setup.sh
```

---

## 4. One-Click Container Orchestration

Once host dependencies are satisfied:

```bash
# Execute the complete 13-stage deployment pipeline
bash scripts/deploy.sh
```

The pipeline automatically:
1. Detects the GCP external IP and binds it to `BASE_URL`.
2. Generates secure production passwords and secrets in `.env`.
3. Boots MySQL 8.4, Redis 7, Python 3.12 ML service, and Apache/PHP 8.3 WebApp.
4. Executes CodeIgniter migrations and seeds ML endpoints into the database.
5. Verifies health endpoints on ports 9007 and 9071.

---

## 5. Production TLS / Reverse Proxy Architecture

In a hardened production GCP deployment, place **Nginx** or **Traefik** as a reverse proxy in front of Docker containers with automated **Let's Encrypt** TLS certificates:

```
[ Android Agents & Browsers ]
             │
       HTTPS / TLS 1.3 (Port 443)
             ▼
   [ Host Nginx Reverse Proxy ]
             │
   ┌─────────┴─────────┐
   │ proxy_pass        │ proxy_pass
   ▼                   ▼
[ WebApp :9007 ]   [ ML Service :9071 ]
   │                   │
   └─────────┬─────────┘
             │ Docker Internal Bridge
   ┌─────────┴─────────┐
   ▼                   ▼
[ Redis 7 ]        [ MySQL 8.4 ]
```

---

## 6. Monitoring & Self-Healing Diagnostics

- **Platform Health Endpoint:** `http://<GCP_EXTERNAL_IP>:9007/health`
- **ML Anomaly Health Endpoint:** `http://<GCP_EXTERNAL_IP>:9071/api/health`
- **Resilience Benchmark Utility:**
  ```bash
  python3 scripts/generate_synthetic_telemetry.py --mode=benchmark --iterations=50
  ```
- **Automated Session GC:**
  ```bash
  docker compose exec web php spark session:gc --max-age=86400
  ```
