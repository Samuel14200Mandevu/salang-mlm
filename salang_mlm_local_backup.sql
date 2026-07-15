-- MySQL dump 10.13  Distrib 8.4.10, for Linux (x86_64)
--
-- Host: localhost    Database: salang_mlm
-- ------------------------------------------------------
-- Server version	8.4.10-0ubuntu0.26.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `salang_mlm`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `salang_mlm` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `salang_mlm`;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commission_payments`
--

DROP TABLE IF EXISTS `commission_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `commission_period_id` bigint unsigned NOT NULL,
  `total_amount` decimal(15,2) DEFAULT '0.00',
  `tax_amount` decimal(15,2) DEFAULT '0.00',
  `net_amount` decimal(15,2) DEFAULT '0.00',
  `status` enum('pending','approved','paid','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `paid_at` date DEFAULT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_period` (`user_id`,`commission_period_id`),
  KEY `commission_period_id` (`commission_period_id`),
  CONSTRAINT `commission_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `commission_payments_ibfk_2` FOREIGN KEY (`commission_period_id`) REFERENCES `commission_periods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commission_payments`
--

LOCK TABLES `commission_payments` WRITE;
/*!40000 ALTER TABLE `commission_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `commission_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commission_periods`
--

DROP TABLE IF EXISTS `commission_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_periods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `period` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `calculation_date` date DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `status` enum('pending','calculating','calculated','paying','paid','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `total_commissions` decimal(15,2) DEFAULT '0.00',
  `total_paid` decimal(15,2) DEFAULT '0.00',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `period` (`period`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commission_periods`
--

LOCK TABLES `commission_periods` WRITE;
/*!40000 ALTER TABLE `commission_periods` DISABLE KEYS */;
INSERT INTO `commission_periods` VALUES (1,'2026-06','2026-06-01','2026-06-30',NULL,NULL,'calculating',0.00,0.00,NULL,'2026-07-14 06:56:27','2026-07-14 06:56:27'),(2,'2026-07','2026-07-01','2026-07-31',NULL,NULL,'pending',0.00,0.00,NULL,'2026-07-14 13:53:45','2026-07-14 13:53:45');
/*!40000 ALTER TABLE `commission_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commissions`
--

DROP TABLE IF EXISTS `commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `from_user_id` bigint unsigned DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `package_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `commission_period_id` bigint unsigned DEFAULT NULL,
  `period` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `calculation_type` enum('automatic','manual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'automatic',
  `generation` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commissions_from_user_id_foreign` (`from_user_id`),
  KEY `commissions_order_id_foreign` (`order_id`),
  KEY `commissions_package_id_foreign` (`package_id`),
  KEY `commissions_user_id_status_index` (`user_id`,`status`),
  KEY `commissions_type_created_at_index` (`type`,`created_at`),
  KEY `fk_commissions_period` (`commission_period_id`),
  KEY `idx_commissions_user_period` (`user_id`,`period`),
  KEY `idx_commissions_period_status` (`period`,`status`),
  KEY `idx_period_user` (`commission_period_id`,`user_id`),
  KEY `idx_commissions_user_type` (`user_id`,`type`),
  CONSTRAINT `commissions_from_user_id_foreign` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `commissions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `commissions_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `commissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_commissions_period` FOREIGN KEY (`commission_period_id`) REFERENCES `commission_periods` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commissions`
--

LOCK TABLES `commissions` WRITE;
/*!40000 ALTER TABLE `commissions` DISABLE KEYS */;
INSERT INTO `commissions` VALUES (100,1,38,'direct',377.00,26.00,'Commission directe (26.00%) pour achat de Gold par samuel mandevu martin',3,4,'pending',NULL,'2026-07-14 13:53:46','2026-07-14 13:53:46',2,'2026-07','automatic',1);
/*!40000 ALTER TABLE `commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `genealogy`
--

DROP TABLE IF EXISTS `genealogy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `genealogy` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `sponsor_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `level` int NOT NULL DEFAULT '0',
  `position` enum('left','right') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `left_count` int NOT NULL DEFAULT '0',
  `right_count` int NOT NULL DEFAULT '0',
  `total_children` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `genealogy_user_id_sponsor_id_unique` (`user_id`,`sponsor_id`),
  KEY `genealogy_parent_id_foreign` (`parent_id`),
  KEY `genealogy_sponsor_id_level_index` (`sponsor_id`,`level`),
  CONSTRAINT `genealogy_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `genealogy_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `genealogy_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `genealogy`
--

LOCK TABLES `genealogy` WRITE;
/*!40000 ALTER TABLE `genealogy` DISABLE KEYS */;
INSERT INTO `genealogy` VALUES (1,1,NULL,NULL,0,NULL,0,0,0,'2026-06-22 14:32:45','2026-06-22 14:32:45'),(36,38,1,1,1,NULL,0,0,0,'2026-07-14 13:53:21','2026-07-14 13:53:21'),(37,41,1,1,1,'left',0,0,0,'2026-07-14 14:54:31','2026-07-14 14:54:31'),(38,42,1,1,1,'left',0,0,0,'2026-07-14 18:30:00','2026-07-14 18:30:00');
/*!40000 ALTER TABLE `genealogy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `higher_ranks`
--

DROP TABLE IF EXISTS `higher_ranks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `higher_ranks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Rubis, Saphir, Diamant 1, ...',
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` int NOT NULL COMMENT 'Pour l''ordre: 1=Rubis, 2=Saphir, etc.',
  `min_branches_rank_9` int NOT NULL DEFAULT '0' COMMENT 'Nombre de branches Niveau 9 exigé',
  `min_branches_diamond` int DEFAULT NULL COMMENT 'Pour Actionnaire: branches Diamant exigées',
  `global_bonus_percentage` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Part du bonus mondial (ex: 2.5 pour Rubis)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `higher_ranks`
--

LOCK TABLES `higher_ranks` WRITE;
/*!40000 ALTER TABLE `higher_ranks` DISABLE KEYS */;
INSERT INTO `higher_ranks` VALUES (1,'Rubis','rubis',1,2,NULL,2.50,1,NULL,NULL),(2,'Saphir','saphir',2,3,NULL,1.00,1,NULL,NULL),(3,'Diamant 1','diamant-1',3,4,NULL,1.50,1,NULL,NULL),(4,'Diamant 2','diamant-2',4,5,NULL,1.40,1,NULL,NULL),(5,'Diamant 3','diamant-3',5,6,NULL,1.30,1,NULL,NULL),(6,'Diamant 4','diamant-4',6,7,NULL,1.20,1,NULL,NULL),(7,'Diamant 5','diamant-5',7,8,NULL,1.10,1,NULL,NULL),(8,'Actionnaire','actionnaire',8,0,4,1.00,1,NULL,NULL);
/*!40000 ALTER TABLE `higher_ranks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (33,'default','{\"uuid\":\"5ed5ab6a-777b-4035-95b1-a944db2a56c7\",\"displayName\":\"App\\\\Notifications\\\\WelcomeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:33;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:37:\\\"App\\\\Notifications\\\\WelcomeNotification\\\":2:{s:14:\\\"\\u0000*\\u0000sponsorName\\\";s:13:\\\"Administrator\\\";s:2:\\\"id\\\";s:36:\\\"9434b251-6dd3-4577-9c99-782fc4279c53\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1783953076,\"delay\":null}',0,NULL,1783953076,1783953076),(34,'default','{\"uuid\":\"9086b412-71d1-4294-89a1-80f1b445360e\",\"displayName\":\"App\\\\Notifications\\\\WelcomeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:33;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:37:\\\"App\\\\Notifications\\\\WelcomeNotification\\\":2:{s:14:\\\"\\u0000*\\u0000sponsorName\\\";s:13:\\\"Administrator\\\";s:2:\\\"id\\\";s:36:\\\"9434b251-6dd3-4577-9c99-782fc4279c53\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783953076,\"delay\":null}',0,NULL,1783953076,1783953076),(35,'default','{\"uuid\":\"fc5b11da-9d63-4ee5-b6e9-1afff39068ad\",\"displayName\":\"App\\\\Notifications\\\\WelcomeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:34;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:37:\\\"App\\\\Notifications\\\\WelcomeNotification\\\":2:{s:14:\\\"\\u0000*\\u0000sponsorName\\\";s:13:\\\"Administrator\\\";s:2:\\\"id\\\";s:36:\\\"6e1a402a-981a-4644-a203-4aaf8f219cdd\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1783954966,\"delay\":null}',0,NULL,1783954966,1783954966),(36,'default','{\"uuid\":\"96fb0767-7c06-47ed-afbb-8e79782bb60c\",\"displayName\":\"App\\\\Notifications\\\\WelcomeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:34;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:37:\\\"App\\\\Notifications\\\\WelcomeNotification\\\":2:{s:14:\\\"\\u0000*\\u0000sponsorName\\\";s:13:\\\"Administrator\\\";s:2:\\\"id\\\";s:36:\\\"6e1a402a-981a-4644-a203-4aaf8f219cdd\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783954967,\"delay\":null}',0,NULL,1783954967,1783954967),(37,'default','{\"uuid\":\"ee04f6c5-cb7f-4f36-8502-d11cea446fd1\",\"displayName\":\"App\\\\Notifications\\\\WelcomeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:35;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:37:\\\"App\\\\Notifications\\\\WelcomeNotification\\\":2:{s:14:\\\"\\u0000*\\u0000sponsorName\\\";s:13:\\\"Administrator\\\";s:2:\\\"id\\\";s:36:\\\"f713aed1-d185-4258-acf7-555131de9d5d\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1783955069,\"delay\":null}',0,NULL,1783955069,1783955069),(38,'default','{\"uuid\":\"a52908bd-24d7-47db-9466-505e3bc031c1\",\"displayName\":\"App\\\\Notifications\\\\WelcomeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:35;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:37:\\\"App\\\\Notifications\\\\WelcomeNotification\\\":2:{s:14:\\\"\\u0000*\\u0000sponsorName\\\";s:13:\\\"Administrator\\\";s:2:\\\"id\\\";s:36:\\\"f713aed1-d185-4258-acf7-555131de9d5d\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1783955070,\"delay\":null}',0,NULL,1783955070,1783955070),(39,'default','{\"uuid\":\"7cb05202-239b-4b37-be6b-5349b8943278\",\"displayName\":\"App\\\\Jobs\\\\UpdateCumulativePV\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":3600,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\UpdateCumulativePV\",\"command\":\"O:27:\\\"App\\\\Jobs\\\\UpdateCumulativePV\\\":1:{s:9:\\\"\\u0000*\\u0000userId\\\";N;}\",\"batchId\":null},\"createdAt\":1784023903,\"delay\":null}',0,NULL,1784023903,1784023903),(40,'default','{\"uuid\":\"9dd7e6d2-1943-4380-87f3-a4a9d340e38b\",\"displayName\":\"App\\\\Jobs\\\\UpdateCumulativePV\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":3600,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\UpdateCumulativePV\",\"command\":\"O:27:\\\"App\\\\Jobs\\\\UpdateCumulativePV\\\":1:{s:9:\\\"\\u0000*\\u0000userId\\\";i:1;}\",\"batchId\":null},\"createdAt\":1784023904,\"delay\":null}',0,NULL,1784023904,1784023904),(41,'default','{\"uuid\":\"77dd84a5-af3d-4189-932d-e9986eb606f1\",\"displayName\":\"App\\\\Notifications\\\\WelcomeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:38;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:37:\\\"App\\\\Notifications\\\\WelcomeNotification\\\":2:{s:14:\\\"\\u0000*\\u0000sponsorName\\\";s:13:\\\"Administrator\\\";s:2:\\\"id\\\";s:36:\\\"bd206026-c510-4620-a785-05e9978e92f9\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1784044401,\"delay\":null}',0,NULL,1784044401,1784044401),(42,'default','{\"uuid\":\"9b78e310-643b-4f9b-957b-885fdd238940\",\"displayName\":\"App\\\\Notifications\\\\WelcomeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:38;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:37:\\\"App\\\\Notifications\\\\WelcomeNotification\\\":2:{s:14:\\\"\\u0000*\\u0000sponsorName\\\";s:13:\\\"Administrator\\\";s:2:\\\"id\\\";s:36:\\\"bd206026-c510-4620-a785-05e9978e92f9\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1784044401,\"delay\":null}',0,NULL,1784044401,1784044401),(43,'default','{\"uuid\":\"b458aed7-116a-4f8c-85b4-c059d9b4d001\",\"displayName\":\"App\\\\Jobs\\\\UpdateTeamPV\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":3600,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\UpdateTeamPV\",\"command\":\"O:21:\\\"App\\\\Jobs\\\\UpdateTeamPV\\\":2:{s:9:\\\"\\u0000*\\u0000userId\\\";i:38;s:12:\\\"\\u0000*\\u0000recursive\\\";b:1;}\",\"batchId\":null},\"createdAt\":1784044425,\"delay\":null}',0,NULL,1784044425,1784044425),(44,'default','{\"uuid\":\"8fca4efc-7ae9-48b3-bb5c-6c0075e91539\",\"displayName\":\"App\\\\Notifications\\\\ActivationCodeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:41;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:44:\\\"App\\\\Notifications\\\\ActivationCodeNotification\\\":2:{s:17:\\\"\\u0000*\\u0000activationCode\\\";s:16:\\\"ACT-DDF4D976A48B\\\";s:2:\\\"id\\\";s:36:\\\"55d517cb-7e04-44bd-b591-7119495314f2\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1784048072,\"delay\":null}',0,NULL,1784048072,1784048072),(45,'default','{\"uuid\":\"96b27062-dbf5-41c7-94dd-803f845a11d0\",\"displayName\":\"App\\\\Notifications\\\\ActivationCodeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:41;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:44:\\\"App\\\\Notifications\\\\ActivationCodeNotification\\\":2:{s:17:\\\"\\u0000*\\u0000activationCode\\\";s:16:\\\"ACT-DDF4D976A48B\\\";s:2:\\\"id\\\";s:36:\\\"55d517cb-7e04-44bd-b591-7119495314f2\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1784048072,\"delay\":null}',0,NULL,1784048072,1784048072),(46,'default','{\"uuid\":\"7b319208-7493-4080-a013-1a3a72854aa2\",\"displayName\":\"App\\\\Notifications\\\\ActivationCodeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:42;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:44:\\\"App\\\\Notifications\\\\ActivationCodeNotification\\\":2:{s:17:\\\"\\u0000*\\u0000activationCode\\\";s:16:\\\"ACT-E3DCF48B5309\\\";s:2:\\\"id\\\";s:36:\\\"a584a949-99fc-4605-ae39-b23e1b10b794\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1784061001,\"delay\":null}',0,NULL,1784061001,1784061001),(47,'default','{\"uuid\":\"c593e2a4-82e2-423a-9bed-c2492fdd47fa\",\"displayName\":\"App\\\\Notifications\\\\ActivationCodeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:42;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:44:\\\"App\\\\Notifications\\\\ActivationCodeNotification\\\":2:{s:17:\\\"\\u0000*\\u0000activationCode\\\";s:16:\\\"ACT-E3DCF48B5309\\\";s:2:\\\"id\\\";s:36:\\\"a584a949-99fc-4605-ae39-b23e1b10b794\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1784061001,\"delay\":null}',0,NULL,1784061001,1784061001),(48,'default','{\"uuid\":\"5a0615e6-4777-4153-9b7e-470922a1b1f6\",\"displayName\":\"App\\\\Notifications\\\\ActivationCodeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:41;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:44:\\\"App\\\\Notifications\\\\ActivationCodeNotification\\\":2:{s:17:\\\"\\u0000*\\u0000activationCode\\\";s:16:\\\"ACT-D16018CEEC03\\\";s:2:\\\"id\\\";s:36:\\\"f9f6dc4d-201a-49b6-bc1d-93e602031fa5\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1784101711,\"delay\":null}',0,NULL,1784101711,1784101711),(49,'default','{\"uuid\":\"94e57b8e-5f76-47e4-a888-7629b9d9697a\",\"displayName\":\"App\\\\Notifications\\\\ActivationCodeNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:41;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:44:\\\"App\\\\Notifications\\\\ActivationCodeNotification\\\":2:{s:17:\\\"\\u0000*\\u0000activationCode\\\";s:16:\\\"ACT-D16018CEEC03\\\";s:2:\\\"id\\\";s:36:\\\"f9f6dc4d-201a-49b6-bc1d-93e602031fa5\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1784101711,\"delay\":null}',0,NULL,1784101711,1784101711);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kyc_documents`
--

DROP TABLE IF EXISTS `kyc_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kyc_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `document_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'id_card, passport, proof_of_address, selfie',
  `document_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending, verified, rejected',
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kyc_documents_user_id_foreign` (`user_id`),
  KEY `kyc_documents_verified_by_foreign` (`verified_by`),
  KEY `kyc_documents_user_id_status_index` (`user_id`,`status`),
  KEY `kyc_documents_document_type_status_index` (`document_type`,`status`),
  CONSTRAINT `kyc_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kyc_documents_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kyc_documents`
--

LOCK TABLES `kyc_documents` WRITE;
/*!40000 ALTER TABLE `kyc_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `kyc_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_06_22_000000_create_permissions_table',1),(5,'2026_06_22_000000_create_ranks_table',1),(6,'2026_06_22_000001_create_packages_table',1),(7,'2026_06_22_000001_create_roles_table',1),(8,'2026_06_22_000002_create_role_has_permissions_table',1),(9,'2026_06_22_000003_create_model_has_roles_table',1),(10,'2026_06_22_000003_create_products_table',1),(11,'2026_06_22_000004_create_model_has_permissions_table',1),(12,'2026_06_22_000004_create_orders_table',1),(13,'2026_06_22_000005_create_order_items_table',1),(14,'2026_06_22_000006_create_commissions_table',1),(15,'2026_06_22_000007_create_wallets_table',1),(16,'2026_06_22_000008_create_transactions_table',1),(17,'2026_06_22_000009_create_withdrawals_table',1),(18,'2026_06_22_000010_create_genealogy_table',1),(19,'2026_06_22_000011_create_rank_history_table',1),(20,'2026_06_22_000012_add_mlm_fields_to_users_table',1),(21,'2024_01_01_000000_create_wishlist_table',2),(22,'2026_07_12_173157_add_level_to_ranks_table',2),(23,'2026_07_12_173409_add_level_to_ranks_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mlm_settings`
--

DROP TABLE IF EXISTS `mlm_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mlm_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `group` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mlm_settings`
--

LOCK TABLES `mlm_settings` WRITE;
/*!40000 ALTER TABLE `mlm_settings` DISABLE KEYS */;
INSERT INTO `mlm_settings` VALUES (1,'global_bonus_pool_percentage','6','bonus','Pourcentage total du chiffre d\'affaires alloué aux bonus mondiaux (Rubis, Saphir, Diamants, Actionnaire)',NULL,NULL),(2,'retail_profit_percentage','25','commission','Pourcentage de profit sur la revente directe',NULL,NULL),(3,'consumer_bonus_percentage','6','commission','Bonus consommateur sur les achats personnels',NULL,NULL),(4,'leadership_generations_level_5','2','leadership','Nombre de générations pour le leadership Niveau 5',NULL,NULL),(5,'leadership_generations_level_6','3','leadership','Nombre de générations pour le leadership Niveau 6',NULL,NULL),(6,'leadership_generations_level_7','4','leadership','Nombre de générations pour le leadership Niveau 7',NULL,NULL),(7,'leadership_generations_level_8','5','leadership','Nombre de générations pour le leadership Niveau 8',NULL,NULL),(8,'leadership_generations_level_9','6','leadership','Nombre de générations pour le leadership Niveau 9',NULL,NULL);
/*!40000 ALTER TABLE `mlm_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(2,'App\\Models\\User',3),(2,'App\\Models\\User',4),(2,'App\\Models\\User',5),(2,'App\\Models\\User',6),(2,'App\\Models\\User',7),(2,'App\\Models\\User',8),(2,'App\\Models\\User',9),(2,'App\\Models\\User',10),(2,'App\\Models\\User',11),(2,'App\\Models\\User',12),(2,'App\\Models\\User',13),(2,'App\\Models\\User',14),(2,'App\\Models\\User',15),(2,'App\\Models\\User',16),(2,'App\\Models\\User',17),(2,'App\\Models\\User',18),(2,'App\\Models\\User',19),(2,'App\\Models\\User',20),(2,'App\\Models\\User',21),(2,'App\\Models\\User',22),(2,'App\\Models\\User',23),(2,'App\\Models\\User',24),(2,'App\\Models\\User',25),(2,'App\\Models\\User',26);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `package_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `price` decimal(15,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `pv_value` int NOT NULL DEFAULT '0',
  `bv_value` int NOT NULL DEFAULT '0',
  `options` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_package_id_foreign` (`package_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (3,2,NULL,4,'Gold Package','PKG-GOLD',1,1450.00,1450.00,0,0,'{\"bv_value\": 1000, \"pv_value\": 1000}','2026-07-14 10:25:30','2026-07-14 10:25:30'),(4,3,NULL,4,'Gold','PKG-gold',1,1450.00,1450.00,1000,1000,'\"{\\\"package_id\\\":4,\\\"package_name\\\":\\\"Gold\\\"}\"','2026-07-14 13:53:45','2026-07-14 13:53:45');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`localhost`*/ /*!50003 TRIGGER `after_order_item_insert` AFTER INSERT ON `order_items` FOR EACH ROW BEGIN
    DECLARE user_id_val INT;
    DECLARE total_pv INT;
    DECLARE total_bv INT;
    
    SELECT user_id INTO user_id_val FROM orders WHERE id = NEW.order_id;
    
    -- Recalculer les PV mensuels
    SELECT SUM(pv_value) INTO total_pv
    FROM order_items oi
    INNER JOIN orders o ON oi.order_id = o.id
    WHERE o.user_id = user_id_val
    AND o.payment_status = 'completed'
    AND MONTH(o.created_at) = MONTH(NOW())
    AND YEAR(o.created_at) = YEAR(NOW());
    
    SELECT SUM(bv_value) INTO total_bv
    FROM order_items oi
    INNER JOIN orders o ON oi.order_id = o.id
    WHERE o.user_id = user_id_val
    AND o.payment_status = 'completed'
    AND MONTH(o.created_at) = MONTH(NOW())
    AND YEAR(o.created_at) = YEAR(NOW());
    
    UPDATE users SET 
        monthly_pv = COALESCE(total_pv, 0),
        monthly_bv = COALESCE(total_bv, 0)
    WHERE id = user_id_val;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `order_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `tax` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shipping` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `billing_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_order_number_index` (`order_number`),
  KEY `orders_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (2,1,'ORD-96F24E081AE5',1450.00,0.00,0.00,0.00,1450.00,'completed','completed','mobile_money',NULL,NULL,NULL,'2026-07-14 10:25:29','2026-07-14 10:25:29','2026-07-14 10:25:29'),(3,38,'PKG-6A565B89DDC76',1450.00,0.00,0.00,0.00,1450.00,'completed','completed',NULL,NULL,NULL,NULL,'2026-07-14 13:53:45','2026-07-14 13:53:45','2026-07-14 13:53:45');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `pv_value` int NOT NULL DEFAULT '0',
  `bv_value` int NOT NULL DEFAULT '0',
  `commission_rate` decimal(5,2) NOT NULL DEFAULT '30.00',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `benefits` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `packages_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packages`
--

LOCK TABLES `packages` WRITE;
/*!40000 ALTER TABLE `packages` DISABLE KEYS */;
INSERT INTO `packages` VALUES (1,'Starter','starter',30.00,0,0,30.00,NULL,'[]',1,NULL,'2026-07-09 12:44:54'),(2,'Silver','silver',85.00,0,0,30.00,NULL,NULL,1,NULL,NULL),(3,'Bronze','bronze',350.00,200,200,30.00,NULL,NULL,1,NULL,NULL),(4,'Gold','gold',1450.00,1000,1000,30.00,NULL,NULL,1,NULL,NULL),(5,'Emerald','emerald',4850.00,3800,3800,30.00,NULL,NULL,1,NULL,NULL);
/*!40000 ALTER TABLE `packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'manage users','web','2026-06-22 14:32:44','2026-06-22 14:32:44'),(2,'manage packages','web','2026-06-22 14:32:44','2026-06-22 14:32:44'),(3,'manage products','web','2026-06-22 14:32:44','2026-06-22 14:32:44'),(4,'manage commissions','web','2026-06-22 14:32:44','2026-06-22 14:32:44'),(5,'manage wallets','web','2026-06-22 14:32:45','2026-06-22 14:32:45'),(6,'view reports','web','2026-06-22 14:32:45','2026-06-22 14:32:45');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price` decimal(15,2) NOT NULL,
  `pv_value` int NOT NULL DEFAULT '10',
  `bv_value` int NOT NULL DEFAULT '10',
  `cost` decimal(15,2) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_slug_is_active_index` (`slug`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'MacBook Pro 16\" M3','macbook-pro-16-m3','Le MacBook Pro 16 pouces avec puce M3 Pro, parfait pour les professionnels exigeants',1200.00,10,10,850.00,15,'MBP-16-M3-001','Informatique','1782465218_macbook-pro-16-m3.jpg',NULL,1,1,NULL,'2026-06-25 14:16:45','2026-07-09 12:37:56'),(2,'MacBook Air 13\" M3','macbook-air-13-m3','Le MacBook Air léger et puissant avec puce M3',1299.00,10,10,1000.00,25,'MBA-13-M3-002','Informatique','1782467377_macbook-air-13-m3.jpg',NULL,1,1,NULL,'2026-06-25 14:16:45','2026-06-26 07:49:37'),(3,'iMac 24\" M3','imac-24-m3','L\'iMac tout-en-un avec écran 4.5K et puce M3',1499.00,10,10,1200.00,10,'IMC-24-M3-003','Informatique','1782467436_imac-24-m3.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:50:36'),(4,'Mac mini M2 Pro','mac-mini-m2-pro','Le Mac mini compact avec puce M2 Pro',799.00,10,10,650.00,20,'MMI-M2P-004','Informatique','1782467464_mac-mini-m2-pro.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:51:04'),(5,'Studio Display 27\"','studio-display-27','Écran 5K Retina pour les professionnels',1599.00,10,10,1200.00,8,'STD-27-005','Informatique','1782467490_studio-display-27.jpg',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:51:30'),(6,'iPhone 15 Pro Max','iphone-15-pro-max','Le meilleur iPhone avec écran 6.7\", puce A17 Pro et batterie longue durée',1499.00,10,10,1200.00,30,'IPH-15PM-006','Téléphonie','1782467585_iphone-15-pro-max.jpg',NULL,1,1,NULL,'2026-06-25 14:16:45','2026-06-26 07:53:05'),(7,'iPhone 15 Pro','iphone-15-pro','iPhone 15 Pro avec écran 6.1\" et puce A17 Pro',1299.00,10,10,1050.00,35,'IPH-15P-007','Téléphonie','1782465242_iphone-15-pro.png',NULL,1,1,NULL,'2026-06-25 14:16:45','2026-06-26 07:14:02'),(8,'iPhone 15','iphone-15','iPhone 15 avec écran 6.1\" et puce A16 Bionic',999.00,10,10,800.00,40,'IPH-15-008','Téléphonie','1782465324_iphone-15.png',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:15:24'),(9,'iPhone 15 Plus','iphone-15-plus','iPhone 15 Plus avec écran 6.7\"',1099.00,10,10,880.00,25,'IPH-15P-009','Téléphonie','1782465354_iphone-15-plus.png',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:15:54'),(10,'iPhone 14 Pro','iphone-14-pro','iPhone 14 Pro avec écran 6.1\" et puce A16',1099.00,10,10,880.00,20,'IPH-14P-010','Téléphonie','1782464291_iphone-14-pro.jpg',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 06:58:11'),(11,'AirPods Pro 2','airpods-pro-2','Écouteurs sans fil avec réduction de bruit active et audio spatial',279.00,10,10,200.00,50,'APP-2-011','Audio','1782467622_airpods-pro-2.jpeg',NULL,1,1,NULL,'2026-06-25 14:16:45','2026-06-26 07:53:42'),(12,'AirPods Max','airpods-max','Casque audio haut de gamme avec réduction de bruit',549.00,10,10,400.00,20,'APM-012','Audio','1782467642_airpods-max.jpg',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:54:02'),(13,'AirPods 3','airpods-3','Écouteurs sans fil avec audio spatial',199.00,10,10,150.00,45,'AP3-013','Audio','1782467664_airpods-3.jpg',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:54:24'),(14,'Beats Studio Buds','beats-studio-buds','Écouteurs sans fil avec suppression de bruit',199.00,10,10,150.00,30,'BSB-014','Audio','1782467682_beats-studio-buds.jpg',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:54:42'),(15,'HomePod mini','homepod-mini','Enceinte intelligente compacte',109.00,10,10,80.00,25,'HPM-015','Audio','1782467705_homepod-mini.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:55:05'),(16,'iPad Pro 12.9\" M2','ipad-pro-129-m2','L\'iPad Pro avec écran XDR et puce M2',1299.00,10,10,1000.00,15,'IPP-129-016','Tablette','1782467736_ipad-pro-129-m2.jpeg',NULL,1,1,NULL,'2026-06-25 14:16:45','2026-06-26 07:55:36'),(17,'iPad Pro 11\" M2','ipad-pro-11-m2','L\'iPad Pro compact avec puce M2',999.00,10,10,750.00,20,'IPP-11-017','Tablette','1782467763_ipad-pro-11-m2.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:56:03'),(18,'iPad Air 5','ipad-air-5','iPad Air avec puce M1',799.00,10,10,600.00,25,'IPA-5-018','Tablette','1782467146_ipad-air-5.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:45:46'),(19,'iPad 10e','ipad-10e','iPad 10e génération',499.00,10,10,380.00,30,'IPD-10-019','Tablette','1782467319_ipad-10e.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:48:39'),(20,'iPad mini 6','ipad-mini-6','iPad mini avec écran 8.3\"',599.00,10,10,450.00,18,'IPM-6-020','Tablette','1782467343_ipad-mini-6.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:45','2026-06-26 07:49:03'),(21,'Apple Watch Ultra 2','apple-watch-ultra-2','La montre connectée la plus robuste avec écran toujours allumé',899.00,10,10,700.00,12,'AWU-2-021','Montres','1782465713_apple-watch-ultra-2.jpeg',NULL,1,1,NULL,'2026-06-25 14:16:46','2026-06-29 16:24:51'),(22,'Apple Watch Series 9','apple-watch-series-9','Apple Watch Series 9 avec puce S9',499.00,10,10,380.00,25,'AWS-9-022','Montres','1782465761_apple-watch-series-9.jpeg',NULL,1,1,NULL,'2026-06-25 14:16:46','2026-06-29 16:24:51'),(23,'Apple Watch SE 2','apple-watch-se-2','Apple Watch SE 2e génération',299.00,10,10,220.00,30,'AWSE-2-023','Montres','1782465800_apple-watch-se-2.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 07:23:20'),(24,'Apple Watch Hermès','apple-watch-hermes','Apple Watch Series 9 Hermès',1299.00,10,10,1000.00,5,'AWH-024','Montres','1782465866_apple-watch-hermes.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 07:24:26'),(25,'Apple Watch Nike','apple-watch-nike','Apple Watch Series 9 Nike',499.00,10,10,380.00,15,'AWN-025','Montres','1782465914_apple-watch-nike.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 07:25:14'),(26,'Magic Keyboard','magic-keyboard','Clavier Magic avec Touch ID',149.00,10,10,100.00,40,'MK-026','Accessoires','1782468349_magic-keyboard.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 08:05:49'),(27,'Magic Mouse','magic-mouse','Souris Magic rechargeable',99.00,10,10,70.00,45,'MM-027','Accessoires','1782467999_magic-mouse.jpg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 07:59:59'),(28,'Magic Trackpad','magic-trackpad','Trackpad Magic rechargeable',129.00,10,10,90.00,30,'MT-028','Accessoires','1782466018_magic-trackpad.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 07:26:58'),(29,'Apple Pencil 2','apple-pencil-2','Stylet pour iPad Pro et iPad Air',139.00,10,10,100.00,35,'AP-2-029','Accessoires','1782468759_apple-pencil-2.jpg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 08:12:40'),(30,'AirTag','airtag','Localisateur d\'objets',39.00,10,10,25.00,60,'AT-030','Accessoires','1782467192_airtag.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 07:46:32'),(31,'Coque iPhone 15 Pro','coque-iphone-15-pro','Coque de protection iPhone 15 Pro',49.00,10,10,30.00,50,'CIP-15P-031','Accessoires','1782467216_coque-iphone-15-pro.jpg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 07:46:56'),(32,'Protecteur écran iPhone','protecteur-ecran-iphone','Protecteur d\'écran en verre trempé',29.00,10,10,15.00,70,'PEI-032','Accessoires','1782467235_protecteur-ecran-iphone.jpg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 07:47:15'),(33,'Chargeur sans fil MagSafe','chargeur-sans-fil-magsafe','Chargeur sans fil MagSafe 15W',49.00,10,10,30.00,40,'CSM-033','Accessoires','1782467261_chargeur-sans-fil-magsafe.jpg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 07:47:41'),(34,'AppleCare+ MacBook Pro','applecare-macbook-pro','Extension de garantie pour MacBook Pro',399.00,10,10,300.00,999,'ACM-034','Services','1782466094_applecare-macbook-pro.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 07:28:14'),(35,'AppleCare+ iPhone','applecare-iphone','Extension de garantie pour iPhone',199.00,10,10,150.00,999,'ACI-035','Services','1782467295_applecare-iphone.jpg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 07:48:15'),(36,'AppleCare+ iPad','applecare-ipad','Extension de garantie pour iPad',99.00,10,10,70.00,999,'ACI-036','Services','1782465664_applecare-ipad.jpeg',NULL,1,0,NULL,'2026-06-25 14:16:46','2026-06-26 07:21:04');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qualified_branches`
--

DROP TABLE IF EXISTS `qualified_branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `qualified_branches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT 'Le leader (ex: vous)',
  `branch_user_id` bigint unsigned NOT NULL COMMENT 'Le 1er filleul de la branche',
  `period` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Format: YYYY-MM',
  `branch_rank_level` int NOT NULL COMMENT 'Niveau atteint par la branche (ex: 4, 5, 6)',
  `branch_pv` int NOT NULL DEFAULT '0' COMMENT 'PV total de la branche sur la période',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_qualified_branch` (`user_id`,`branch_user_id`,`period`),
  KEY `branch_user_id` (`branch_user_id`),
  CONSTRAINT `qualified_branches_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `qualified_branches_ibfk_2` FOREIGN KEY (`branch_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qualified_branches`
--

LOCK TABLES `qualified_branches` WRITE;
/*!40000 ALTER TABLE `qualified_branches` DISABLE KEYS */;
/*!40000 ALTER TABLE `qualified_branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rank_history`
--

DROP TABLE IF EXISTS `rank_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rank_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `old_rank_id` bigint unsigned DEFAULT NULL,
  `new_rank_id` bigint unsigned NOT NULL,
  `old_rank_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_rank_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pv_at_time` int NOT NULL DEFAULT '0',
  `bv_at_time` int NOT NULL DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rank_history_old_rank_id_foreign` (`old_rank_id`),
  KEY `rank_history_new_rank_id_foreign` (`new_rank_id`),
  KEY `rank_history_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `rank_history_new_rank_id_foreign` FOREIGN KEY (`new_rank_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rank_history_old_rank_id_foreign` FOREIGN KEY (`old_rank_id`) REFERENCES `ranks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rank_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rank_history`
--

LOCK TABLES `rank_history` WRITE;
/*!40000 ALTER TABLE `rank_history` DISABLE KEYS */;
INSERT INTO `rank_history` VALUES (3,1,4,3,'Directeur','Cumul Directeur',1000,500,'Automatic rank update','2026-07-13 12:31:16','2026-07-13 12:31:16'),(7,38,1,4,'Distributor','Directeur',1000,1000,'Automatic rank update','2026-07-14 13:53:45','2026-07-14 13:53:45');
/*!40000 ALTER TABLE `rank_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranks`
--

DROP TABLE IF EXISTS `ranks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ranks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `level` int NOT NULL DEFAULT '1',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_pv` int NOT NULL DEFAULT '0',
  `min_bv` int NOT NULL DEFAULT '0',
  `monthly_pv_required` int NOT NULL DEFAULT '0' COMMENT 'PV personnel mensuel exigé pour toucher les commissions',
  `team_pv_required` int NOT NULL DEFAULT '0' COMMENT 'PV d''équipe mensuel exigé pour le leadership',
  `min_sponsors` int NOT NULL DEFAULT '0',
  `min_team` int NOT NULL DEFAULT '0',
  `bonus_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `pv_payment_required` int DEFAULT '0',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `conditions` json DEFAULT NULL,
  `commission_types` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ranks_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranks`
--

LOCK TABLES `ranks` WRITE;
/*!40000 ALTER TABLE `ranks` DISABLE KEYS */;
INSERT INTO `ranks` VALUES (1,1,'Distributeur','distributor',0,0,0,0,0,0,6.00,0,'Grade de base, point de départ dans le système Salang','[{\"label\": \"Inscription\", \"value\": \"Validée\"}]','[\"Bonus Consommateur (6%)\"]',1,NULL,NULL),(2,2,'Qualification','supervisor',100,100,20,0,0,0,6.00,20,'Premier grade actif, débutez vos commissions','[{\"label\": \"PV Personnel\", \"value\": \"≥ 100 PV\"}, {\"label\": \"PV Mensuel\", \"value\": \"≥ 20 PV\"}]','[\"Bonus Direct (6%)\", \"Bonus Consommateur (6%)\"]',1,NULL,NULL),(3,3,'Cumul Directeur','assistant-manager',200,200,20,0,0,0,22.00,20,'Grade intermédiaire, augmentez vos commissions','[{\"label\": \"PV Personnel\", \"value\": \"≥ 200 PV\"}, {\"label\": \"PV Mensuel\", \"value\": \"≥ 20 PV\"}]','[\"Bonus Direct (22%)\", \"Bonus Indirect\", \"Bonus Consommateur (6%)\"]',1,NULL,NULL),(4,4,'Directeur','manager',1000,1000,25,0,0,0,26.00,25,'Grade de leader, commencez à développer votre réseau','[{\"type\": \"personal_pv\", \"label\": \"PV ≥ 1000\", \"value\": 1000}, {\"type\": \"branches\", \"label\": \"3 branches Niveau 3 avec CUMUL ≥ 1000 PV\", \"branches\": 3, \"group_pv\": 1000, \"rank_level\": 3}, {\"type\": \"branches\", \"label\": \"2 branches Niveau 3 avec CUMUL ≥ 2200 PV\", \"branches\": 2, \"group_pv\": 2200, \"rank_level\": 3}]','[\"Bonus Direct (26%)\", \"Bonus Indirect\", \"Bonus Consommateur (6%)\"]',1,NULL,'2026-07-13 12:47:13'),(5,5,'Manager Senior','senior-manager',3800,3800,30,500,0,0,30.00,30,'Grade de manager, optimisez les commissions de votre réseau','[{\"type\": \"branches\", \"label\": \"3 branches Niveau 4 avec CUMUL ≥ 3800 PV\", \"branches\": 3, \"group_pv\": 3800, \"rank_level\": 4}, {\"type\": \"branches\", \"label\": \"2 branches Niveau 4 avec CUMUL ≥ 7800 PV\", \"branches\": 2, \"group_pv\": 7800, \"rank_level\": 4}, {\"type\": \"branches_mixed\", \"label\": \"2 branches Niveau 4 + 4 branches Niveau 3 avec CUMUL ≥ 3800 PV\", \"branches\": {\"2\": 4, \"4\": 3}, \"group_pv\": 3800}, {\"type\": \"branches_mixed\", \"label\": \"1 branche Niveau 4 + 6 branches Niveau 3 avec CUMUL ≥ 3800 PV\", \"branches\": {\"1\": 4, \"6\": 3}, \"group_pv\": 3800}]','[\"Bonus Direct (30%)\", \"Bonus Indirect\", \"Bonus Leadership (0.5%)\", \"Bonus Consommateur (6%)\"]',1,NULL,'2026-07-13 13:41:27'),(6,6,'Directeur Envolée','soaring-manager',16000,16000,50,1000,0,0,34.00,50,'Grade de directeur, développez un réseau profond','[{\"type\": \"branches\", \"label\": \"3 branches Niveau 5 avec CUMUL ≥ 16000 PV\", \"branches\": 3, \"group_pv\": 16000, \"rank_level\": 5}, {\"type\": \"branches\", \"label\": \"2 branches Niveau 5 avec CUMUL ≥ 35000 PV\", \"branches\": 2, \"group_pv\": 35000, \"rank_level\": 5}, {\"type\": \"branches_mixed\", \"label\": \"2 branches Niveau 5 + 4 branches Niveau 4 avec CUMUL ≥ 16000 PV\", \"branches\": {\"2\": 5, \"4\": 4}, \"group_pv\": 16000}, {\"type\": \"branches_mixed\", \"label\": \"1 branche Niveau 5 + 6 branches Niveau 4 avec CUMUL ≥ 16000 PV\", \"branches\": {\"1\": 5, \"6\": 4}, \"group_pv\": 16000}]','[\"Bonus Direct (34%)\", \"Bonus Indirect\", \"Bonus Leadership (1.1%)\", \"Bonus Consommateur (6%)\"]',1,NULL,'2026-07-13 12:47:13'),(7,7,'Saphire Manager','sapphire-manager',73000,73000,100,2000,0,0,40.00,100,'Grade saphir, accédez aux primes mondiales','[{\"type\": \"branches\", \"label\": \"3 branches Niveau 6 avec CUMUL ≥ 73000 PV\", \"branches\": 3, \"group_pv\": 73000, \"rank_level\": 6}, {\"type\": \"branches\", \"label\": \"2 branches Niveau 6 avec CUMUL ≥ 145000 PV\", \"branches\": 2, \"group_pv\": 145000, \"rank_level\": 6}, {\"type\": \"branches_mixed\", \"label\": \"2 branches Niveau 6 + 4 branches Niveau 5 avec CUMUL ≥ 73000 PV\", \"branches\": {\"2\": 6, \"4\": 5}, \"group_pv\": 73000}, {\"type\": \"branches_mixed\", \"label\": \"1 branche Niveau 6 + 6 branches Niveau 5 avec CUMUL ≥ 73000 PV\", \"branches\": {\"1\": 6, \"6\": 5}, \"group_pv\": 73000}]','[\"Bonus Direct (40%)\", \"Bonus Indirect\", \"Bonus Leadership (1.8%)\", \"Bonus Consommateur (6%)\"]',1,NULL,NULL),(8,8,'Diamant Bleu','blue-diamond',280000,280000,180,3000,0,0,43.00,200,'Grade diamant, primes mondiales significatives','[{\"type\": \"branches\", \"label\": \"3 branches Niveau 7 avec CUMUL ≥ 280000 PV\", \"branches\": 3, \"group_pv\": 280000, \"rank_level\": 7}, {\"type\": \"branches\", \"label\": \"2 branches Niveau 7 avec CUMUL ≥ 580000 PV\", \"branches\": 2, \"group_pv\": 580000, \"rank_level\": 7}, {\"type\": \"branches_mixed\", \"label\": \"2 branches Niveau 7 + 4 branches Niveau 6 avec CUMUL ≥ 280000 PV\", \"branches\": {\"2\": 7, \"4\": 6}, \"group_pv\": 280000}, {\"type\": \"branches_mixed\", \"label\": \"1 branche Niveau 7 + 6 branches Niveau 6 avec CUMUL ≥ 280000 PV\", \"branches\": {\"1\": 7, \"6\": 6}, \"group_pv\": 280000}]','[\"Bonus Direct (43%)\", \"Bonus Indirect\", \"Bonus Leadership (2.6%)\", \"Bonus Consommateur (6%)\"]',1,NULL,NULL),(9,9,'Perle Diamant','diamond',400000,400000,300,5000,0,0,45.00,300,'Grade ultime, bonuses mondiaux maximum','[{\"type\": \"branches\", \"label\": \"3 branches Niveau 8 avec CUMUL ≥ 400000 PV\", \"branches\": 3, \"group_pv\": 400000, \"rank_level\": 8}, {\"type\": \"branches\", \"label\": \"2 branches Niveau 8 avec CUMUL ≥ 780000 PV\", \"branches\": 2, \"group_pv\": 780000, \"rank_level\": 8}, {\"type\": \"branches_mixed\", \"label\": \"2 branches Niveau 8 + 4 branches Niveau 7 avec CUMUL ≥ 400000 PV\", \"branches\": {\"2\": 8, \"4\": 7}, \"group_pv\": 400000}, {\"type\": \"branches_mixed\", \"label\": \"1 branche Niveau 8 + 6 branches Niveau 7 avec CUMUL ≥ 400000 PV\", \"branches\": {\"1\": 8, \"6\": 7}, \"group_pv\": 400000}]','[\"Bonus Direct (45%)\", \"Bonus Indirect\", \"Bonus Leadership (3.5%)\", \"Bonus Consommateur (6%)\"]',1,NULL,NULL),(10,10,'Pearl','pearl',50000,0,0,0,0,0,50.00,0,'Grade Pearl, niveau avancé du système','[{\"label\": \"Être niveau 10\", \"value\": \"Avoir les conditions requises pour Pearl\"}]','[\"Bonus Direct (50%)\", \"Bonus Indirect\", \"Bonus Leadership (4.0%)\", \"Bonus Consommateur (6%)\"]',0,NULL,NULL);
/*!40000 ALTER TABLE `ranks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','web','2026-06-22 14:32:45','2026-06-22 14:32:45'),(2,'user','web','2026-06-22 14:32:45','2026-06-22 14:32:45');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('hNjWh0UrSH91utBcJHy6NpmLUWB7I0DEHzfgg6bE',41,'127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNmdzSFB4UEF2SHZtTW5nUzZ1dDBmNmx4bWhXU3pYZGF5UkwxeGllVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MTp7aTowO3M6Nzoid2FybmluZyI7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9uZXR3b3JrIjtzOjU6InJvdXRlIjtzOjEzOiJuZXR3b3JrLmluZGV4Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDE7czo3OiJ3YXJuaW5nIjtzOjY3OiJWb3RyZSBjb21wdGUgZXN0IGluYWN0aWYuIEFjdGl2ZXotbGUgcG91ciByZWNldm9pciBkZXMgY29tbWlzc2lvbnMuIjt9',1784101883),('p0anGaKXXRQ1LH9g19KaCj5dYTS0q4NIr02lX4Ay',NULL,'127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTGRlb2h5VTJsejZqNmIxZXJYcDJOYmZQVGVXZFFJNnpwN2JrZ0pTMiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNjoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL3VzZXJzLzQyIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi91c2Vycy80MiI7czo1OiJyb3V0ZSI7czoxNjoiYWRtaW4udXNlcnMuc2hvdyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1784099119),('Wei2VUeM19lCLECCnjOyaqXlbGNShDymFZrFs7vj',NULL,'127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMEhCWmRWaTJmbE9FOWF6V09VZ3FPT1VCeVQ5RUVrZmhiTnRTTkJvSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1784101300);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `wallet_id` bigint unsigned NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `fee` decimal(15,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_wallet_id_foreign` (`wallet_id`),
  KEY `transactions_user_id_type_status_index` (`user_id`,`type`,`status`),
  KEY `transactions_created_at_index` (`created_at`),
  KEY `idx_transactions_reference` (`reference`),
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (65,38,38,'deposit',1500.00,0.00,1500.00,0.00,1500.00,'completed','DEP-6A565B84BA8E7','Deposit via Mobile money','\"{\\\"payment_method\\\":\\\"mobile_money\\\"}\"','2026-07-14 13:53:40','2026-07-14 13:53:40','2026-07-14 13:53:40'),(66,38,38,'purchase',-1450.00,0.00,-1450.00,1500.00,50.00,'completed',NULL,'Purchase of package Gold','\"{\\\"package_id\\\":4}\"','2026-07-14 13:53:45','2026-07-14 13:53:45','2026-07-14 13:53:45');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_monthly_ranks`
--

DROP TABLE IF EXISTS `user_monthly_ranks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_monthly_ranks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `rank_id` bigint unsigned NOT NULL,
  `period` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pv_monthly` int DEFAULT '0',
  `bv_monthly` int DEFAULT '0',
  `team_pv` int DEFAULT '0',
  `team_bv` int DEFAULT '0',
  `direct_sponsors` int DEFAULT '0',
  `qualified_branches` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_period` (`user_id`,`period`),
  KEY `rank_id` (`rank_id`),
  CONSTRAINT `user_monthly_ranks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_monthly_ranks_ibfk_2` FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_monthly_ranks`
--

LOCK TABLES `user_monthly_ranks` WRITE;
/*!40000 ALTER TABLE `user_monthly_ranks` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_monthly_ranks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `sponsor_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parrain_id` bigint unsigned DEFAULT NULL,
  `position` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rank` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Distributor',
  `rank_id` bigint unsigned DEFAULT NULL,
  `package_id` bigint unsigned DEFAULT NULL,
  `pv_balance` int NOT NULL DEFAULT '0',
  `bv_balance` int NOT NULL DEFAULT '0',
  `commission_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_earnings` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_sponsors` int NOT NULL DEFAULT '0',
  `total_team` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `activation_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activation_code_expires_at` timestamp NULL DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `activation_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kyc_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_submitted',
  `kyc_verified_at` timestamp NULL DEFAULT NULL,
  `package_expiry` timestamp NULL DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `monthly_pv` int DEFAULT '0',
  `monthly_bv` int DEFAULT '0',
  `team_pv` int DEFAULT '0',
  `team_bv` int DEFAULT '0',
  `qualified_branches` int DEFAULT '0',
  `direct_sponsors_count` int DEFAULT '0',
  `last_rank_update` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_package_id_foreign` (`package_id`),
  KEY `users_kyc_status_index` (`kyc_status`),
  KEY `idx_users_parrain` (`parrain_id`),
  KEY `idx_users_rank` (`rank_id`),
  KEY `idx_users_parrain_id` (`parrain_id`),
  KEY `idx_monthly_pv` (`monthly_pv`),
  KEY `idx_team_pv` (`team_pv`),
  KEY `idx_users_sponsor_id` (`sponsor_id`),
  KEY `idx_activation_code` (`activation_code`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `fk_users_parrain` FOREIGN KEY (`parrain_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_rank_id_foreign` FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrator','admin@salang.com',NULL,NULL,'SALADMIN',NULL,NULL,'+225 0700000000','Directeur',4,4,1000,1000,0.00,0.00,3,4,1,NULL,NULL,'2026-07-14 16:22:58','system','not_submitted',NULL,NULL,'avatar_1_1783085629.jpeg',NULL,NULL,'Côte d\'Ivoire','Abidjan',NULL,NULL,'$2y$12$ZBAOB9BgU8FyTqcjhks.G.1zu0C2kC33AoqADM72IxuZnF1qW7kau',NULL,'2026-06-22 14:32:45','2026-07-14 18:30:00',1000,1000,1000,0,0,0,'2026-07-13'),(38,'samuel mandevu martin','samuelmandevu10@gmail.com',NULL,NULL,'SAL1028AD',1,NULL,'975746415','Directeur',4,4,1000,1000,0.00,0.00,0,0,1,NULL,NULL,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$kjSq1RXtoR81Ld9m4NY2bu7jLRqmghGn6RNBqVYqchf6B3piN8VCW',NULL,'2026-07-14 13:53:21','2026-07-14 13:53:45',1000,1000,0,0,0,0,'2026-07-14'),(41,'Issa MAKENGO Daniel','issamakengodaniel@salang.com',NULL,NULL,'SAL72E2CB',1,NULL,'+243975746415','Distributor',1,NULL,0,0,0.00,0.00,0,0,0,NULL,NULL,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$x6Rl6g1aHc5NO0E8bmtfPuf/UgPiHkOoJc6pzTuvsxuujqM8TIBOq',NULL,'2026-07-14 14:54:31','2026-07-14 14:54:31',0,0,0,0,0,0,NULL),(42,'Carmel KALUTA','kalutacarmel@salang.com',NULL,NULL,'SAL83CF96',1,NULL,'+243979573654','Distributor',1,NULL,0,0,0.00,0.00,0,0,0,NULL,NULL,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$lBOXytAf1UVZxjlToIAR/uTBuq7vhZdXZS7tNDwS.TQpWqAurRL66',NULL,'2026-07-14 18:30:00','2026-07-14 18:30:00',0,0,0,0,0,0,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallets`
--

DROP TABLE IF EXISTS `wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wallets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pending_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_withdrawn` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_deposited` decimal(15,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallets_user_id_unique` (`user_id`),
  CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallets`
--

LOCK TABLES `wallets` WRITE;
/*!40000 ALTER TABLE `wallets` DISABLE KEYS */;
INSERT INTO `wallets` VALUES (38,38,50.00,0.00,0.00,1500.00,'USD',1,'2026-07-14 13:53:21','2026-07-14 13:53:45'),(41,41,0.00,0.00,0.00,0.00,'USD',1,'2026-07-14 14:54:31','2026-07-14 14:54:31'),(42,42,0.00,0.00,0.00,0.00,'USD',1,'2026-07-14 18:30:00','2026-07-14 18:30:00');
/*!40000 ALTER TABLE `wallets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlist` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlist_user_id_product_id_unique` (`user_id`,`product_id`),
  KEY `wishlist_product_id_foreign` (`product_id`),
  CONSTRAINT `wishlist_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist`
--

LOCK TABLES `wishlist` WRITE;
/*!40000 ALTER TABLE `wishlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `withdrawals`
--

DROP TABLE IF EXISTS `withdrawals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `withdrawals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `wallet_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `fee` decimal(15,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(15,2) NOT NULL,
  `method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_details` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `withdrawals_wallet_id_foreign` (`wallet_id`),
  KEY `withdrawals_user_id_status_index` (`user_id`,`status`),
  KEY `withdrawals_created_at_index` (`created_at`),
  CONSTRAINT `withdrawals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `withdrawals_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `withdrawals`
--

LOCK TABLES `withdrawals` WRITE;
/*!40000 ALTER TABLE `withdrawals` DISABLE KEYS */;
/*!40000 ALTER TABLE `withdrawals` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-15 10:01:57
