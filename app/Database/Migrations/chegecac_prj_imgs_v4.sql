-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 13, 2026 at 09:47 PM
-- Server version: 8.0.39
-- PHP Version: 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chegecac_prj_imgs_v4`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_groups_users`
--

CREATE TABLE `auth_groups_users` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `group` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `auth_identities`
--

CREATE TABLE `auth_identities` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `secret` varchar(255) NOT NULL,
  `secret2` varchar(255) DEFAULT NULL,
  `expires` datetime DEFAULT NULL,
  `extra` text,
  `force_reset` tinyint(1) NOT NULL DEFAULT '0',
  `last_used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `auth_logins`
--

CREATE TABLE `auth_logins` (
  `id` int UNSIGNED NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `id_type` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `date` datetime NOT NULL,
  `success` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `auth_permissions_users`
--

CREATE TABLE `auth_permissions_users` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `permission` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `auth_remember_tokens`
--

CREATE TABLE `auth_remember_tokens` (
  `id` int UNSIGNED NOT NULL,
  `selector` varchar(255) NOT NULL,
  `hashedValidator` varchar(255) NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `auth_token_logins`
--

CREATE TABLE `auth_token_logins` (
  `id` int UNSIGNED NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `id_type` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `date` datetime NOT NULL,
  `success` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int NOT NULL,
  `batch` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `class` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text,
  `type` varchar(31) NOT NULL DEFAULT 'string',
  `context` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_apps`
--

