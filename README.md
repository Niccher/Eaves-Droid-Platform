# Prj Images - Mobile Data Intelligence Platform

Prj Images is a powerful web-based platform built on **CodeIgniter 4** designed to analyze, visualize, and manage mobile data (SMS, Calls, Contacts, Apps, and Locations) extracted from Android devices.

## 🚀 Project Overview

The system acts as a central hub for data visualization and device management. It features an "Intelligence Dashboard" that uses statistical analysis to categorize communication patterns (Financial, Security, Spam, etc.) and provides remote command capabilities via Firebase.

### Key Features
- **Intelligence Dashboard**: Automated categorization of SMS and Call logs using keyword-based analysis.
- **Remote Commands**: Send real-time extraction requests to connected Android devices.
- **Secure Profiles**: Encrypted user bios and secure multi-factor ready authentication.
- **Data Export**: Comprehensive data export in CSV/ZIP formats.
- **Device Management**: Track connected devices, session history, and activity logs.

## 🛠 Technology Stack

- **Backend**: PHP 8.1+ (CodeIgniter 4 framework)
- **Authentication**: CodeIgniter Shield
- **Frontend**: AdminLTE 3 (Bootstrap 4), DataTables, Chart.js
- **Database**: MySQL/MariaDB
- **Security**: AES-256 data encryption for sensitive fields

## 🏗 Internal Logic & Architecture

### 1. Data Models
- **`Mod_Finder`**: The core data engine. It handles complex SQL queries for data counts and implements the **categorization logic** for SMS (Financial, OTP, Malicious) and Calls (Family, Business, Spam).
- **`Mod_User`**: Manages the link between Shield authentication and custom `user_profiles`. It also handles API token generation and device hardware mapping.
- **`Mod_Crypt`**: A dedicated security layer that uses a server-side secret key to encrypt and decrypt sensitive user information like bios.

### 2. Remote Command Flow
The system utilizes **Firebase Cloud Messaging (FCM)**. When a user sends a command (e.g., "Extract SMS") from the Web Dashboard:
1. The controller identifies the target device's latest FCM token.
2. A data message is sent via `FirebaseLib`.
3. The Android app receives the "action" and begins the background extraction process.

### 3. Data Intelligence
The `Mod_Finder` uses an array-based keyword matching system (Heuristics) to categorize data:
- **Financial**: Detects bank names and transaction keywords (KCB, M-Pesa, etc).
- **Security**: Flags OTPs and verification codes.
- **Threats**: Identifies common scam patterns (lottery wins, prize alerts).

## ⚙️ Setup Instructions

### Prerequisites
- Apache/Nginx (PHP 8.1 support)
- MySQL/MariaDB
- Composer (for dependency management)

### Installation Steps
1. **Clone & Extract**: Place the project files in your web root (e.g., `/opt/lampp/htdocs/`).
2. **Database Configuration**:
   - Create a database in MySQL.
   - Update `app/Config/Database.php` with your credentials.
   - Run migrations: `php spark migrate`.
3. **Environment Setup**:
   - Rename `env` to `.env`.
   - Set `CI_ENVIRONMENT = production` (or `development` for debugging).
   - Set your `app.baseURL`.
4. **Permissions**:
   - Ensure `writable/` and `public/uploads/` are writable by the web server:
     ```bash
     chmod -R 777 writable/
     chmod -R 777 public/uploads/
     ```
5. **Security Key**: Set your encryption key in `app/Config/Encryption.php`.

## 📁 Directory Structure

- `app/Controllers/clients`: Authenticated user controllers.
- `app/Models`: Data models and intelligence logic.
- `app/Views/users`: Dashboard and data viewing screens.
- `public/uploads/profiles`: User avatars.
- `writable/`: Logs, cache, and session data.

---
*Developed as part of the Prj Images Ecosystem.*
