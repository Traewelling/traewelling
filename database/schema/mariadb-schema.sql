/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_suggestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_suggestions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `host` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `station_id` bigint(20) unsigned DEFAULT NULL,
  `begin` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  `hashtag` varchar(255) DEFAULT NULL,
  `admin_notification_id` bigint(20) unsigned DEFAULT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_suggestions_user_id_foreign` (`user_id`),
  KEY `event_suggestions_train_station_id_foreign` (`station_id`),
  CONSTRAINT `event_suggestions_train_station_id_foreign` FOREIGN KEY (`station_id`) REFERENCES `train_stations` (`id`),
  CONSTRAINT `event_suggestions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `hashtag` varchar(255) DEFAULT NULL,
  `host` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `station_id` bigint(20) unsigned DEFAULT NULL,
  `checkin_start` datetime NOT NULL,
  `checkin_end` datetime NOT NULL,
  `event_start` datetime DEFAULT NULL COMMENT 'If different from checkin_start',
  `event_end` datetime DEFAULT NULL COMMENT 'If different from checkin_end',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `events_slug_unique` (`slug`),
  KEY `events_station_id_foreign` (`station_id`),
  KEY `events_approved_by_foreign` (`approved_by`),
  CONSTRAINT `events_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `events_station_id_foreign` FOREIGN KEY (`station_id`) REFERENCES `train_stations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `follow_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `follow_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `follow_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `follow_requests_user_id_follow_id_unique` (`user_id`,`follow_id`),
  KEY `follow_requests_follow_id_foreign` (`follow_id`),
  CONSTRAINT `follow_requests_follow_id_foreign` FOREIGN KEY (`follow_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `follow_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `follows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `follows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `follow_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `follows_user_id_foreign` (`user_id`),
  KEY `follows_follow_id_foreign` (`follow_id`),
  CONSTRAINT `follows_follow_id_foreign` FOREIGN KEY (`follow_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `follows_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hafas_operators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hafas_operators` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hafas_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hafas_operators_hafas_id_unique` (`hafas_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hafas_trips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hafas_trips` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trip_id` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL,
  `linename` varchar(255) NOT NULL,
  `journey_number` int(10) unsigned DEFAULT NULL,
  `operator_id` bigint(20) unsigned DEFAULT NULL,
  `origin_id` bigint(20) unsigned NOT NULL,
  `destination_id` bigint(20) unsigned NOT NULL,
  `polyline_id` bigint(20) unsigned DEFAULT NULL,
  `departure` timestamp NULL DEFAULT NULL,
  `arrival` timestamp NULL DEFAULT NULL,
  `source` varchar(255) NOT NULL DEFAULT 'hafas',
  `motis_source` varchar(255) DEFAULT NULL,
  `motis_source_license_id` uuid DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'if not null, this trip belongs to the user (e.g. manually created trips)',
  `last_refreshed` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hafas_trips_trip_id_unique` (`trip_id`),
  KEY `hafas_trips_polyline_id_foreign` (`polyline_id`),
  KEY `hafas_trips_created_at_trip_id_index` (`created_at`,`trip_id`),
  KEY `hafas_trips_user_id_foreign` (`user_id`),
  KEY `hafas_trips_origin_id_foreign` (`origin_id`),
  KEY `hafas_trips_destination_id_foreign` (`destination_id`),
  KEY `hafas_trips_operator_id_category_index` (`operator_id`,`category`),
  KEY `motis_source_license_id_foreign` (`motis_source_license_id`),
  CONSTRAINT `hafas_trips_destination_id_foreign` FOREIGN KEY (`destination_id`) REFERENCES `train_stations` (`id`),
  CONSTRAINT `hafas_trips_operator_id_foreign` FOREIGN KEY (`operator_id`) REFERENCES `hafas_operators` (`id`),
  CONSTRAINT `hafas_trips_origin_id_foreign` FOREIGN KEY (`origin_id`) REFERENCES `train_stations` (`id`),
  CONSTRAINT `hafas_trips_polyline_id_foreign` FOREIGN KEY (`polyline_id`) REFERENCES `poly_lines` (`id`),
  CONSTRAINT `hafas_trips_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `motis_source_license_id_foreign` FOREIGN KEY (`motis_source_license_id`) REFERENCES `motis_source_licenses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ics_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ics_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `token` char(36) NOT NULL,
  `last_accessed` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ics_tokens_token_unique` (`token`),
  KEY `ics_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `ics_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `likes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `status_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `likes_user_id_status_id_unique` (`user_id`,`status_id`),
  KEY `likes_status_id_foreign` (`status_id`),
  CONSTRAINT `likes_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mastodon_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mastodon_servers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `client_secret` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mastodon_servers_domain_unique` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mentions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mentions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `status_id` bigint(20) unsigned NOT NULL,
  `mentioned_id` bigint(20) unsigned NOT NULL,
  `position` smallint(5) unsigned NOT NULL,
  `length` smallint(5) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mentions_status_id_mentioned_id_position_unique` (`status_id`,`mentioned_id`,`position`),
  KEY `mentions_mentioned_id_foreign` (`mentioned_id`),
  CONSTRAINT `mentions_mentioned_id_foreign` FOREIGN KEY (`mentioned_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentions_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `motis_source_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `motis_source_licenses` (
  `id` uuid NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `human_name` varchar(255) DEFAULT NULL,
  `license` varchar(255) DEFAULT NULL,
  `license_url` varchar(255) DEFAULT NULL,
  `source_url` varchar(255) DEFAULT NULL,
  `spdx` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `motis_sources_provider_country_name_index` (`provider`,`country`,`name`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `scopes` text DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_auth_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `scopes` text DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_auth_codes_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `secret` varchar(100) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `redirect` text NOT NULL,
  `webhooks_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `privacy_policy_url` varchar(255) DEFAULT NULL,
  `authorized_webhook_url` varchar(255) DEFAULT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_personal_access_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_personal_access_clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) NOT NULL,
  `access_token_id` varchar(100) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `poly_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `poly_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `hash` varchar(255) NOT NULL,
  `polyline` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`polyline`)),
  `source` enum('hafas','brouter') NOT NULL DEFAULT 'hafas',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `poly_lines_hash_unique` (`hash`),
  KEY `poly_lines_hash_index` (`hash`),
  KEY `poly_lines_parent_id_index` (`parent_id`),
  KEY `poly_lines_source_index` (`source`),
  CONSTRAINT `poly_lines_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `poly_lines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `privacy_agreements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `privacy_agreements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `body_md_de` text NOT NULL,
  `body_md_en` text NOT NULL,
  `valid_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `queue_monitor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `queue_monitor` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `job_uuid` char(36) DEFAULT NULL,
  `job_id` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `queue` varchar(255) DEFAULT NULL,
  `status` int(10) unsigned NOT NULL DEFAULT 0,
  `queued_at` datetime DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `started_at_exact` varchar(255) DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `finished_at_exact` varchar(255) DEFAULT NULL,
  `attempt` int(11) NOT NULL DEFAULT 0,
  `retried` tinyint(1) NOT NULL DEFAULT 0,
  `progress` int(11) DEFAULT NULL,
  `exception` longtext DEFAULT NULL,
  `exception_message` text DEFAULT NULL,
  `exception_class` text DEFAULT NULL,
  `data` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `queue_monitor_job_id_index` (`job_id`),
  KEY `queue_monitor_started_at_index` (`started_at`),
  KEY `queue_monitor_name_status_queue_index` (`name`,`status`,`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(255) NOT NULL DEFAULT 'open' COMMENT 'Enum ReportStatus',
  `subject_type` varchar(255) NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `reason` varchar(255) DEFAULT NULL COMMENT 'Enum ReportReason or null.',
  `description` varchar(255) DEFAULT NULL,
  `reporter_id` bigint(20) unsigned DEFAULT NULL,
  `admin_notification_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reports_reporter_id_foreign` (`reporter_id`),
  KEY `reports_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `reports_status_index` (`status`),
  CONSTRAINT `reports_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `social_login_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_login_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `twitter_id` varchar(255) DEFAULT NULL,
  `mastodon_id` varchar(255) DEFAULT NULL,
  `mastodon_server` bigint(20) unsigned DEFAULT NULL,
  `mastodon_token` text DEFAULT NULL,
  `mastodon_visibility` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `social_login_profiles_twitter_id_unique` (`twitter_id`),
  KEY `social_login_profiles_user_id_foreign` (`user_id`),
  KEY `social_login_profiles_mastodon_server_foreign` (`mastodon_server`),
  CONSTRAINT `social_login_profiles_mastodon_server_foreign` FOREIGN KEY (`mastodon_server`) REFERENCES `mastodon_servers` (`id`),
  CONSTRAINT `social_login_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `station_identifiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `station_identifiers` (
  `id` uuid NOT NULL,
  `station_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `identifier` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT 'Name of the station provided by the data source',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `station_identifiers_station_id_foreign` (`station_id`),
  KEY `station_identifiers_type_origin_identifier_index` (`type`,`origin`,`identifier`),
  CONSTRAINT `station_identifiers_station_id_foreign` FOREIGN KEY (`station_id`) REFERENCES `train_stations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `station_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `station_names` (
  `id` char(36) NOT NULL,
  `station_id` bigint(20) unsigned NOT NULL,
  `language` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `station_names_station_id_language_unique` (`station_id`,`language`),
  CONSTRAINT `station_names_station_id_foreign` FOREIGN KEY (`station_id`) REFERENCES `train_stations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `status_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `status_id` bigint(20) unsigned NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `visibility` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_tags_status_id_key_unique` (`status_id`,`key`),
  CONSTRAINT `status_tags_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `statuses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `body` text DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `business` smallint(5) unsigned NOT NULL DEFAULT 0,
  `visibility` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `event_id` bigint(20) unsigned DEFAULT NULL,
  `mastodon_post_id` varchar(255) DEFAULT NULL,
  `client_id` bigint(20) unsigned DEFAULT NULL,
  `moderation_notes` varchar(255) DEFAULT NULL COMMENT 'Notes from the moderation team - visible to the user',
  `lock_visibility` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Prevent the user from changing the visibility of the status?',
  `hide_body` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Hide the body of the status from other users?',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `statuses_event_id_foreign` (`event_id`),
  KEY `statuses_user_id_mastodon_post_id_created_at_index` (`user_id`,`mastodon_post_id`,`created_at`),
  KEY `statuses_client_id_foreign` (`client_id`),
  KEY `statuses_user_id_visibility_index` (`user_id`,`visibility`),
  CONSTRAINT `statuses_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `statuses_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `statuses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `train_checkins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `train_checkins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `status_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'workaround for unique key',
  `trip_id` varchar(255) NOT NULL,
  `origin_stopover_id` bigint(20) unsigned DEFAULT NULL,
  `destination_stopover_id` bigint(20) unsigned DEFAULT NULL,
  `distance` int(10) unsigned DEFAULT NULL COMMENT 'meters',
  `duration` int(10) unsigned DEFAULT NULL COMMENT 'Duration in minutes. Cached value with real time and manual data. Null if not yet calculated.',
  `departure` datetime DEFAULT NULL,
  `manual_departure` timestamp NULL DEFAULT NULL COMMENT 'User-defined override of the departure',
  `arrival` datetime DEFAULT NULL,
  `manual_arrival` timestamp NULL DEFAULT NULL COMMENT 'User-defined override of the arrival',
  `points` int(11) DEFAULT NULL,
  `forced` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `train_checkins_status_id_unique` (`status_id`),
  KEY `train_checkins_trip_id_foreign` (`trip_id`),
  KEY `train_checkins_user_id_arrival_index` (`user_id`,`arrival`),
  KEY `train_checkins_departure_arrival_status_id_index` (`departure`,`arrival`,`status_id`),
  KEY `train_checkins_origin_stopover_id_foreign` (`origin_stopover_id`),
  KEY `train_checkins_destination_stopover_id_foreign` (`destination_stopover_id`),
  CONSTRAINT `train_checkins_destination_stopover_id_foreign` FOREIGN KEY (`destination_stopover_id`) REFERENCES `train_stopovers` (`id`),
  CONSTRAINT `train_checkins_origin_stopover_id_foreign` FOREIGN KEY (`origin_stopover_id`) REFERENCES `train_stopovers` (`id`),
  CONSTRAINT `train_checkins_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `train_checkins_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `hafas_trips` (`trip_id`),
  CONSTRAINT `train_checkins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `train_stations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `train_stations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ibnr` bigint(20) unsigned DEFAULT NULL,
  `wikidata_id` varchar(255) DEFAULT NULL,
  `ifopt_a` varchar(255) DEFAULT NULL COMMENT 'Country',
  `ifopt_b` int(10) unsigned DEFAULT NULL COMMENT 'Administrative Area',
  `ifopt_c` int(10) unsigned DEFAULT NULL COMMENT 'Mode or Stop Place',
  `ifopt_d` int(10) unsigned DEFAULT NULL COMMENT 'Stop Place or Stop Place Component',
  `ifopt_e` int(10) unsigned DEFAULT NULL COMMENT 'Stop Place Component (or unused)',
  `rilIdentifier` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `latitude` decimal(9,6) NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  `source` varchar(255) DEFAULT NULL,
  `time_offset` tinyint(4) DEFAULT NULL COMMENT 'Defines the offset of the train station relative to Europe/Berlin',
  `shift_time` tinyint(1) DEFAULT NULL COMMENT 'If false, the timezone of the hafas request will not be shifted to Europe/Berlin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `train_stations_ibnr_unique` (`ibnr`),
  KEY `train_stations_rilidentifier_index` (`rilIdentifier`),
  KEY `train_stations_wikidata_id_foreign` (`wikidata_id`),
  KEY `ifopt` (`ifopt_a`,`ifopt_b`,`ifopt_c`,`ifopt_d`,`ifopt_e`),
  KEY `train_stations_latitude_longitude_index` (`latitude`,`longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `train_stopovers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `train_stopovers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trip_id` varchar(255) NOT NULL,
  `train_station_id` bigint(20) unsigned NOT NULL,
  `arrival_planned` timestamp NULL DEFAULT NULL,
  `arrival_real` timestamp NULL DEFAULT NULL,
  `arrival_platform_planned` varchar(255) DEFAULT NULL,
  `arrival_platform_real` varchar(255) DEFAULT NULL,
  `departure_planned` timestamp NULL DEFAULT NULL,
  `departure_real` timestamp NULL DEFAULT NULL,
  `departure_platform_planned` varchar(255) DEFAULT NULL,
  `departure_platform_real` varchar(255) DEFAULT NULL,
  `cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `train_stopovers_trip_id_train_station_id_unique` (`trip_id`,`train_station_id`,`arrival_planned`,`departure_planned`),
  UNIQUE KEY `stopovers_station_arrival` (`trip_id`,`train_station_id`,`arrival_planned`),
  UNIQUE KEY `stopovers_station_departure` (`trip_id`,`train_station_id`,`departure_planned`),
  KEY `train_stopovers_train_station_id_foreign` (`train_station_id`),
  KEY `train_stopovers_arrival_planned_arrival_real_index` (`arrival_planned`,`arrival_real`),
  KEY `train_stopovers_departure_planned_index` (`departure_planned`),
  KEY `train_stopovers_departure_real_index` (`departure_real`),
  KEY `train_stopovers_arrival_planned_index` (`arrival_planned`),
  KEY `train_stopovers_arrival_real_index` (`arrival_real`),
  KEY `index_trip_id_arrival_departure` (`trip_id`,`arrival_planned`,`departure_planned`),
  CONSTRAINT `train_stopovers_train_station_id_foreign` FOREIGN KEY (`train_station_id`) REFERENCES `train_stations` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `train_stopovers_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `hafas_trips` (`trip_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trusted_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `trusted_users` (
  `id` char(36) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `trusted_id` bigint(20) unsigned NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trusted_users_user_id_trusted_id_unique` (`user_id`,`trusted_id`),
  KEY `trusted_users_trusted_id_foreign` (`trusted_id`),
  CONSTRAINT `trusted_users_trusted_id_foreign` FOREIGN KEY (`trusted_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trusted_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='This table is used to store trusted users for friend checkin.';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_blocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `blocked_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_blocks_user_id_blocked_id_unique` (`user_id`,`blocked_id`),
  KEY `user_blocks_blocked_id_foreign` (`blocked_id`),
  CONSTRAINT `user_blocks_blocked_id_foreign` FOREIGN KEY (`blocked_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_blocks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_mutes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_mutes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `muted_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_mutes_user_id_muted_id_unique` (`user_id`,`muted_id`),
  KEY `user_mutes_muted_id_foreign` (`muted_id`),
  CONSTRAINT `user_mutes_muted_id_foreign` FOREIGN KEY (`muted_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_mutes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `username` varchar(25) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `privacy_ack_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `home_id` bigint(20) unsigned DEFAULT NULL,
  `private_profile` tinyint(1) NOT NULL DEFAULT 0,
  `default_status_visibility` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `prevent_index` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'prevent search engines from indexing this profile',
  `privacy_hide_days` int(10) unsigned DEFAULT NULL COMMENT 'Set statuses private after x days',
  `language` varchar(12) DEFAULT NULL,
  `timezone` varchar(255) NOT NULL DEFAULT 'Europe/Berlin',
  `friend_checkin` varchar(255) NOT NULL DEFAULT 'forbidden',
  `likes_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `points_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `mapprovider` enum('cargo','open-railway-map') NOT NULL DEFAULT 'cargo',
  `data_provider` varchar(255) NOT NULL DEFAULT 'default',
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `recent_gdpr_export` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_home_id_foreign` (`home_id`),
  CONSTRAINT `users_home_id_foreign` FOREIGN KEY (`home_id`) REFERENCES `train_stations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `webhook_creation_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhook_creation_requests` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `oauth_client_id` bigint(20) unsigned NOT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `events` varchar(255) NOT NULL,
  `url` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `webhook_creation_requests_user_id_foreign` (`user_id`),
  KEY `webhook_creation_requests_oauth_client_id_foreign` (`oauth_client_id`),
  CONSTRAINT `webhook_creation_requests_oauth_client_id_foreign` FOREIGN KEY (`oauth_client_id`) REFERENCES `oauth_clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `webhook_creation_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `webhook_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhook_events` (
  `webhook_id` bigint(20) unsigned NOT NULL,
  `event` char(32) NOT NULL,
  PRIMARY KEY (`webhook_id`,`event`),
  CONSTRAINT `webhook_events_webhook_id_foreign` FOREIGN KEY (`webhook_id`) REFERENCES `webhooks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhooks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `oauth_client_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `url` text NOT NULL,
  `secret` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `webhooks_oauth_client_id_foreign` (`oauth_client_id`),
  KEY `webhooks_user_id_foreign` (`user_id`),
  CONSTRAINT `webhooks_oauth_client_id_foreign` FOREIGN KEY (`oauth_client_id`) REFERENCES `oauth_clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `webhooks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

/*M!999999\- enable the sandbox mode */ 
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2016_06_01_000001_create_oauth_auth_codes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2016_06_01_000002_create_oauth_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2016_06_01_000003_create_oauth_refresh_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2016_06_01_000004_create_oauth_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2016_06_01_000005_create_oauth_personal_access_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2018_02_05_000000_create_queue_monitor_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2019_08_06_184725_create_social_login_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2019_08_09_003647_create_statuses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2019_08_09_003814_create_likes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2019_08_09_003837_create_follows_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2019_08_11_233556_create_mastodon_servers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2019_08_13_155546_create_hafas_trips_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2019_08_13_162744_create_train_stations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2019_08_14_013806_create_train_checkins_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2019_08_19_173342_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2019_11_04_200120_create_privacy_agreements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2019_11_22_220440_create_poly_lines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2019_12_14_130742_create_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2019_12_18_222050_connect_statuses_and_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2019_12_20_221724_create_user_roles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2020_02_20_162218_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2020_08_05_151702_change_train_station_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2020_09_27_190346_migrate_laravel_passport_to_v9',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2020_09_28_150313_migrate_notifications_to_laravel8',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2020_09_28_170000_add_foreign_keys_to_social_login_profiles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2020_09_28_170001_unify_columns_from_train_stations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2020_09_28_170002_add_foreign_keys_to_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2020_09_28_170003_add_foreign_keys_to_follows',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2020_09_28_170004_add_keys_to_poly_lines',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2020_09_28_170005_add_foreign_keys_to_hafas_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2020_09_28_170006_add_foreign_keys_to_likes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2020_09_28_170007_add_foreign_keys_to_sessions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2020_09_28_170008_add_foreign_keys_to_statuses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2020_09_28_170009_add_foreign_keys_to_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2020_09_28_170010_add_foreign_keys_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2020_10_05_184500_add_unique_key_to_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2020_10_16_175602_add_unique_key_to_likes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2020_11_18_101929_add_user_private_profile_key',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2021_01_26_203951_create_train_stopovers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2021_02_01_000000_add_unique_key_to_poly_lines',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2021_02_15_000000_create_ics_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2021_02_20_155443_remove_static_points_from_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2021_02_20_213248_add_ril_identifier_to_train_stations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2021_02_23_170640_create_hafas_operators_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2021_02_23_171038_add_hafas_operator_to_hafas_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2021_02_23_185026_change_avatar_to_be_null_on_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2021_04_21_102707_change_statuses_to_accept_business_checkin_as_integer',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2021_04_28_230708_add_last_accessed_and_name_to_ics_tokens',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2021_04_29_150026_create_follow_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2021_05_01_165202_add_privacy_search_engine_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2021_05_10_201216_add_language_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2021_05_12_211411_create_user_mutes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2021_05_26_134022_remove_train_distance_and_duration_from_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2021_05_26_172817_create_event_suggestions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2021_06_07_145349_add_visibility_to_statuses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2021_06_08_200223_add_index_to_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2021_07_03_222039_add_last_login_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2021_07_04_002719_drop_points_calculation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2021_07_24_124933_create_default_visibility_on_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2021_08_05_211128_change_column_for_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2021_08_05_214231_remove_delay_from_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2021_08_05_220132_add_user_id_to_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2021_10_08_132506_make_twitter_id_unique_in_social_login_profiles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2021_10_08_160350_add_foreign_key_to_social_login_profiles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2021_10_12_143849_drop-blogposts-table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2021_11_03_211948_add_unique_key_to_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2021_11_06_193343_make_url_nullable_at_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2021_12_14_143156_add_user_arrival_index_to_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2022_01_06_224610_add_index_to_train_stopovers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2022_01_06_230418_add_index_to_hafas_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2022_03_03_104050_add_cancelled_attribute_to_stopover',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2022_03_07_000000_add_hide_status_days_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2022_03_11_000000_migrate_train_checkin_category',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2022_03_18_000000_migrate_encryptables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2022_03_28_000000_add_support_code_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2022_03_29_000000_add_index_to_train_stopovers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2022_04_01_000000_remove_support_code_from_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2022_04_02_000000_change_unique_key_to_support_multiple_stopovers_per_station',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2022_04_02_000000_create_remarks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2022_04_02_000001_create_trip_remarks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2022_04_03_000000_create_user_agents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2022_04_03_000001_create_api_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2022_04_07_085923_change_status_code_datatype_in_api_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2022_05_22_000000_add_station_id_to_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2022_05_29_000000_create_carriage_sequences_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2022_06_04_000000_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2022_06_04_000001_make_order_number_nullable_at_carriage_sequences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2022_07_21_000000_add_index_to_api_logs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2022_07_21_000001_add_index_to_user_agents',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2022_07_23_000000_add_forced_to_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2022_08_31_000000_add_last_refreshed_to_hafas_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2022_08_31_000001_add_index2_to_train_stopovers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2022_09_08_000000_create_locations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2022_09_25_000000_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2022_09_26_000000_fill_planned_departure_and_arrival_at_train_stopovers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2022_09_26_000001_add_tweet_id_to_statuses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2022_10_11_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2022_10_17_000000_default_uuid_field',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2022_10_22_000000_add_unique_key_to_train_stopovers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2022_11_25_223003_add_toot_id_to_status_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2022_12_11_000000_drop_trip_remarks',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2022_12_11_000001_drop_remarks',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2022_12_23_000000_create_status_tags_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2022_12_25_000000_create_user_blocks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2022_19_10_000000_add_twitter_refresh_token',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2023_01_08_202020_remove_always_dbl_flag_from_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2023_01_21_000000_add_journey_number_to_hafas_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2023_01_28_000000_migrate_passport_from_v7_to_v8',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2023_01_30_173822_add_scopes_to_existing_oauth_access_tokens',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2023_02_04_184426_extend_oauth_clients_with_custom_data',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2023_02_07_202300_add_mastodon_visibility_field_to_social_login_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2023_02_11_000000_remove_api_usage',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2023_02_11_000001_remove_locations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2023_03_01_000000_update_queue_monitor_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2023_03_06_000000_add_override_arrival_and_departure_to_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2023_03_23_000000_add_index_to_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2023_03_23_000000_migrate_oauth_access_tokens',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2023_03_23_000000_migrate_oauth_auth_codes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2023_03_23_000000_migrate_oauth_clients',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2023_03_23_000000_migrate_oauth_personal_access_clients',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2023_03_23_000001_add_index_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2023_03_23_000002_add_index_to_train_stations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2023_03_23_000003_create_webhooks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2023_03_23_000004_create_webhook_creation_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2023_03_23_215105_change_index_at_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2023_04_25_000000_add_source_to_poly_lines',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2023_05_03_144452_add_job_uuid_and_retried_to_queue_monitor_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2023_05_05_000000_drop_carriage_sequences',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2023_05_07_135454_add_likes_enabled_field_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2023_05_09_000000_make_station_id_null_on_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2023_05_10_000000_default_uuid_field',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2023_06_06_000000_delete_all_notifications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2023_06_09_161642_add_mapprovider_field_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2023_06_13_213444_expand_event_date_restrictions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2023_07_06_201721_rename_legacy_stopovers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2023_07_07_183520_add_timezone_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2023_07_08_090008_add_time_information_to_stations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2023_07_09_000000_make_hashtag_nullable_on_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2023_07_13_144452_add_queued_at_column_to_queue_monitor_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2023_07_24_000000_add_user_mastodon_index_to_statuses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2023_07_26_000000_add_duration_to_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2023_08_06_000000_rename_manual_arrival_and_departure_at_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2023_08_06_000001_add_hashtag_to_event_suggestions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2023_08_06_000002_rename_station_id_at_event_suggestions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2023_08_19_160842_add_webhook_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2023_08_19_161454_migrate_webhook_events_from_bitmasks_to_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2023_08_19_174051_drop_webhook_events_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2023_09_22_194607_add_experimental_setting_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2023_09_23_162754_add-parent-to-polylines',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2023_11_20_000000_drop_user_agents',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2023_11_20_000001_drop_locations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2023_11_20_000002_drop_remarks',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2023_11_21_000000_drop_twitter_secrets',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2023_11_21_000001_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2023_11_21_000003_drop_role_from_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2023_11_21_000004_drop_experimental_from_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2023_11_22_000000_add_source_to_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2023_11_23_000000_remove_type_from_statuses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2023_11_23_000001_fix_departure_column_on_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2023_11_23_000002_add_user_id_to_hafas_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2023_11_27_000000_drop_delay_column_in_hafas_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2023_12_17_000000_create_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2023_12_17_000001_add_event_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2023_12_17_000002_add_batch_uuid_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2023_12_18_000000_add_origin_and_destination_stopover_foreign_to_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2023_12_20_000000_migrate_stopover_relation_id_in_train_checkins',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2023_12_29_000000_add_client_id_to_statuses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2024_01_30_000001_create_reports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2024_02_08_000000_create_mentions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2024_03_01_000000_create_wikidata_entities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2024_03_01_000001_add_wikidata_id_to_stations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2024_03_10_211526_add_ifopt_to_stations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2024_03_22_000000_add_origin_and_destination_id_to_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2024_03_22_000001_migrate_origin_and_destination_id_in_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2024_03_22_000002_make_trip_origin_id_and_destination_id_not_nullable',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2024_04_19_131906_add_index_to_poly_line_parent_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2024_05_21_000000_make_ibnr_nullable_on_stations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2024_05_21_000001_remove_station_ibnr_from_hafas_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2024_05_22_000003_drop_origin_destination_from_check_in',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2024_05_22_000004_add_index_operator_id_category_to_hafas_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2024_05_22_000005_add_index_name_status_queue_on_queue_monitor',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2024_05_22_000008_add_index_user_id_visibility_on_statuses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2024_05_25_000000_add_source_index_to_polylines',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2024_05_25_000001_add_trip_id_arrival_departure_index_to_train_stopovers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2024_05_27_000000_drop_shadow_banned_from_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2024_05_30_000000_add_prefix_to_begin_end_on_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (189,'2024_06_28_000000_add_friend_checkin_setting_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (190,'2024_07_07_000000_make_display_name_not_nullable_at_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (191,'2024_07_07_000000_set_size_of_username_at_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (192,'2024_07_14_081815_add_points_setting_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (193,'2024_07_28_000001_add_trusted_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (194,'2024_07_30_000000_add_admin_notification_id_to_reports',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2024_08_07_000000_create_station_names_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (196,'2024_08_07_000001_drop_foreign_wikidata_entity_on_station',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2024_08_07_000002_drop_wikidata_entities',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (198,'2024_08_11_000000_drop_tweet_id_from_statuses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (199,'2024_08_13_000000_add_admin_notification_id_to_event_suggestions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (200,'2024_11_04_223623_add_recent_gdpr_export_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (201,'2025_01_08_000000_add_source_to_stations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (202,'2025_01_08_000001_what_the_fuck_twitter_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (203,'2025_01_16_000000_add_moderation_notes_to_statuses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (204,'2025_02_23_094950_create_station_identifiers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (205,'2025_02_27_213311_add_motis_source_to_hafas_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (206,'2025_02_27_221837_add_column_data_provider_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (207,'2025_03_04_111312_add_latitude_longitude_index_to_train_stations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (208,'2025_04_11_083248_create_motis_sources_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (209,'2025_04_11_131601_add_motis_source_to_trips',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (210,'2025_04_11_155304_add_human_name_to_motis_licenses',1);