CREATE TABLE `tbl_apps` (
  `counter` int UNSIGNED NOT NULL,
  `package_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `app_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version_code` int DEFAULT NULL,
  `first_install_time` bigint UNSIGNED DEFAULT NULL,
  `last_update_time` bigint UNSIGNED DEFAULT NULL,
  `is_system_app` tinyint(1) DEFAULT '0',
  `target_sdk` int DEFAULT NULL,
  `min_sdk` int DEFAULT NULL,
  `app_size` bigint UNSIGNED DEFAULT '0',
  `permissions` json DEFAULT NULL,
  `permission_count` int UNSIGNED DEFAULT '0',
  `owner_id` int UNSIGNED NOT NULL,
  `device_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_id` int UNSIGNED DEFAULT NULL,
  `device_model` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `android_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_Owner` int UNSIGNED NOT NULL,
  `meta_Print` int UNSIGNED NOT NULL,
  `extracted_at` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_call_logs`
--

CREATE TABLE `tbl_call_logs` (
  `counter` int UNSIGNED NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(50) NOT NULL,
  `call_type` varchar(20) NOT NULL,
  `call_date` datetime NOT NULL,
  `duration_seconds` int NOT NULL DEFAULT '0',
  `formatted_duration` varchar(20) DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `extracted_at` bigint UNSIGNED DEFAULT NULL,
  `meta_Owner` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_contacts`
--

CREATE TABLE `tbl_contacts` (
  `counter` int UNSIGNED NOT NULL,
  `contact_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Android ContactsContract.Contacts._ID',
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Primary display name',
  `phone_numbers` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of phone numbers',
  `phone_count` int NOT NULL DEFAULT '0',
  `emails` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of email addresses',
  `email_count` int NOT NULL DEFAULT '0',
  `photo_uri` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contact photo content URI',
  `companies` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of company names',
  `addresses` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of formatted address strings',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_favorite` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = starred/favorite',
  `last_contacted` bigint UNSIGNED DEFAULT NULL COMMENT 'Timestamp (ms) of last interaction',
  `contact_frequency` int NOT NULL DEFAULT '0' COMMENT 'Times contacted',
  `device_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extracted_at` bigint UNSIGNED DEFAULT NULL COMMENT 'Extraction timestamp from Android (ms)',
  `meta_Owner` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'User ID owning this data',
  `is_synced` tinyint(1) NOT NULL DEFAULT '0',
  `sync_count` int NOT NULL DEFAULT '0',
  `last_sync` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Contactus`
--

CREATE TABLE `tbl_Contactus` (
  `ID` int NOT NULL,
  `Name` varchar(200) NOT NULL,
  `Email` varchar(200) NOT NULL,
  `Subject` varchar(500) NOT NULL,
  `Message` varchar(2000) NOT NULL,
  `Timestamp` varchar(20) NOT NULL,
  `IP` varchar(20) NOT NULL,
  `Viewed` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_device_profile`
--

CREATE TABLE `tbl_device_profile` (
  `device_checksum` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `android_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_manufacturer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_product` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_device` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_board` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_hardware` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `android_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `android_sdk_int` int DEFAULT NULL,
  `android_security_patch` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `build_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `build_fingerprint` text COLLATE utf8mb4_unicode_ci,
  `memory_total_mb` int DEFAULT NULL,
  `internal_storage_total_gb` int DEFAULT NULL,
  `external_storage_total_gb` int DEFAULT NULL,
  `app_package` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extraction_timestamp` bigint DEFAULT NULL,
  `extractor_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Interactions`
--

CREATE TABLE `tbl_Interactions` (
  `Interaction` int NOT NULL,
  `User_ID` varchar(50) NOT NULL,
  `Action` varchar(250) NOT NULL,
  `IP` varchar(250) NOT NULL,
  `Timestamps` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Notification`
--

CREATE TABLE `tbl_Notification` (
  `Count` int NOT NULL,
  `Timestamps` varchar(20) NOT NULL,
  `Person_Id` varchar(70) NOT NULL,
  `Message` varchar(300) NOT NULL,
  `Message_Type` varchar(20) NOT NULL,
  `Status` varchar(10) NOT NULL DEFAULT '00',
  `Accessed` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Parser_Finance`
--

CREATE TABLE `tbl_Parser_Finance` (
  `parser_Id` int NOT NULL,
  `transaction_Id` varchar(32) NOT NULL,
  `transaction_Amount` varchar(32) NOT NULL,
  `transaction_Recipients` varchar(32) NOT NULL,
  `transaction_Date` varchar(32) NOT NULL,
  `transaction_Source` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Points_Finance`
--

CREATE TABLE `tbl_Points_Finance` (
  `point_Id` int NOT NULL,
  `point_Owner` varchar(32) NOT NULL,
  `point_Name` varchar(128) NOT NULL,
  `point_Inserted` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sms`
--

CREATE TABLE `tbl_sms` (
  `counter` int UNSIGNED NOT NULL,
  `android_sms_id` bigint NOT NULL COMMENT 'Maps to sms_id',
  `thread_id` bigint NOT NULL,
  `address` varchar(50) NOT NULL,
  `formatted_address` varchar(100) DEFAULT NULL,
  `body_encoded` longtext NOT NULL COMMENT 'Base64 encoded SMS body',
  `body_length` int DEFAULT '0',
  `sms_date` bigint UNSIGNED NOT NULL COMMENT 'Device timestamp (ms)',
  `sms_date_sent` bigint UNSIGNED DEFAULT NULL,
  `sms_type` varchar(20) NOT NULL COMMENT 'inbox, sent, etc.',
  `type_code` int NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `is_seen` tinyint(1) DEFAULT '0',
  `status_code` int DEFAULT '0',
  `error_code` int DEFAULT '0',
  `protocol` int DEFAULT '0',
  `protocol_type` varchar(20) DEFAULT 'SMS',
  `service_center` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `is_locked` tinyint(1) DEFAULT '0',
  `creator` varchar(255) DEFAULT NULL,
  `device_id` varchar(100) NOT NULL,
  `meta_owner` varchar(100) NOT NULL,
  `extracted_at` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tokens`
--

CREATE TABLE `tbl_tokens` (
  `counter` int UNSIGNED NOT NULL,
  `token` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_type` enum('pin','qr') COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `initiator` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL,
  `device_checksum` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `android_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_refreshable` tinyint(1) NOT NULL DEFAULT '0',
  `scopes` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Tokentest`
--

CREATE TABLE `tbl_Tokentest` (
  `ID` int NOT NULL,
  `token_submitted` varchar(100) NOT NULL,
  `token_senttime` varchar(20) NOT NULL,
  `token_received` varchar(20) NOT NULL,
  `token_ip` varchar(20) NOT NULL,
  `token_format` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_actions`
--

CREATE TABLE `tbl_user_actions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `session_id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_category` enum('authentication','file','profile','admin','system','security') COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_severity` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'low',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `device_type` enum('desktop','mobile','tablet','bot','unknown') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operating_system` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_code` smallint DEFAULT NULL,
  `execution_time_ms` int UNSIGNED DEFAULT NULL,
  `resource_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g., file_id, post_id, etc.',
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT '1',
  `error_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_date` date GENERATED ALWAYS AS (cast(`created_at` as date)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

CREATE TABLE `uploaded_files` (
  `file_id` int UNSIGNED NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size_bytes` bigint UNSIGNED NOT NULL,
  `file_extension` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'apps, sms, contacts, logs, etc.',
  `token_used` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_owner_id` int UNSIGNED DEFAULT NULL,
  `device_checksum` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_print_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `android_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upload_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `upload_status` enum('pending','processing','parsed','failed','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `upload_error` text COLLATE utf8mb4_unicode_ci,
  `parsed_at` timestamp NULL DEFAULT NULL,
  `parsed_records` int UNSIGNED DEFAULT '0',
  `parse_duration_ms` int UNSIGNED DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `status_message` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `last_active` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `user_id` int UNSIGNED NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text,
  `last_seen_at` datetime DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `last_user_agent` varchar(255) DEFAULT NULL,
  `unread_notifications` int DEFAULT '0',
  `last_notification_at` datetime DEFAULT NULL,
  `notifications_enabled` tinyint(1) DEFAULT '1',
  `email_notifications` tinyint(1) DEFAULT '1',
  `push_notifications` tinyint(1) DEFAULT '1',
  `language` varchar(8) DEFAULT 'en',
  `timezone` varchar(64) DEFAULT NULL,
  `theme` varchar(16) DEFAULT 'system',
  `account_status` varchar(16) DEFAULT 'active',
  `suspended_reason` varchar(255) DEFAULT NULL,
  `last_deleted_data_at` datetime DEFAULT NULL,
  `last_exported_at` datetime DEFAULT NULL,
  `export_count` int DEFAULT '0',
  `onboarding_completed` tinyint(1) DEFAULT '0',
  `profile_completed` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_groups_users`
--
ALTER TABLE `auth_groups_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auth_groups_users_user_id_foreign` (`user_id`);

--
-- Indexes for table `auth_identities`
--
ALTER TABLE `auth_identities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_secret` (`type`,`secret`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `auth_logins`
--
ALTER TABLE `auth_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_type_identifier` (`id_type`,`identifier`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `auth_permissions_users`
--
ALTER TABLE `auth_permissions_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auth_permissions_users_user_id_foreign` (`user_id`);

--
-- Indexes for table `auth_remember_tokens`
--
ALTER TABLE `auth_remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `selector` (`selector`),
  ADD KEY `auth_remember_tokens_user_id_foreign` (`user_id`);

--
-- Indexes for table `auth_token_logins`
--
ALTER TABLE `auth_token_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_type_identifier` (`id_type`,`identifier`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_apps`
--
ALTER TABLE `tbl_apps`
  ADD PRIMARY KEY (`counter`),
  ADD UNIQUE KEY `unique_app_owner` (`package_name`,`meta_Owner`),
  ADD KEY `idx_package_name` (`package_name`),
  ADD KEY `idx_app_name` (`app_name`),
  ADD KEY `idx_owner_id` (`owner_id`),
  ADD KEY `idx_is_system_app` (`is_system_app`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_owner_package` (`owner_id`,`package_name`),
  ADD KEY `idx_owner_system` (`owner_id`,`is_system_app`),
  ADD KEY `idx_file_id` (`file_id`),
  ADD KEY `idx_meta_owner` (`meta_Owner`),
  ADD KEY `idx_meta_print` (`meta_Print`),
  ADD KEY `idx_meta_owner_print` (`meta_Owner`,`meta_Print`),
  ADD KEY `idx_meta_owner_package` (`meta_Owner`,`package_name`);

--
-- Indexes for table `tbl_call_logs`
--
ALTER TABLE `tbl_call_logs`
  ADD PRIMARY KEY (`counter`),
  ADD UNIQUE KEY `unique_log` (`phone_number`,`call_date`,`duration_seconds`,`meta_Owner`);

--
-- Indexes for table `tbl_contacts`
--
ALTER TABLE `tbl_contacts`
  ADD PRIMARY KEY (`counter`),
  ADD UNIQUE KEY `unique_contact_per_device` (`contact_id`,`device_id`,`meta_Owner`),
  ADD KEY `idx_display_name` (`display_name`),
  ADD KEY `idx_contact_id` (`contact_id`),
  ADD KEY `idx_device_id` (`device_id`),
  ADD KEY `idx_meta_owner` (`meta_Owner`),
  ADD KEY `idx_is_favorite` (`is_favorite`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_owner_device` (`meta_Owner`,`device_id`),
  ADD KEY `idx_owner_favorite` (`meta_Owner`,`is_favorite`),
  ADD KEY `idx_owner_name` (`meta_Owner`,`display_name`),
  ADD KEY `idx_owner_freq` (`meta_Owner`,`contact_frequency`);
ALTER TABLE `tbl_contacts` ADD FULLTEXT KEY `ft_contacts_search` (`display_name`,`notes`);

--
-- Indexes for table `tbl_Contactus`
--
ALTER TABLE `tbl_Contactus`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_device_profile`
--
ALTER TABLE `tbl_device_profile`
  ADD PRIMARY KEY (`device_checksum`),
  ADD UNIQUE KEY `idx_unique_android_id` (`android_id`);

--
-- Indexes for table `tbl_Interactions`
--
ALTER TABLE `tbl_Interactions`
  ADD PRIMARY KEY (`Interaction`);

--
-- Indexes for table `tbl_Parser_Finance`
--
ALTER TABLE `tbl_Parser_Finance`
  ADD UNIQUE KEY `parser_Id` (`parser_Id`);

--
-- Indexes for table `tbl_Points_Finance`
--
ALTER TABLE `tbl_Points_Finance`
  ADD UNIQUE KEY `point_Id` (`point_Id`);

--
-- Indexes for table `tbl_sms`
--
ALTER TABLE `tbl_sms`
  ADD PRIMARY KEY (`counter`),
  ADD UNIQUE KEY `unique_sms` (`android_sms_id`,`device_id`,`meta_owner`),
  ADD KEY `idx_address` (`address`),
  ADD KEY `idx_type` (`sms_type`),
  ADD KEY `idx_date` (`sms_date`);
ALTER TABLE `tbl_sms` ADD FULLTEXT KEY `ft_sms_body` (`body_encoded`);

--
-- Indexes for table `tbl_tokens`
--
ALTER TABLE `tbl_tokens`
  ADD PRIMARY KEY (`counter`),
  ADD UNIQUE KEY `uniq_token` (`token`);

--
-- Indexes for table `tbl_Tokentest`
--
ALTER TABLE `tbl_Tokentest`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_user_actions`
--
ALTER TABLE `tbl_user_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_composite` (`user_id`,`created_at`),
  ADD KEY `idx_action_category` (`action_category`),
  ADD KEY `idx_severity` (`action_severity`),
  ADD KEY `idx_resource` (`resource_id`),
  ADD KEY `idx_created_date` (`created_date`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_success` (`success`),
  ADD KEY `idx_created_at` (`created_at` DESC),
  ADD KEY `idx_user_category` (`user_id`,`action_category`,`created_at` DESC),
  ADD KEY `idx_category_type` (`action_category`,`action_type`,`created_at` DESC);

--
-- Indexes for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `idx_token` (`token_used`),
  ADD KEY `idx_owner` (`token_owner_id`),
  ADD KEY `idx_device` (`device_checksum`),
  ADD KEY `idx_category` (`file_category`),
  ADD KEY `idx_status` (`upload_status`),
  ADD KEY `idx_upload_time` (`uploaded_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth_groups_users`
--
ALTER TABLE `auth_groups_users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_identities`
--
ALTER TABLE `auth_identities`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_logins`
--
ALTER TABLE `auth_logins`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_permissions_users`
--
ALTER TABLE `auth_permissions_users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_remember_tokens`
--
ALTER TABLE `auth_remember_tokens`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_token_logins`
--
ALTER TABLE `auth_token_logins`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_apps`
--
ALTER TABLE `tbl_apps`
  MODIFY `counter` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_call_logs`
--
ALTER TABLE `tbl_call_logs`
  MODIFY `counter` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_contacts`
--
ALTER TABLE `tbl_contacts`
  MODIFY `counter` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_Interactions`
--
ALTER TABLE `tbl_Interactions`
  MODIFY `Interaction` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_Parser_Finance`
--
ALTER TABLE `tbl_Parser_Finance`
  MODIFY `parser_Id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_Points_Finance`
--
ALTER TABLE `tbl_Points_Finance`
  MODIFY `point_Id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_sms`
--
ALTER TABLE `tbl_sms`
  MODIFY `counter` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tokens`
--
ALTER TABLE `tbl_tokens`
  MODIFY `counter` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_Tokentest`
--
ALTER TABLE `tbl_Tokentest`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_actions`
--
ALTER TABLE `tbl_user_actions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  MODIFY `file_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auth_groups_users`
--
ALTER TABLE `auth_groups_users`
  ADD CONSTRAINT `auth_groups_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auth_identities`
--
ALTER TABLE `auth_identities`
  ADD CONSTRAINT `auth_identities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auth_permissions_users`
--
ALTER TABLE `auth_permissions_users`
  ADD CONSTRAINT `auth_permissions_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auth_remember_tokens`
--
ALTER TABLE `auth_remember_tokens`
  ADD CONSTRAINT `auth_remember_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `fk_user_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
