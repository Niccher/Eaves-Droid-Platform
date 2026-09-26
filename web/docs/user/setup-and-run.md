# User Guide: Setup and Run

This guide walks operators through starting the **Eaves Droid WebApp** using Docker Compose or native PHP.

---

## 1. Prerequisites

### Option A: Docker (Recommended)
* **Docker Engine** 24.0+
* **Docker Compose v2** (or Docker Desktop)
* Git

### Option B: Native Host Environment
* **PHP 8.3** with extensions: `php-cli`, `php-curl`, `php-mysql`, `php-mbstring`, `php-intl`, `php-xml`
* **Composer 2.7+**
* **MySQL 8.4** server running locally or over LAN

---

## 2. Running via Docker Compose

### Step 1: Clone and Configure
Clone the repository and copy the environment configuration:
```bash
git clone https://github.com/Niccher/Eaves-Droid-WebApp.git
cd Eaves-Droid-WebApp
cp env .env
```

### Step 2: Launch Containers
Start the containers in the background:
```bash
docker compose up --build -d
```
Verify container health:
```bash
docker compose ps
```
The `shared-mysql` container should indicate `healthy` before traffic reaches `eaves-droid-webapp`.

### Step 3: Run Migrations and Seed Initial Data
Run the CodeIgniter migrations and seed the initial plans and superadmin account:
```bash
docker compose exec eaves-droid php spark migrate --all
docker compose exec eaves-droid php spark db:seed PlanSeeder
docker compose exec eaves-droid php spark db:seed SuperAdminSeeder
```

### Step 4: Verify Application Access
* **Web Portal:** Open `http://localhost:9007` in your browser.
* **Health Check:** Confirm `http://localhost:9007/api/health` returns HTTP 200.
* **Default Superadmin:** Check seeded console logs or run `php spark db:seed SuperAdminSeeder` for default credentials.

### Step 5: Stopping the Service
```bash
docker compose down
```

---

## 3. Running Natively (Without Docker)

1. Install Composer dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
2. Configure `.env`: Set database credentials (`database.default.hostname`, `database.default.database`, etc.).
3. Generate encryption key:
   ```bash
   php spark key:generate
   ```
4. Run migrations and seeds:
   ```bash
   php spark migrate --all
   php spark db:seed PlanSeeder
   php spark db:seed SuperAdminSeeder
   ```
5. Serve the application:
   ```bash
   php spark serve --host 0.0.0.0 --port 9007
   ```
