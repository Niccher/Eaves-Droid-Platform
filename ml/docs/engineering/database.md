# Engineering: Database Integration

Details on the MySQL schema tables owned and managed by ML Eaves Droid.

---

## 1. Schema Tables

Managed authoritatively by CodeIgniter 4 migrations in `web/app/Database/Migrations/` (`20240807080026_create_ml_jobs_table.php`, `20240807080027_create_ml_results_table.php`, and `20240807080028_create_ml_analysis_tracking_table.php`):

```sql
CREATE TABLE IF NOT EXISTS `ml_jobs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `status` ENUM('pending', 'running', 'completed', 'failed') NOT NULL DEFAULT 'pending',
  `scope` VARCHAR(32) DEFAULT 'full',
  `algorithms` JSON NOT NULL,
  `timing_ms` FLOAT DEFAULT 0.0,
  `error` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_ml_jobs_user_status` (`user_id`, `status`)
);

CREATE TABLE IF NOT EXISTS `ml_results` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `algorithm_id` VARCHAR(64) NOT NULL,
  `category` VARCHAR(32) NOT NULL,
  `target_id` INT,
  `score` FLOAT NOT NULL,
  `confidence` FLOAT NOT NULL,
  `details` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ml_results_job` (`job_id`),
  INDEX `idx_ml_results_user_algo` (`user_id`, `algorithm_id`)
);

CREATE TABLE IF NOT EXISTS `ml_analysis_tracking` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `category` VARCHAR(32) NOT NULL,
  `last_analyzed_id` INT DEFAULT 0,
  `last_analyzed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE INDEX `idx_tracking_user_cat` (`user_id`, `category`)
);
```

---

## 2. SQLAlchemy ORM Models

Models are defined in `app/utils/db.py` using standard declarative mappings and connection pooling.
