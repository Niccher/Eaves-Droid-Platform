# Engineering: Local Development Setup

This guide assists software engineers in configuring a native development workstation for working on the CodeIgniter 4 application.

---

## 1. Workstation Prerequisites

* **PHP 8.3+** with CLI, cURL, MySQLnd, Mbstring, Intl, and XML modules:
  ```bash
  sudo apt-get install php8.3-cli php8.3-curl php8.3-mysql php8.3-mbstring php8.3-intl php8.3-xml
  ```
* **Composer 2.7+**
* **MySQL 8.4** server (can be run standalone via Docker or installed locally)

---

## 2. Setting Up the Codebase

1. Clone and install dependencies:
   ```bash
   cd path/to/Eaves-Droid-WebApp
   composer install
   ```

2. Copy development configuration:
   ```bash
   cp env .env
   ```

3. Update `.env` for local dev:
   ```ini
   CI_ENVIRONMENT = development
   app.baseURL = 'http://localhost:8080/'
   database.default.hostname = 127.0.0.1
   database.default.database = db_eaves_droid
   database.default.username = root
   database.default.password = root_password
   ```

4. Generate the application encryption key:
   ```bash
   php spark key:generate
   ```

5. Migrate and seed the local database:
   ```bash
   php spark migrate --all
   php spark db:seed PlanSeeder
   php spark db:seed SuperAdminSeeder
   ```

6. Start the built-in development server:
   ```bash
   php spark serve --port 8080
   ```
