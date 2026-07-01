-- phpMyAdmin SQL Dump
-- version 5.2.3deb1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mer. 01 juil. 2026 à 14:17
-- Version du serveur : 8.4.9-0ubuntu0.26.04.1
-- Version de PHP : 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `salang_mlm`
--

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commissions`
--

CREATE TABLE `commissions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `from_user_id` bigint UNSIGNED DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `package_id` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commissions`
--

INSERT INTO `commissions` (`id`, `user_id`, `from_user_id`, `type`, `amount`, `percentage`, `description`, `order_id`, `package_id`, `status`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 'indirect', 214.00, 15.00, 'Commission indirect de Nicolas Dubois', NULL, NULL, 'pending', '2026-06-08 14:32:53', '2026-06-20 14:32:53', '2026-06-22 14:32:53'),
(2, 1, 11, 'direct', 117.00, 30.00, 'Commission direct de Camille Moreau', NULL, NULL, 'pending', NULL, '2026-06-15 14:32:53', '2026-06-22 14:32:53'),
(3, 1, 2, 'retail', 273.00, 25.00, 'Commission retail de Jean Dupont', NULL, NULL, 'paid', '2026-06-13 14:32:53', '2026-06-04 14:32:53', '2026-06-22 14:32:53'),
(4, 1, 4, 'direct', 83.00, 30.00, 'Commission direct de Pierre Durand', NULL, NULL, 'pending', '2026-06-19 14:32:53', '2026-05-29 14:32:53', '2026-06-22 14:32:53'),
(5, 1, 12, 'leadership', 268.00, 10.00, 'Commission leadership de Alexandre Laurent', NULL, NULL, 'pending', NULL, '2026-06-01 14:32:53', '2026-06-22 14:32:53'),
(6, 1, 8, 'retail', 293.00, 25.00, 'Commission retail de Thomas Robert', NULL, NULL, 'pending', NULL, '2026-05-29 14:32:53', '2026-06-22 14:32:53'),
(7, 1, 9, 'leadership', 69.00, 10.00, 'Commission leadership de Julie Richard', NULL, NULL, 'pending', NULL, '2026-05-26 14:32:53', '2026-06-22 14:32:53'),
(8, 1, 5, 'retail', 57.00, 25.00, 'Commission retail de Sophie Lefevre', NULL, NULL, 'paid', '2026-06-19 14:32:53', '2026-05-30 14:32:53', '2026-06-22 14:32:53'),
(9, 1, 4, 'indirect', 39.00, 15.00, 'Commission indirect de Pierre Durand', NULL, NULL, 'paid', NULL, '2026-05-27 14:32:53', '2026-06-22 14:32:53'),
(10, 1, 8, 'leadership', 108.00, 10.00, 'Commission leadership de Thomas Robert', NULL, NULL, 'paid', '2026-05-27 14:32:53', '2026-05-25 14:32:53', '2026-06-22 14:32:53'),
(11, 1, 6, 'retail', 215.00, 25.00, 'Commission retail de Lucas Bernard', NULL, NULL, 'paid', '2026-06-17 14:32:53', '2026-05-28 14:32:53', '2026-06-22 14:32:53'),
(12, 1, 8, 'indirect', 101.00, 15.00, 'Commission indirect de Thomas Robert', NULL, NULL, 'paid', NULL, '2026-05-23 14:32:54', '2026-06-22 14:32:54'),
(13, 1, 7, 'leadership', 222.00, 10.00, 'Commission leadership de Emma Petit', NULL, NULL, 'paid', NULL, '2026-06-13 14:32:54', '2026-06-22 14:32:54'),
(14, 1, 6, 'retail', 221.00, 25.00, 'Commission retail de Lucas Bernard', NULL, NULL, 'paid', '2026-06-11 14:32:54', '2026-06-16 14:32:54', '2026-06-22 14:32:54'),
(15, 1, 5, 'leadership', 61.00, 10.00, 'Commission leadership de Sophie Lefevre', NULL, NULL, 'pending', NULL, '2026-06-17 14:32:54', '2026-06-22 14:32:54'),
(16, 1, 16, 'indirect', 213.00, 15.00, 'Commission indirect de Philippe Garcia', NULL, NULL, 'pending', NULL, '2026-05-24 14:32:54', '2026-06-22 14:32:54'),
(17, 1, 15, 'indirect', 161.00, 15.00, 'Commission indirect de Catherine Michel', NULL, NULL, 'pending', NULL, '2026-06-08 14:32:54', '2026-06-22 14:32:54'),
(18, 1, 4, 'direct', 219.00, 30.00, 'Commission direct de Pierre Durand', NULL, NULL, 'paid', NULL, '2026-06-03 14:32:54', '2026-06-22 14:32:54'),
(19, 1, 15, 'leadership', 181.00, 10.00, 'Commission leadership de Catherine Michel', NULL, NULL, 'paid', NULL, '2026-06-04 14:32:54', '2026-06-22 14:32:54'),
(20, 1, 12, 'indirect', 24.00, 15.00, 'Commission indirect de Alexandre Laurent', NULL, NULL, 'pending', NULL, '2026-05-24 14:32:54', '2026-06-22 14:32:54'),
(21, 1, 14, 'indirect', 80.00, 15.00, 'Commission indirect de Michel Francois', NULL, NULL, 'paid', NULL, '2026-06-14 14:32:54', '2026-06-22 14:32:54'),
(22, 1, 6, 'retail', 163.00, 25.00, 'Commission retail de Lucas Bernard', NULL, NULL, 'pending', '2026-06-06 14:32:54', '2026-05-30 14:32:54', '2026-06-22 14:32:54'),
(23, 1, 13, 'retail', 168.00, 25.00, 'Commission retail de Isabelle Simon', NULL, NULL, 'pending', '2026-06-04 14:32:54', '2026-06-14 14:32:54', '2026-06-22 14:32:54'),
(24, 1, 11, 'direct', 261.00, 30.00, 'Commission direct de Camille Moreau', NULL, NULL, 'paid', NULL, '2026-06-16 14:32:54', '2026-06-22 14:32:54'),
(25, 1, 5, 'indirect', 216.00, 15.00, 'Commission indirect de Sophie Lefevre', NULL, NULL, 'pending', '2026-06-17 14:32:54', '2026-06-13 14:32:54', '2026-06-22 14:32:54'),
(26, 2, 16, 'leadership', 29.00, 30.00, 'Commission de Philippe Garcia', NULL, NULL, 'paid', '2026-06-19 14:32:54', '2026-06-07 14:32:54', '2026-06-22 14:32:54'),
(27, 2, 15, 'direct', 156.00, 30.00, 'Commission de Catherine Michel', NULL, NULL, 'paid', '2026-06-05 14:32:54', '2026-06-20 14:32:54', '2026-06-22 14:32:54'),
(28, 3, 6, 'direct', 128.00, 30.00, 'Commission de Lucas Bernard', NULL, NULL, 'paid', '2026-06-14 14:32:54', '2026-06-10 14:32:54', '2026-06-22 14:32:54'),
(29, 3, 13, 'leadership', 104.00, 30.00, 'Commission de Isabelle Simon', NULL, NULL, 'paid', '2026-06-15 14:32:54', '2026-06-20 14:32:54', '2026-06-22 14:32:54'),
(30, 3, 7, 'direct', 126.00, 30.00, 'Commission de Emma Petit', NULL, NULL, 'paid', '2026-06-11 14:32:55', '2026-06-08 14:32:55', '2026-06-22 14:32:55'),
(31, 4, 16, 'retail', 95.00, 30.00, 'Commission de Philippe Garcia', NULL, NULL, 'paid', '2026-06-10 14:32:55', '2026-06-07 14:32:55', '2026-06-22 14:32:55'),
(32, 4, 11, 'direct', 194.00, 30.00, 'Commission de Camille Moreau', NULL, NULL, 'paid', '2026-06-07 14:32:55', '2026-06-06 14:32:55', '2026-06-22 14:32:55'),
(33, 4, 11, 'indirect', 170.00, 30.00, 'Commission de Camille Moreau', NULL, NULL, 'paid', '2026-06-09 14:32:55', '2026-06-14 14:32:55', '2026-06-22 14:32:55'),
(34, 5, 12, 'indirect', 169.00, 30.00, 'Commission de Alexandre Laurent', NULL, NULL, 'paid', '2026-06-05 14:32:55', '2026-06-07 14:32:55', '2026-06-22 14:32:55'),
(35, 5, 9, 'direct', 82.00, 30.00, 'Commission de Julie Richard', NULL, NULL, 'paid', '2026-06-08 14:32:55', '2026-06-06 14:32:55', '2026-06-22 14:32:55'),
(36, 5, 16, 'retail', 122.00, 30.00, 'Commission de Philippe Garcia', NULL, NULL, 'paid', '2026-06-18 14:32:55', '2026-06-16 14:32:55', '2026-06-22 14:32:55'),
(37, 6, 5, 'indirect', 63.00, 30.00, 'Commission de Sophie Lefevre', NULL, NULL, 'paid', '2026-06-14 14:32:55', '2026-06-06 14:32:55', '2026-06-22 14:32:55'),
(38, 6, 5, 'indirect', 142.00, 30.00, 'Commission de Sophie Lefevre', NULL, NULL, 'paid', '2026-06-10 14:32:55', '2026-06-13 14:32:55', '2026-06-22 14:32:55'),
(39, 7, 10, 'indirect', 102.00, 30.00, 'Commission de Nicolas Dubois', NULL, NULL, 'paid', '2026-06-17 14:32:55', '2026-06-08 14:32:55', '2026-06-22 14:32:55'),
(40, 7, 3, 'retail', 85.00, 30.00, 'Commission de Marie Martin', NULL, NULL, 'paid', '2026-06-06 14:32:55', '2026-06-03 14:32:55', '2026-06-22 14:32:55'),
(41, 8, 13, 'direct', 73.00, 30.00, 'Commission de Isabelle Simon', NULL, NULL, 'paid', '2026-06-06 14:32:55', '2026-06-18 14:32:55', '2026-06-22 14:32:55'),
(42, 8, 3, 'leadership', 99.00, 30.00, 'Commission de Marie Martin', NULL, NULL, 'paid', '2026-06-04 14:32:55', '2026-06-21 14:32:55', '2026-06-22 14:32:55'),
(43, 8, 11, 'retail', 27.00, 30.00, 'Commission de Camille Moreau', NULL, NULL, 'paid', '2026-06-19 14:32:55', '2026-06-13 14:32:55', '2026-06-22 14:32:55'),
(44, 9, 10, 'leadership', 92.00, 30.00, 'Commission de Nicolas Dubois', NULL, NULL, 'paid', '2026-06-14 14:32:55', '2026-06-12 14:32:55', '2026-06-22 14:32:55'),
(45, 9, 16, 'leadership', 188.00, 30.00, 'Commission de Philippe Garcia', NULL, NULL, 'paid', '2026-06-17 14:32:55', '2026-06-07 14:32:55', '2026-06-22 14:32:55'),
(46, 9, 15, 'indirect', 83.00, 30.00, 'Commission de Catherine Michel', NULL, NULL, 'paid', '2026-06-17 14:32:55', '2026-06-10 14:32:55', '2026-06-22 14:32:55'),
(47, 10, 2, 'leadership', 49.00, 30.00, 'Commission de Jean Dupont', NULL, NULL, 'paid', '2026-06-19 14:32:55', '2026-06-21 14:32:55', '2026-06-22 14:32:55'),
(48, 10, 15, 'retail', 142.00, 30.00, 'Commission de Catherine Michel', NULL, NULL, 'paid', '2026-06-07 14:32:55', '2026-06-20 14:32:55', '2026-06-22 14:32:55'),
(49, 10, 7, 'retail', 158.00, 30.00, 'Commission de Emma Petit', NULL, NULL, 'paid', '2026-06-16 14:32:55', '2026-06-16 14:32:55', '2026-06-22 14:32:55'),
(50, 11, 6, 'direct', 39.00, 30.00, 'Commission de Lucas Bernard', NULL, NULL, 'paid', '2026-06-13 14:32:55', '2026-06-06 14:32:55', '2026-06-22 14:32:55'),
(51, 11, 5, 'retail', 176.00, 30.00, 'Commission de Sophie Lefevre', NULL, NULL, 'paid', '2026-06-21 14:32:56', '2026-06-20 14:32:56', '2026-06-22 14:32:56'),
(52, 11, 6, 'retail', 166.00, 30.00, 'Commission de Lucas Bernard', NULL, NULL, 'paid', '2026-06-06 14:32:56', '2026-06-09 14:32:56', '2026-06-22 14:32:56'),
(53, 12, 16, 'retail', 54.00, 30.00, 'Commission de Philippe Garcia', NULL, NULL, 'paid', '2026-06-07 14:32:56', '2026-06-10 14:32:56', '2026-06-22 14:32:56'),
(54, 12, 4, 'leadership', 136.00, 30.00, 'Commission de Pierre Durand', NULL, NULL, 'paid', '2026-06-02 14:32:56', '2026-06-03 14:32:56', '2026-06-22 14:32:56'),
(55, 12, 2, 'retail', 82.00, 30.00, 'Commission de Jean Dupont', NULL, NULL, 'paid', '2026-06-21 14:32:56', '2026-06-04 14:32:56', '2026-06-22 14:32:56'),
(56, 13, 8, 'retail', 148.00, 30.00, 'Commission de Thomas Robert', NULL, NULL, 'paid', '2026-06-17 14:32:56', '2026-06-05 14:32:56', '2026-06-22 14:32:56'),
(57, 13, 5, 'retail', 136.00, 30.00, 'Commission de Sophie Lefevre', NULL, NULL, 'paid', '2026-06-14 14:32:56', '2026-06-17 14:32:56', '2026-06-22 14:32:56'),
(58, 13, 6, 'retail', 31.00, 30.00, 'Commission de Lucas Bernard', NULL, NULL, 'paid', '2026-06-15 14:32:56', '2026-06-07 14:32:56', '2026-06-22 14:32:56'),
(59, 14, 16, 'leadership', 46.00, 30.00, 'Commission de Philippe Garcia', NULL, NULL, 'paid', '2026-06-16 14:32:56', '2026-06-12 14:32:56', '2026-06-22 14:32:56'),
(60, 14, 12, 'leadership', 184.00, 30.00, 'Commission de Alexandre Laurent', NULL, NULL, 'paid', '2026-06-19 14:32:56', '2026-06-03 14:32:56', '2026-06-22 14:32:56'),
(61, 14, 3, 'retail', 44.00, 30.00, 'Commission de Marie Martin', NULL, NULL, 'paid', '2026-06-11 14:32:56', '2026-06-18 14:32:56', '2026-06-22 14:32:56'),
(62, 15, 11, 'leadership', 192.00, 30.00, 'Commission de Camille Moreau', NULL, NULL, 'paid', '2026-06-10 14:32:56', '2026-06-08 14:32:56', '2026-06-22 14:32:56'),
(63, 15, 5, 'direct', 10.00, 30.00, 'Commission de Sophie Lefevre', NULL, NULL, 'paid', '2026-06-16 14:32:56', '2026-06-17 14:32:56', '2026-06-22 14:32:56'),
(64, 15, 9, 'direct', 136.00, 30.00, 'Commission de Julie Richard', NULL, NULL, 'paid', '2026-06-06 14:32:56', '2026-06-03 14:32:56', '2026-06-22 14:32:56'),
(65, 16, 5, 'retail', 19.00, 30.00, 'Commission de Sophie Lefevre', NULL, NULL, 'paid', '2026-06-09 14:32:56', '2026-06-21 14:32:56', '2026-06-22 14:32:56'),
(66, 16, 9, 'retail', 187.00, 30.00, 'Commission de Julie Richard', NULL, NULL, 'paid', '2026-06-17 14:32:56', '2026-06-08 14:32:56', '2026-06-22 14:32:56'),
(67, 16, 5, 'direct', 116.00, 30.00, 'Commission de Sophie Lefevre', NULL, NULL, 'paid', '2026-06-04 14:32:56', '2026-06-10 14:32:56', '2026-06-22 14:32:56'),
(68, 1, 22, 'indirect', 20.00, 15.00, 'Commission indirect de Emma Petit', NULL, NULL, 'paid', '2026-06-14 14:34:53', '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(69, 1, 26, 'leadership', 48.00, 10.00, 'Commission leadership de Camille Moreau', NULL, NULL, 'pending', '2026-06-17 14:34:53', '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(70, 1, 18, 'retail', 91.00, 25.00, 'Commission retail de Marie Martin', NULL, NULL, 'paid', NULL, '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(71, 1, 17, 'direct', 112.00, 30.00, 'Commission direct de Jean Dupont', NULL, NULL, 'paid', '2026-05-31 14:34:53', '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(72, 1, 20, 'indirect', 258.00, 15.00, 'Commission indirect de Sophie Lefevre', NULL, NULL, 'paid', '2026-06-05 14:34:53', '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(73, 1, 24, 'direct', 134.00, 30.00, 'Commission direct de Julie Richard', NULL, NULL, 'paid', NULL, '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(74, 1, 25, 'direct', 30.00, 30.00, 'Commission direct de Nicolas Dubois', NULL, NULL, 'pending', NULL, '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(75, 1, 26, 'indirect', 193.00, 15.00, 'Commission indirect de Camille Moreau', NULL, NULL, 'pending', NULL, '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(76, 1, 26, 'direct', 97.00, 30.00, 'Commission direct de Camille Moreau', NULL, NULL, 'paid', NULL, '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(77, 1, 23, 'leadership', 90.00, 10.00, 'Commission leadership de Thomas Robert', NULL, NULL, 'paid', '2026-05-31 14:34:53', '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(78, 1, 26, 'indirect', 179.00, 15.00, 'Commission indirect de Camille Moreau', NULL, NULL, 'pending', NULL, '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(79, 1, 20, 'direct', 219.00, 30.00, 'Commission direct de Sophie Lefevre', NULL, NULL, 'paid', NULL, '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(80, 1, 26, 'leadership', 214.00, 10.00, 'Commission leadership de Camille Moreau', NULL, NULL, 'paid', NULL, '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(81, 1, 23, 'direct', 241.00, 30.00, 'Commission direct de Thomas Robert', NULL, NULL, 'paid', '2026-06-10 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(82, 1, 17, 'leadership', 180.00, 10.00, 'Commission leadership de Jean Dupont', NULL, NULL, 'paid', NULL, '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(83, 1, 17, 'direct', 234.00, 30.00, 'Commission direct de Jean Dupont', NULL, NULL, 'paid', '2026-05-27 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(84, 1, 21, 'leadership', 245.00, 10.00, 'Commission leadership de Lucas Bernard', NULL, NULL, 'paid', NULL, '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(85, 1, 23, 'direct', 42.00, 30.00, 'Commission direct de Thomas Robert', NULL, NULL, 'pending', '2026-06-05 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(86, 1, 20, 'leadership', 68.00, 10.00, 'Commission leadership de Sophie Lefevre', NULL, NULL, 'pending', NULL, '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(87, 1, 19, 'retail', 126.00, 25.00, 'Commission retail de Pierre Durand', NULL, NULL, 'paid', NULL, '2026-06-22 14:34:54', '2026-06-22 14:34:54');

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `genealogy`
--

CREATE TABLE `genealogy` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `sponsor_id` bigint UNSIGNED DEFAULT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `level` int NOT NULL DEFAULT '0',
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `left_count` int NOT NULL DEFAULT '0',
  `right_count` int NOT NULL DEFAULT '0',
  `total_children` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `genealogy`
--

INSERT INTO `genealogy` (`id`, `user_id`, `sponsor_id`, `parent_id`, `level`, `position`, `left_count`, `right_count`, `total_children`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 0, NULL, 0, 0, 0, '2026-06-22 14:32:45', '2026-06-22 14:32:45'),
(2, 2, 1, 1, 1, NULL, 0, 0, 2, '2026-06-22 14:32:46', '2026-06-22 14:32:46'),
(3, 3, 1, 1, 1, NULL, 0, 0, 3, '2026-06-22 14:32:46', '2026-06-22 14:32:46'),
(4, 4, 1, 1, 1, NULL, 0, 0, 4, '2026-06-22 14:32:47', '2026-06-22 14:32:47'),
(5, 5, 1, 1, 1, NULL, 0, 0, 4, '2026-06-22 14:32:47', '2026-06-22 14:32:47'),
(6, 6, 1, 1, 1, NULL, 0, 0, 2, '2026-06-22 14:32:48', '2026-06-22 14:32:48'),
(7, 7, 1, 1, 1, NULL, 0, 0, 3, '2026-06-22 14:32:48', '2026-06-22 14:32:48'),
(8, 8, 1, 1, 1, NULL, 0, 0, 0, '2026-06-22 14:32:49', '2026-06-22 14:32:49'),
(9, 9, 2, 2, 2, NULL, 0, 0, 5, '2026-06-22 14:32:49', '2026-06-22 14:32:49'),
(10, 10, 2, 2, 2, NULL, 0, 0, 4, '2026-06-22 14:32:50', '2026-06-22 14:32:50'),
(11, 11, 2, 2, 2, NULL, 0, 0, 3, '2026-06-22 14:32:50', '2026-06-22 14:32:50'),
(12, 12, 2, 2, 2, NULL, 0, 0, 4, '2026-06-22 14:32:51', '2026-06-22 14:32:51'),
(13, 13, 2, 2, 2, NULL, 0, 0, 0, '2026-06-22 14:32:51', '2026-06-22 14:32:51'),
(14, 14, 2, 2, 2, NULL, 0, 0, 2, '2026-06-22 14:32:52', '2026-06-22 14:32:52'),
(15, 15, 2, 2, 2, NULL, 0, 0, 0, '2026-06-22 14:32:52', '2026-06-22 14:32:52'),
(16, 16, 2, 2, 2, NULL, 0, 0, 0, '2026-06-22 14:32:53', '2026-06-22 14:32:53'),
(17, 17, 1, 1, 1, NULL, 0, 0, 0, '2026-06-22 14:34:49', '2026-06-22 14:34:49'),
(18, 18, 1, 1, 1, NULL, 0, 0, 0, '2026-06-22 14:34:49', '2026-06-22 14:34:49'),
(19, 19, 1, 1, 1, NULL, 0, 0, 0, '2026-06-22 14:34:50', '2026-06-22 14:34:50'),
(20, 20, 1, 1, 1, NULL, 0, 0, 0, '2026-06-22 14:34:50', '2026-06-22 14:34:50'),
(21, 21, 1, 1, 1, NULL, 0, 0, 0, '2026-06-22 14:34:50', '2026-06-22 14:34:50'),
(22, 22, 1, 1, 1, NULL, 0, 0, 0, '2026-06-22 14:34:51', '2026-06-22 14:34:51'),
(23, 23, 1, 1, 1, NULL, 0, 0, 0, '2026-06-22 14:34:51', '2026-06-22 14:34:51'),
(24, 24, 1, 1, 1, NULL, 0, 0, 0, '2026-06-22 14:34:52', '2026-06-22 14:34:52'),
(25, 25, 1, 1, 1, NULL, 0, 0, 0, '2026-06-22 14:34:52', '2026-06-22 14:34:52'),
(26, 26, 1, 1, 1, NULL, 0, 0, 0, '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(27, 27, 1, 1, 1, NULL, 0, 0, 0, '2026-06-22 18:07:10', '2026-06-22 18:07:10');

-- --------------------------------------------------------

--
-- Structure de la table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kyc_documents`
--

CREATE TABLE `kyc_documents` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `document_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'id_card, passport, proof_of_address, selfie',
  `document_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending, verified, rejected',
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_22_000000_create_permissions_table', 1),
(5, '2026_06_22_000000_create_ranks_table', 1),
(6, '2026_06_22_000001_create_packages_table', 1),
(7, '2026_06_22_000001_create_roles_table', 1),
(8, '2026_06_22_000002_create_role_has_permissions_table', 1),
(9, '2026_06_22_000003_create_model_has_roles_table', 1),
(10, '2026_06_22_000003_create_products_table', 1),
(11, '2026_06_22_000004_create_model_has_permissions_table', 1),
(12, '2026_06_22_000004_create_orders_table', 1),
(13, '2026_06_22_000005_create_order_items_table', 1),
(14, '2026_06_22_000006_create_commissions_table', 1),
(15, '2026_06_22_000007_create_wallets_table', 1),
(16, '2026_06_22_000008_create_transactions_table', 1),
(17, '2026_06_22_000009_create_withdrawals_table', 1),
(18, '2026_06_22_000010_create_genealogy_table', 1),
(19, '2026_06_22_000011_create_rank_history_table', 1),
(20, '2026_06_22_000012_add_mlm_fields_to_users_table', 1);

-- --------------------------------------------------------

--
-- Structure de la table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 3),
(2, 'App\\Models\\User', 4),
(2, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 6),
(2, 'App\\Models\\User', 7),
(2, 'App\\Models\\User', 8),
(2, 'App\\Models\\User', 9),
(2, 'App\\Models\\User', 10),
(2, 'App\\Models\\User', 11),
(2, 'App\\Models\\User', 12),
(2, 'App\\Models\\User', 13),
(2, 'App\\Models\\User', 14),
(2, 'App\\Models\\User', 15),
(2, 'App\\Models\\User', 16),
(2, 'App\\Models\\User', 17),
(2, 'App\\Models\\User', 18),
(2, 'App\\Models\\User', 19),
(2, 'App\\Models\\User', 20),
(2, 'App\\Models\\User', 21),
(2, 'App\\Models\\User', 22),
(2, 'App\\Models\\User', 23),
(2, 'App\\Models\\User', 24),
(2, 'App\\Models\\User', 25),
(2, 'App\\Models\\User', 26);

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `tax` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shipping` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` text COLLATE utf8mb4_unicode_ci,
  `billing_address` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `subtotal`, `tax`, `shipping`, `discount`, `total`, `status`, `payment_status`, `payment_method`, `shipping_address`, `billing_address`, `metadata`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'ORD-6A42B86CD55EE', 1398.00, 251.64, 0.00, 0.00, 1649.64, 'cancelled', 'pending', NULL, NULL, NULL, NULL, NULL, '2026-06-29 16:24:44', '2026-06-29 16:24:51');

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `package_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `price` decimal(15,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `options` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `package_id`, `name`, `sku`, `quantity`, `price`, `total`, `options`, `created_at`, `updated_at`) VALUES
(1, 1, 21, NULL, 'Apple Watch Ultra 2', NULL, 1, 899.00, 899.00, NULL, '2026-06-29 16:24:44', '2026-06-29 16:24:44'),
(2, 1, 22, NULL, 'Apple Watch Series 9', NULL, 1, 499.00, 499.00, NULL, '2026-06-29 16:24:45', '2026-06-29 16:24:45');

-- --------------------------------------------------------

--
-- Structure de la table `packages`
--

CREATE TABLE `packages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `pv_value` int NOT NULL DEFAULT '0',
  `bv_value` int NOT NULL DEFAULT '0',
  `commission_rate` decimal(5,2) NOT NULL DEFAULT '30.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `benefits` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `packages`
--

INSERT INTO `packages` (`id`, `name`, `slug`, `price`, `pv_value`, `bv_value`, `commission_rate`, `description`, `benefits`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Starter', 'starter', 30.00, 0, 0, 30.00, NULL, NULL, 1, NULL, NULL),
(2, 'Silver', 'silver', 85.00, 0, 0, 30.00, NULL, NULL, 1, NULL, NULL),
(3, 'Bronze', 'bronze', 350.00, 200, 200, 30.00, NULL, NULL, 1, NULL, NULL),
(4, 'Gold', 'gold', 1450.00, 1000, 1000, 30.00, NULL, NULL, 1, NULL, NULL),
(5, 'Emerald', 'emerald', 4850.00, 3800, 3800, 30.00, NULL, NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage users', 'web', '2026-06-22 14:32:44', '2026-06-22 14:32:44'),
(2, 'manage packages', 'web', '2026-06-22 14:32:44', '2026-06-22 14:32:44'),
(3, 'manage products', 'web', '2026-06-22 14:32:44', '2026-06-22 14:32:44'),
(4, 'manage commissions', 'web', '2026-06-22 14:32:44', '2026-06-22 14:32:44'),
(5, 'manage wallets', 'web', '2026-06-22 14:32:45', '2026-06-22 14:32:45'),
(6, 'view reports', 'web', '2026-06-22 14:32:45', '2026-06-22 14:32:45');

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(15,2) NOT NULL,
  `cost` decimal(15,2) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `price`, `cost`, `stock`, `sku`, `category`, `image`, `gallery`, `is_active`, `is_featured`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 'MacBook Pro 16\" M3', 'macbook-pro-16-m3', 'Le MacBook Pro 16 pouces avec puce M3 Pro, parfait pour les professionnels exigeants', 2999.00, 2500.00, 15, 'MBP-16-M3-001', 'Informatique', '1782465218_macbook-pro-16-m3.jpg', NULL, 1, 1, NULL, '2026-06-25 14:16:45', '2026-06-26 07:13:39'),
(2, 'MacBook Air 13\" M3', 'macbook-air-13-m3', 'Le MacBook Air léger et puissant avec puce M3', 1299.00, 1000.00, 25, 'MBA-13-M3-002', 'Informatique', '1782467377_macbook-air-13-m3.jpg', NULL, 1, 1, NULL, '2026-06-25 14:16:45', '2026-06-26 07:49:37'),
(3, 'iMac 24\" M3', 'imac-24-m3', 'L\'iMac tout-en-un avec écran 4.5K et puce M3', 1499.00, 1200.00, 10, 'IMC-24-M3-003', 'Informatique', '1782467436_imac-24-m3.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:50:36'),
(4, 'Mac mini M2 Pro', 'mac-mini-m2-pro', 'Le Mac mini compact avec puce M2 Pro', 799.00, 650.00, 20, 'MMI-M2P-004', 'Informatique', '1782467464_mac-mini-m2-pro.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:51:04'),
(5, 'Studio Display 27\"', 'studio-display-27', 'Écran 5K Retina pour les professionnels', 1599.00, 1200.00, 8, 'STD-27-005', 'Informatique', '1782467490_studio-display-27.jpg', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:51:30'),
(6, 'iPhone 15 Pro Max', 'iphone-15-pro-max', 'Le meilleur iPhone avec écran 6.7\", puce A17 Pro et batterie longue durée', 1499.00, 1200.00, 30, 'IPH-15PM-006', 'Téléphonie', '1782467585_iphone-15-pro-max.jpg', NULL, 1, 1, NULL, '2026-06-25 14:16:45', '2026-06-26 07:53:05'),
(7, 'iPhone 15 Pro', 'iphone-15-pro', 'iPhone 15 Pro avec écran 6.1\" et puce A17 Pro', 1299.00, 1050.00, 35, 'IPH-15P-007', 'Téléphonie', '1782465242_iphone-15-pro.png', NULL, 1, 1, NULL, '2026-06-25 14:16:45', '2026-06-26 07:14:02'),
(8, 'iPhone 15', 'iphone-15', 'iPhone 15 avec écran 6.1\" et puce A16 Bionic', 999.00, 800.00, 40, 'IPH-15-008', 'Téléphonie', '1782465324_iphone-15.png', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:15:24'),
(9, 'iPhone 15 Plus', 'iphone-15-plus', 'iPhone 15 Plus avec écran 6.7\"', 1099.00, 880.00, 25, 'IPH-15P-009', 'Téléphonie', '1782465354_iphone-15-plus.png', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:15:54'),
(10, 'iPhone 14 Pro', 'iphone-14-pro', 'iPhone 14 Pro avec écran 6.1\" et puce A16', 1099.00, 880.00, 20, 'IPH-14P-010', 'Téléphonie', '1782464291_iphone-14-pro.jpg', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 06:58:11'),
(11, 'AirPods Pro 2', 'airpods-pro-2', 'Écouteurs sans fil avec réduction de bruit active et audio spatial', 279.00, 200.00, 50, 'APP-2-011', 'Audio', '1782467622_airpods-pro-2.jpeg', NULL, 1, 1, NULL, '2026-06-25 14:16:45', '2026-06-26 07:53:42'),
(12, 'AirPods Max', 'airpods-max', 'Casque audio haut de gamme avec réduction de bruit', 549.00, 400.00, 20, 'APM-012', 'Audio', '1782467642_airpods-max.jpg', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:54:02'),
(13, 'AirPods 3', 'airpods-3', 'Écouteurs sans fil avec audio spatial', 199.00, 150.00, 45, 'AP3-013', 'Audio', '1782467664_airpods-3.jpg', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:54:24'),
(14, 'Beats Studio Buds', 'beats-studio-buds', 'Écouteurs sans fil avec suppression de bruit', 199.00, 150.00, 30, 'BSB-014', 'Audio', '1782467682_beats-studio-buds.jpg', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:54:42'),
(15, 'HomePod mini', 'homepod-mini', 'Enceinte intelligente compacte', 109.00, 80.00, 25, 'HPM-015', 'Audio', '1782467705_homepod-mini.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:55:05'),
(16, 'iPad Pro 12.9\" M2', 'ipad-pro-129-m2', 'L\'iPad Pro avec écran XDR et puce M2', 1299.00, 1000.00, 15, 'IPP-129-016', 'Tablette', '1782467736_ipad-pro-129-m2.jpeg', NULL, 1, 1, NULL, '2026-06-25 14:16:45', '2026-06-26 07:55:36'),
(17, 'iPad Pro 11\" M2', 'ipad-pro-11-m2', 'L\'iPad Pro compact avec puce M2', 999.00, 750.00, 20, 'IPP-11-017', 'Tablette', '1782467763_ipad-pro-11-m2.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:56:03'),
(18, 'iPad Air 5', 'ipad-air-5', 'iPad Air avec puce M1', 799.00, 600.00, 25, 'IPA-5-018', 'Tablette', '1782467146_ipad-air-5.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:45:46'),
(19, 'iPad 10e', 'ipad-10e', 'iPad 10e génération', 499.00, 380.00, 30, 'IPD-10-019', 'Tablette', '1782467319_ipad-10e.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:48:39'),
(20, 'iPad mini 6', 'ipad-mini-6', 'iPad mini avec écran 8.3\"', 599.00, 450.00, 18, 'IPM-6-020', 'Tablette', '1782467343_ipad-mini-6.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:45', '2026-06-26 07:49:03'),
(21, 'Apple Watch Ultra 2', 'apple-watch-ultra-2', 'La montre connectée la plus robuste avec écran toujours allumé', 899.00, 700.00, 12, 'AWU-2-021', 'Montres', '1782465713_apple-watch-ultra-2.jpeg', NULL, 1, 1, NULL, '2026-06-25 14:16:46', '2026-06-29 16:24:51'),
(22, 'Apple Watch Series 9', 'apple-watch-series-9', 'Apple Watch Series 9 avec puce S9', 499.00, 380.00, 25, 'AWS-9-022', 'Montres', '1782465761_apple-watch-series-9.jpeg', NULL, 1, 1, NULL, '2026-06-25 14:16:46', '2026-06-29 16:24:51'),
(23, 'Apple Watch SE 2', 'apple-watch-se-2', 'Apple Watch SE 2e génération', 299.00, 220.00, 30, 'AWSE-2-023', 'Montres', '1782465800_apple-watch-se-2.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 07:23:20'),
(24, 'Apple Watch Hermès', 'apple-watch-hermes', 'Apple Watch Series 9 Hermès', 1299.00, 1000.00, 5, 'AWH-024', 'Montres', '1782465866_apple-watch-hermes.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 07:24:26'),
(25, 'Apple Watch Nike', 'apple-watch-nike', 'Apple Watch Series 9 Nike', 499.00, 380.00, 15, 'AWN-025', 'Montres', '1782465914_apple-watch-nike.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 07:25:14'),
(26, 'Magic Keyboard', 'magic-keyboard', 'Clavier Magic avec Touch ID', 149.00, 100.00, 40, 'MK-026', 'Accessoires', '1782468349_magic-keyboard.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 08:05:49'),
(27, 'Magic Mouse', 'magic-mouse', 'Souris Magic rechargeable', 99.00, 70.00, 45, 'MM-027', 'Accessoires', '1782467999_magic-mouse.jpg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 07:59:59'),
(28, 'Magic Trackpad', 'magic-trackpad', 'Trackpad Magic rechargeable', 129.00, 90.00, 30, 'MT-028', 'Accessoires', '1782466018_magic-trackpad.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 07:26:58'),
(29, 'Apple Pencil 2', 'apple-pencil-2', 'Stylet pour iPad Pro et iPad Air', 139.00, 100.00, 35, 'AP-2-029', 'Accessoires', '1782468759_apple-pencil-2.jpg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 08:12:40'),
(30, 'AirTag', 'airtag', 'Localisateur d\'objets', 39.00, 25.00, 60, 'AT-030', 'Accessoires', '1782467192_airtag.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 07:46:32'),
(31, 'Coque iPhone 15 Pro', 'coque-iphone-15-pro', 'Coque de protection iPhone 15 Pro', 49.00, 30.00, 50, 'CIP-15P-031', 'Accessoires', '1782467216_coque-iphone-15-pro.jpg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 07:46:56'),
(32, 'Protecteur écran iPhone', 'protecteur-ecran-iphone', 'Protecteur d\'écran en verre trempé', 29.00, 15.00, 70, 'PEI-032', 'Accessoires', '1782467235_protecteur-ecran-iphone.jpg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 07:47:15'),
(33, 'Chargeur sans fil MagSafe', 'chargeur-sans-fil-magsafe', 'Chargeur sans fil MagSafe 15W', 49.00, 30.00, 40, 'CSM-033', 'Accessoires', '1782467261_chargeur-sans-fil-magsafe.jpg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 07:47:41'),
(34, 'AppleCare+ MacBook Pro', 'applecare-macbook-pro', 'Extension de garantie pour MacBook Pro', 399.00, 300.00, 999, 'ACM-034', 'Services', '1782466094_applecare-macbook-pro.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 07:28:14'),
(35, 'AppleCare+ iPhone', 'applecare-iphone', 'Extension de garantie pour iPhone', 199.00, 150.00, 999, 'ACI-035', 'Services', '1782467295_applecare-iphone.jpg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 07:48:15'),
(36, 'AppleCare+ iPad', 'applecare-ipad', 'Extension de garantie pour iPad', 99.00, 70.00, 999, 'ACI-036', 'Services', '1782465664_applecare-ipad.jpeg', NULL, 1, 0, NULL, '2026-06-25 14:16:46', '2026-06-26 07:21:04');

-- --------------------------------------------------------

--
-- Structure de la table `ranks`
--

CREATE TABLE `ranks` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_pv` int NOT NULL DEFAULT '0',
  `min_bv` int NOT NULL DEFAULT '0',
  `min_sponsors` int NOT NULL DEFAULT '0',
  `min_team` int NOT NULL DEFAULT '0',
  `bonus_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ranks`
--

INSERT INTO `ranks` (`id`, `name`, `slug`, `min_pv`, `min_bv`, `min_sponsors`, `min_team`, `bonus_percentage`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Distributor', 'distributor', 0, 0, 0, 0, 0.00, 1, NULL, NULL),
(2, 'Supervisor', 'supervisor', 100, 0, 0, 0, 5.00, 1, NULL, NULL),
(3, 'Assistant Manager', 'assistant-manager', 200, 0, 0, 0, 10.00, 1, NULL, NULL),
(4, 'Manager', 'manager', 500, 0, 0, 0, 15.00, 1, NULL, NULL),
(5, 'Senior Manager', 'senior-manager', 1000, 0, 0, 0, 20.00, 1, NULL, NULL),
(6, 'Soaring Manager', 'soaring-manager', 2000, 0, 0, 0, 25.00, 1, NULL, NULL),
(7, 'Sapphire Manager', 'sapphire-manager', 5000, 0, 0, 0, 30.00, 1, NULL, NULL),
(8, 'Blue Diamond', 'blue-diamond', 10000, 0, 0, 0, 35.00, 1, NULL, NULL),
(9, 'Diamond', 'diamond', 20000, 0, 0, 0, 40.00, 1, NULL, NULL),
(10, 'Pearl', 'pearl', 50000, 0, 0, 0, 50.00, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `rank_history`
--

CREATE TABLE `rank_history` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `old_rank_id` bigint UNSIGNED DEFAULT NULL,
  `new_rank_id` bigint UNSIGNED NOT NULL,
  `old_rank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_rank_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pv_at_time` int NOT NULL DEFAULT '0',
  `bv_at_time` int NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2026-06-22 14:32:45', '2026-06-22 14:32:45'),
(2, 'user', 'web', '2026-06-22 14:32:45', '2026-06-22 14:32:45');

-- --------------------------------------------------------

--
-- Structure de la table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1);

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('fncx20cQ94WkS9kda4LXjZhxxufqojzatEgxH2JY', NULL, '192.168.43.65', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_5_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/149.0.7827.137 Mobile/15E148 Safari/604.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiT3dKYnM2d2NtWWg4aXM5Mk82cjhmWU5yb0xVY05LSkU3SHFrd2tnayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xOTIuMTY4LjQzLjYwOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YToxOntzOjg6ImludGVuZGVkIjtzOjM1OiJodHRwOi8vMTkyLjE2OC40My42MDo4MDAwL2Rhc2hib2FyZCI7fX0=', 1782821897),
('VIOvq4S1XgoCVKxXSoMssskA0VxoUI0chwz1lc0e', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicUVzcUl1MklxQjVRRGFNUk1HWExTVXJERnBwNVFlQkZVQkxRblc5NyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1782822887);

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `wallet_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `fee` decimal(15,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `wallet_id`, `type`, `amount`, `fee`, `net_amount`, `balance_before`, `balance_after`, `status`, `reference`, `description`, `metadata`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'withdrawal', 376.00, 9.40, 366.60, 1306.00, 2199.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-15 14:32:56', '2026-06-15 14:32:56', '2026-06-22 14:32:56'),
(2, 1, 1, 'deposit', 457.00, 0.00, 457.00, 223.00, 659.00, 'completed', NULL, 'Deposit transaction', NULL, '2026-06-10 14:32:56', '2026-06-13 14:32:56', '2026-06-22 14:32:56'),
(3, 1, 1, 'purchase', 340.00, 0.00, 340.00, 128.00, 1845.00, 'completed', NULL, 'Purchase transaction', NULL, '2026-06-16 14:32:57', '2026-06-15 14:32:57', '2026-06-22 14:32:57'),
(4, 1, 1, 'purchase', 193.00, 0.00, 193.00, 722.00, 1763.00, 'completed', NULL, 'Purchase transaction', NULL, '2026-06-14 14:32:57', '2026-06-07 14:32:57', '2026-06-22 14:32:57'),
(5, 1, 1, 'purchase', 453.00, 0.00, 453.00, 476.00, 1336.00, 'completed', NULL, 'Purchase transaction', NULL, '2026-06-13 14:32:57', '2026-06-17 14:32:57', '2026-06-22 14:32:57'),
(6, 1, 1, 'purchase', 432.00, 0.00, 432.00, 608.00, 2183.00, 'completed', NULL, 'Purchase transaction', NULL, '2026-06-13 14:32:57', '2026-06-10 14:32:57', '2026-06-22 14:32:57'),
(7, 1, 1, 'withdrawal', 464.00, 11.60, 452.40, 1766.00, 476.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-19 14:32:57', '2026-06-19 14:32:57', '2026-06-22 14:32:57'),
(8, 1, 1, 'purchase', 393.00, 0.00, 393.00, 305.00, 636.00, 'completed', NULL, 'Purchase transaction', NULL, '2026-06-18 14:32:57', '2026-06-18 14:32:57', '2026-06-22 14:32:57'),
(9, 1, 1, 'deposit', 464.00, 0.00, 464.00, 293.00, 609.00, 'completed', NULL, 'Deposit transaction', NULL, '2026-06-16 14:32:57', '2026-06-12 14:32:57', '2026-06-22 14:32:57'),
(10, 1, 1, 'withdrawal', 469.00, 11.73, 457.28, 229.00, 1754.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-11 14:32:57', '2026-06-13 14:32:57', '2026-06-22 14:32:57'),
(11, 1, 1, 'withdrawal', 38.00, 0.95, 37.05, 1215.00, 753.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-20 14:32:57', '2026-06-21 14:32:57', '2026-06-22 14:32:57'),
(12, 1, 1, 'commission', 194.00, 0.00, 194.00, 946.00, 208.00, 'completed', NULL, 'Commission transaction', NULL, '2026-06-10 14:32:57', '2026-06-21 14:32:57', '2026-06-22 14:32:57'),
(13, 1, 1, 'withdrawal', 235.00, 5.88, 229.13, 961.00, 2018.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-09 14:32:57', '2026-06-14 14:32:57', '2026-06-22 14:32:57'),
(14, 1, 1, 'deposit', 207.00, 0.00, 207.00, 1667.00, 988.00, 'completed', NULL, 'Deposit transaction', NULL, '2026-06-17 14:32:57', '2026-06-19 14:32:57', '2026-06-22 14:32:57'),
(15, 1, 1, 'commission', 286.00, 0.00, 286.00, 310.00, 1407.00, 'completed', NULL, 'Commission transaction', NULL, '2026-06-18 14:32:57', '2026-06-21 14:32:57', '2026-06-22 14:32:57'),
(16, 1, 1, 'withdrawal', 313.00, 7.83, 305.18, 486.00, 1622.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-14 14:32:57', '2026-06-15 14:32:57', '2026-06-22 14:32:57'),
(17, 1, 1, 'withdrawal', 400.00, 10.00, 390.00, 548.00, 1617.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-21 14:32:57', '2026-06-15 14:32:57', '2026-06-22 14:32:57'),
(18, 1, 1, 'deposit', 282.00, 0.00, 282.00, 387.00, 713.00, 'completed', NULL, 'Deposit transaction', NULL, '2026-06-11 14:32:57', '2026-06-12 14:32:57', '2026-06-22 14:32:57'),
(19, 1, 1, 'commission', 348.00, 0.00, 348.00, 641.00, 829.00, 'completed', NULL, 'Commission transaction', NULL, '2026-06-12 14:32:57', '2026-06-21 14:32:57', '2026-06-22 14:32:57'),
(20, 1, 1, 'deposit', 479.00, 0.00, 479.00, 803.00, 800.00, 'completed', NULL, 'Deposit transaction', NULL, '2026-06-13 14:32:57', '2026-06-18 14:32:57', '2026-06-22 14:32:57'),
(21, 1, 1, 'deposit', 237.00, 0.00, 237.00, 1316.00, 2025.00, 'completed', NULL, 'Deposit transaction', NULL, '2026-06-17 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(22, 1, 1, 'withdrawal', 227.00, 5.68, 221.33, 803.00, 1614.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-18 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(23, 1, 1, 'withdrawal', 83.00, 2.08, 80.93, 158.00, 1067.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-20 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(24, 1, 1, 'commission', 47.00, 0.00, 47.00, 764.00, 2040.00, 'completed', NULL, 'Commission transaction', NULL, '2026-06-15 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(25, 1, 1, 'commission', 22.00, 0.00, 22.00, 1781.00, 1789.00, 'completed', NULL, 'Commission transaction', NULL, '2026-06-09 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(26, 1, 1, 'withdrawal', 345.00, 8.63, 336.38, 463.00, 1819.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-20 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(27, 1, 1, 'purchase', 163.00, 0.00, 163.00, 527.00, 622.00, 'completed', NULL, 'Purchase transaction', NULL, '2026-06-10 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(28, 1, 1, 'commission', 466.00, 0.00, 466.00, 1145.00, 2289.00, 'completed', NULL, 'Commission transaction', NULL, '2026-06-11 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(29, 1, 1, 'withdrawal', 107.00, 2.68, 104.33, 807.00, 2251.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-17 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(30, 1, 1, 'commission', 33.00, 0.00, 33.00, 1911.00, 769.00, 'completed', NULL, 'Commission transaction', NULL, '2026-06-11 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(31, 1, 1, 'withdrawal', 281.00, 7.03, 273.98, 1248.00, 1757.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-17 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(32, 1, 1, 'withdrawal', 406.00, 10.15, 395.85, 498.00, 2424.00, 'completed', NULL, 'Withdrawal transaction', NULL, '2026-06-07 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(33, 1, 1, 'commission', 388.00, 0.00, 388.00, 1298.00, 723.00, 'completed', NULL, 'Commission transaction', NULL, '2026-06-11 14:34:54', '2026-06-22 14:34:54', '2026-06-22 14:34:54'),
(34, 1, 1, 'purchase', 490.00, 0.00, 490.00, 1302.00, 1912.00, 'completed', NULL, 'Purchase transaction', NULL, '2026-06-19 14:34:55', '2026-06-22 14:34:55', '2026-06-22 14:34:55'),
(35, 1, 1, 'deposit', 230.00, 0.00, 230.00, 1956.00, 1576.00, 'completed', NULL, 'Deposit transaction', NULL, '2026-06-17 14:34:55', '2026-06-22 14:34:55', '2026-06-22 14:34:55'),
(36, 2, 2, 'purchase', -1649.64, 0.00, -1649.64, 341.00, -1308.64, 'pending', NULL, 'Commande #ORD-6A42B86CD55EE', NULL, NULL, '2026-06-29 16:24:45', '2026-06-29 16:24:45');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sponsor_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rank` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Distributor',
  `rank_id` bigint UNSIGNED DEFAULT NULL,
  `package_id` bigint UNSIGNED DEFAULT NULL,
  `pv_balance` int NOT NULL DEFAULT '0',
  `bv_balance` int NOT NULL DEFAULT '0',
  `commission_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_earnings` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_sponsors` int NOT NULL DEFAULT '0',
  `total_team` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `kyc_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_submitted',
  `kyc_verified_at` timestamp NULL DEFAULT NULL,
  `package_expiry` timestamp NULL DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `sponsor_id`, `phone`, `rank`, `rank_id`, `package_id`, `pv_balance`, `bv_balance`, `commission_balance`, `total_earnings`, `total_sponsors`, `total_team`, `is_active`, `kyc_status`, `kyc_verified_at`, `package_expiry`, `avatar`, `country`, `city`, `address`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@salang.com', 'SALADMIN', '+225 0700000000', 'Pearl', 10, 5, 50000, 50000, 0.00, 4238.00, 2, 27, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Abidjan', NULL, NULL, '$2y$12$ZBAOB9BgU8FyTqcjhks.G.1zu0C2kC33AoqADM72IxuZnF1qW7kau', NULL, '2026-06-22 14:32:45', '2026-06-22 18:07:10'),
(2, 'Jean Dupont', 'test1@salang.com', 'SALE4623B', '+225 0720408256', 'Distributor', 3, 2, 992, 5974, 0.00, 4382.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Bouaké', NULL, NULL, '$2y$12$YCXYggWoyy/eQWInRiRh2.XbJhZo8foq1zHatBmg4iKRU2S.bbxH.', NULL, '2026-05-03 14:32:46', '2026-06-22 14:32:46'),
(3, 'Marie Martin', 'test2@salang.com', 'SALEBA2A5', '+225 0749526808', 'Distributor', 2, 2, 767, 7046, 0.00, 2023.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'San-Pédro', NULL, NULL, '$2y$12$CsoduRKp63NRrI0WKfUe9ezAI4i40y47I2NhxXjAt7yzfRXpQjBmO', NULL, '2026-06-16 14:32:46', '2026-06-22 14:32:46'),
(4, 'Pierre Durand', 'test3@salang.com', 'SALF3B92B', '+225 0762654964', 'Distributor', 4, 1, 7033, 5171, 0.00, 242.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Bouaké', NULL, NULL, '$2y$12$qXfON1vtnep14H3EC6fGwevAOvOAriINDzLLJeuFvs7qB6td/j2oW', NULL, '2026-06-01 14:32:47', '2026-06-22 14:32:47'),
(5, 'Sophie Lefevre', 'test4@salang.com', 'SALFABAEA', '+225 0799099244', 'Distributor', 8, 3, 5701, 6249, 0.00, 1143.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'San-Pédro', NULL, NULL, '$2y$12$vr7VFCD8khg36rMYqZCvKet.WMH/nI1gyAAcxnGwARTDwTzX9E88u', NULL, '2026-06-18 14:32:47', '2026-06-22 14:32:47'),
(6, 'Lucas Bernard', 'test5@salang.com', 'SAL03625B', '+225 0775328790', 'Distributor', 3, 3, 4113, 1331, 0.00, 4469.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Daloa', NULL, NULL, '$2y$12$4JVHXqQdAL1Yb6/m3szb0O4cNKT9e.Weak6H9vmXkTfuwj18Za.9.', NULL, '2026-05-05 14:32:48', '2026-06-22 14:32:48'),
(7, 'Emma Petit', 'test6@salang.com', 'SAL0AF7EB', '+225 0738041683', 'Distributor', 1, 3, 4682, 5509, 0.00, 3162.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Bouaké', NULL, NULL, '$2y$12$gGVKBVGT2NfsY0dE1FK3LuN3reCiBTzKz64C7We8zuqF7mFQLo/Iy', NULL, '2026-05-15 14:32:48', '2026-06-22 14:32:48'),
(8, 'Thomas Robert', 'test7@salang.com', 'SAL13E5E6', '+225 0740316698', 'Distributor', 3, 1, 907, 4895, 0.00, 3819.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Daloa', NULL, NULL, '$2y$12$JX/x6E8KcVayTRFyzUxVxO.pp4cknXTbookXkFT8vztpSOvtLc/YS', NULL, '2026-04-23 14:32:49', '2026-06-22 14:32:49'),
(9, 'Julie Richard', 'test8@salang.com', 'SAL1B1C4E', '+225 0761619508', 'Distributor', 4, 4, 2275, 5382, 0.00, 1998.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Daloa', NULL, NULL, '$2y$12$23evTUpmJ5MiHU8h8hdcQOZ/Pk5vJqiOeja.QNeAI3IN5VjveG7Wq', NULL, '2026-06-20 14:32:49', '2026-06-22 14:32:49'),
(10, 'Nicolas Dubois', 'test9@salang.com', 'SAL233CA5', '+225 0721656274', 'Distributor', 1, 5, 6243, 3522, 0.00, 2975.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Daloa', NULL, NULL, '$2y$12$Ib0GbWMHW1YlP2zMO1UjL.fOheId6qKoAadwAcdQ2MPUiOQqQK06q', NULL, '2026-05-07 14:32:50', '2026-06-22 14:32:50'),
(11, 'Camille Moreau', 'test10@salang.com', 'SAL2A565C', '+225 0773929706', 'Distributor', 9, 3, 3801, 128, 0.00, 4944.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Daloa', NULL, NULL, '$2y$12$Tu3RZ0gwbw1fEaBs3pJCXu/nszx26Yp7cbZPFwoV6GWphs7y2MTsK', NULL, '2026-05-05 14:32:50', '2026-06-22 14:32:50'),
(12, 'Alexandre Laurent', 'test11@salang.com', 'SAL334098', '+225 0761962375', 'Distributor', 6, 2, 3918, 3975, 0.00, 976.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Bouaké', NULL, NULL, '$2y$12$f0d15wxWyQhWymF9gMPkOOW8qMuBw8INxRdj8ArV3T.7Ez/CVobea', NULL, '2026-06-04 14:32:51', '2026-06-22 14:32:51'),
(13, 'Isabelle Simon', 'test12@salang.com', 'SAL3A4A1E', '+225 0707724794', 'Distributor', 9, 3, 1899, 2729, 0.00, 1257.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Daloa', NULL, NULL, '$2y$12$g0vlkw46U5l6graiah0/7.5zyA/RCo.PPsg2sHfW9BwE38l.hve1u', NULL, '2026-04-25 14:32:51', '2026-06-22 14:32:51'),
(14, 'Michel Francois', 'test13@salang.com', 'SAL43486A', '+225 0770573589', 'Distributor', 2, 3, 7136, 6139, 0.00, 1262.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'San-Pédro', NULL, NULL, '$2y$12$TMB.JrHZHTzvMWdtEFVdgO/5YA39jRoAOtrE9HREkyGrEhNZwylW.', NULL, '2026-06-04 14:32:52', '2026-06-22 14:32:52'),
(15, 'Catherine Michel', 'test14@salang.com', 'SAL4B35B7', '+225 0774662789', 'Distributor', 6, 5, 1922, 1925, 0.00, 1073.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Abidjan', NULL, NULL, '$2y$12$iLZORbuSb2.TdZQ4ZHsDNeNFhzMwnFvpE3VsRM.FhPdN0O8N9gbjS', NULL, '2026-05-24 14:32:52', '2026-06-22 14:32:52'),
(16, 'Philippe Garcia', 'test15@salang.com', 'SAL538886', '+225 0742986294', 'Distributor', 9, 1, 413, 5790, 0.00, 4433.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Daloa', NULL, NULL, '$2y$12$jv9kp3g3GV6C87yQz4tN4uCkTKmN4Ye75yrt4F0hGQfake9zWM4.O', NULL, '2026-06-20 14:32:53', '2026-06-22 14:32:53'),
(17, 'Jean Dupont', 'demo1@salang.com', 'SAL8E6EC0', '+225 0741292554', 'Distributor', 1, 5, 4759, 0, 0.00, 1268.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Bouaké', NULL, NULL, '$2y$12$rIWWpagusL7O52U3WNXKDugM/Tg10tzirXS/cdBH1JOL83tZ738Nm', NULL, '2026-06-22 14:34:48', '2026-06-22 14:34:48'),
(18, 'Marie Martin', 'demo2@salang.com', 'SAL96B5B6', '+225 0706277416', 'Distributor', 3, 5, 1302, 0, 0.00, 1107.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Bouaké', NULL, NULL, '$2y$12$4JjmOCKlLHFSh7sioKhXLuZmdjT93bP0BsTj20rVqoBr6rrZZMJ8u', NULL, '2026-06-22 14:34:49', '2026-06-22 14:34:49'),
(19, 'Pierre Durand', 'demo3@salang.com', 'SAL9DAEFE', '+225 0781159165', 'Distributor', 1, 2, 1994, 0, 0.00, 550.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Bouaké', NULL, NULL, '$2y$12$snE2nKv8UddoUQbUfZPLku9VprO2gikWS2OUYGVaIxfTr7kkAJxA2', NULL, '2026-06-22 14:34:49', '2026-06-22 14:34:49'),
(20, 'Sophie Lefevre', 'demo4@salang.com', 'SALA5CAF4', '+225 0785683085', 'Distributor', 10, 2, 4087, 0, 0.00, 211.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Abidjan', NULL, NULL, '$2y$12$huSyEa5B2/Y/KEi3N8c0juWIAXY422BmBDyjDRayktJ2VurGwL4iC', NULL, '2026-06-22 14:34:50', '2026-06-22 14:34:50'),
(21, 'Lucas Bernard', 'demo5@salang.com', 'SALAC45DC', '+225 0766855108', 'Distributor', 4, 2, 585, 0, 0.00, 724.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Abidjan', NULL, NULL, '$2y$12$9UocFWDg7Ig/bwtbCBa38.g9ESQLbXfYB4IjxC/S6k2JVFBHKMOQ6', NULL, '2026-06-22 14:34:50', '2026-06-22 14:34:50'),
(22, 'Emma Petit', 'demo6@salang.com', 'SALB4760B', '+225 0759990557', 'Distributor', 1, 1, 4072, 0, 0.00, 1857.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Bouaké', NULL, NULL, '$2y$12$6.EX4nlOiACqFsNRsKn8n.Rx047FOGfBLyK1sWG2XmFVPA2AJsgPe', NULL, '2026-06-22 14:34:51', '2026-06-22 14:34:51'),
(23, 'Thomas Robert', 'demo7@salang.com', 'SALBB316E', '+225 0779298220', 'Distributor', 8, 1, 3384, 0, 0.00, 1040.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Yamoussoukro', NULL, NULL, '$2y$12$RoyUw4.zhzW9UZ5r/RAQl.IZUQ.Y9zWALfNVMf.4ZX/58QDGm1I2W', NULL, '2026-06-22 14:34:51', '2026-06-22 14:34:51'),
(24, 'Julie Richard', 'demo8@salang.com', 'SALC36B76', '+225 0737076705', 'Distributor', 6, 3, 1379, 0, 0.00, 233.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Bouaké', NULL, NULL, '$2y$12$5vy2vCiuaUfhVgnivpn7vetLkIklsGjL5bTdIiXOfLHi4/NKxO5Ue', NULL, '2026-06-22 14:34:52', '2026-06-22 14:34:52'),
(25, 'Nicolas Dubois', 'demo9@salang.com', 'SALCA5BB3', '+225 0779994543', 'Distributor', 3, 2, 4561, 0, 0.00, 687.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Yamoussoukro', NULL, NULL, '$2y$12$SKokPF/jTQa2oSA9Hqtzv.LsmmMDvDI7D1dbnDio.9gP9wD8yhZmi', NULL, '2026-06-22 14:34:52', '2026-06-22 14:34:52'),
(26, 'Camille Moreau', 'demo10@salang.com', 'SALD28C8B', '+225 0712757884', 'Distributor', 2, 2, 1430, 0, 0.00, 2115.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, 'Côte d\'Ivoire', 'Bouaké', NULL, NULL, '$2y$12$JVnz06gcjogNybJ5ITP1Me8RpCZUnZnPk9UZSE3Tw.FkOElD1Klxa', NULL, '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(27, 'samuel mandevu martin', 'samuelmandevu10@gmail.com', 'SALDEBF71', '975746415', 'Distributor', 1, NULL, 0, 0, 0.00, 0.00, 0, 0, 1, 'not_submitted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$5vAF7arDmkgANsSuI/op8uzOj17SWN6EDb.wchdqfJxhsuEkj9frO', NULL, '2026-06-22 18:07:10', '2026-06-22 18:07:10');

-- --------------------------------------------------------

--
-- Structure de la table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pending_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_withdrawn` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_deposited` decimal(15,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `pending_balance`, `total_withdrawn`, `total_deposited`, `currency`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 2500.00, 500.00, 1000.00, 4000.00, 'USD', 1, '2026-06-22 14:32:45', '2026-06-22 14:32:45'),
(2, 2, 341.00, 144.00, 172.00, 568.00, 'USD', 1, '2026-06-22 14:32:46', '2026-06-22 14:32:46'),
(3, 3, 486.00, 273.00, 380.00, 1119.00, 'USD', 1, '2026-06-22 14:32:46', '2026-06-22 14:32:46'),
(4, 4, 912.00, 105.00, 280.00, 1285.00, 'USD', 1, '2026-06-22 14:32:47', '2026-06-22 14:32:47'),
(5, 5, 389.00, 235.00, 282.00, 284.00, 'USD', 1, '2026-06-22 14:32:47', '2026-06-22 14:32:47'),
(6, 6, 1387.00, 107.00, 239.00, 1477.00, 'USD', 1, '2026-06-22 14:32:48', '2026-06-22 14:32:48'),
(7, 7, 1003.00, 250.00, 51.00, 1612.00, 'USD', 1, '2026-06-22 14:32:48', '2026-06-22 14:32:48'),
(8, 8, 1079.00, 12.00, 342.00, 1891.00, 'USD', 1, '2026-06-22 14:32:49', '2026-06-22 14:32:49'),
(9, 9, 419.00, 15.00, 317.00, 1192.00, 'USD', 1, '2026-06-22 14:32:49', '2026-06-22 14:32:49'),
(10, 10, 183.00, 52.00, 249.00, 1960.00, 'USD', 1, '2026-06-22 14:32:50', '2026-06-22 14:32:50'),
(11, 11, 54.00, 157.00, 500.00, 268.00, 'USD', 1, '2026-06-22 14:32:50', '2026-06-22 14:32:50'),
(12, 12, 540.00, 263.00, 467.00, 1546.00, 'USD', 1, '2026-06-22 14:32:51', '2026-06-22 14:32:51'),
(13, 13, 188.00, 247.00, 142.00, 788.00, 'USD', 1, '2026-06-22 14:32:51', '2026-06-22 14:32:51'),
(14, 14, 1440.00, 234.00, 39.00, 913.00, 'USD', 1, '2026-06-22 14:32:52', '2026-06-22 14:32:52'),
(15, 15, 681.00, 227.00, 470.00, 172.00, 'USD', 1, '2026-06-22 14:32:52', '2026-06-22 14:32:52'),
(16, 16, 162.00, 186.00, 97.00, 1783.00, 'USD', 1, '2026-06-22 14:32:53', '2026-06-22 14:32:53'),
(17, 17, 333.00, 159.00, 0.00, 0.00, 'USD', 1, '2026-06-22 14:34:49', '2026-06-22 14:34:49'),
(18, 18, 680.00, 104.00, 0.00, 0.00, 'USD', 1, '2026-06-22 14:34:49', '2026-06-22 14:34:49'),
(19, 19, 135.00, 106.00, 0.00, 0.00, 'USD', 1, '2026-06-22 14:34:50', '2026-06-22 14:34:50'),
(20, 20, 467.00, 79.00, 0.00, 0.00, 'USD', 1, '2026-06-22 14:34:50', '2026-06-22 14:34:50'),
(21, 21, 220.00, 18.00, 0.00, 0.00, 'USD', 1, '2026-06-22 14:34:50', '2026-06-22 14:34:50'),
(22, 22, 402.00, 190.00, 0.00, 0.00, 'USD', 1, '2026-06-22 14:34:51', '2026-06-22 14:34:51'),
(23, 23, 525.00, 87.00, 0.00, 0.00, 'USD', 1, '2026-06-22 14:34:51', '2026-06-22 14:34:51'),
(24, 24, 102.00, 95.00, 0.00, 0.00, 'USD', 1, '2026-06-22 14:34:52', '2026-06-22 14:34:52'),
(25, 25, 221.00, 34.00, 0.00, 0.00, 'USD', 1, '2026-06-22 14:34:52', '2026-06-22 14:34:52'),
(26, 26, 573.00, 152.00, 0.00, 0.00, 'USD', 1, '2026-06-22 14:34:53', '2026-06-22 14:34:53'),
(27, 27, 0.00, 0.00, 0.00, 0.00, 'USD', 1, '2026-06-22 18:07:10', '2026-06-22 18:07:10');

-- --------------------------------------------------------

--
-- Structure de la table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `wallet_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `fee` decimal(15,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(15,2) NOT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `withdrawals`
--

INSERT INTO `withdrawals` (`id`, `user_id`, `wallet_id`, `amount`, `fee`, `net_amount`, `method`, `payment_address`, `phone_number`, `bank_details`, `status`, `notes`, `processed_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 288.00, 7.20, 280.80, 'bank', NULL, NULL, NULL, 'completed', 'Retrait test 1', NULL, NULL, '2026-06-08 14:32:57', '2026-06-22 14:32:57'),
(2, 1, 1, 142.00, 3.55, 138.45, 'mobile_money', NULL, NULL, NULL, 'completed', 'Retrait test 2', NULL, NULL, '2026-06-14 14:32:57', '2026-06-22 14:32:57'),
(3, 1, 1, 255.00, 6.38, 248.63, 'crypto', NULL, NULL, NULL, 'processing', 'Retrait test 3', NULL, NULL, '2026-06-03 14:32:57', '2026-06-22 14:32:57'),
(4, 1, 1, 268.00, 6.70, 261.30, 'mobile_money', NULL, NULL, NULL, 'processing', 'Retrait test 4', NULL, NULL, '2026-06-14 14:32:58', '2026-06-22 14:32:58'),
(5, 1, 1, 275.00, 6.88, 268.13, 'bank', NULL, NULL, NULL, 'pending', 'Retrait test 5', NULL, NULL, '2026-06-06 14:32:58', '2026-06-22 14:32:58');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Index pour la table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Index pour la table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commissions_from_user_id_foreign` (`from_user_id`),
  ADD KEY `commissions_order_id_foreign` (`order_id`),
  ADD KEY `commissions_package_id_foreign` (`package_id`),
  ADD KEY `commissions_user_id_status_index` (`user_id`,`status`),
  ADD KEY `commissions_type_created_at_index` (`type`,`created_at`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Index pour la table `genealogy`
--
ALTER TABLE `genealogy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `genealogy_user_id_sponsor_id_unique` (`user_id`,`sponsor_id`),
  ADD KEY `genealogy_parent_id_foreign` (`parent_id`),
  ADD KEY `genealogy_sponsor_id_level_index` (`sponsor_id`,`level`);

--
-- Index pour la table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Index pour la table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `kyc_documents`
--
ALTER TABLE `kyc_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kyc_documents_user_id_foreign` (`user_id`),
  ADD KEY `kyc_documents_verified_by_foreign` (`verified_by`),
  ADD KEY `kyc_documents_user_id_status_index` (`user_id`,`status`),
  ADD KEY `kyc_documents_document_type_status_index` (`document_type`,`status`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Index pour la table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_order_number_index` (`order_number`),
  ADD KEY `orders_user_id_status_index` (`user_id`,`status`);

--
-- Index pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`),
  ADD KEY `order_items_package_id_foreign` (`package_id`);

--
-- Index pour la table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `packages_slug_unique` (`slug`);

--
-- Index pour la table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_slug_is_active_index` (`slug`,`is_active`);

--
-- Index pour la table `ranks`
--
ALTER TABLE `ranks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ranks_slug_unique` (`slug`);

--
-- Index pour la table `rank_history`
--
ALTER TABLE `rank_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rank_history_old_rank_id_foreign` (`old_rank_id`),
  ADD KEY `rank_history_new_rank_id_foreign` (`new_rank_id`),
  ADD KEY `rank_history_user_id_created_at_index` (`user_id`,`created_at`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Index pour la table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Index pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_wallet_id_foreign` (`wallet_id`),
  ADD KEY `transactions_user_id_type_status_index` (`user_id`,`type`,`status`),
  ADD KEY `transactions_created_at_index` (`created_at`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_rank_id_foreign` (`rank_id`),
  ADD KEY `users_package_id_foreign` (`package_id`),
  ADD KEY `users_kyc_status_index` (`kyc_status`);

--
-- Index pour la table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wallets_user_id_unique` (`user_id`);

--
-- Index pour la table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `withdrawals_wallet_id_foreign` (`wallet_id`),
  ADD KEY `withdrawals_user_id_status_index` (`user_id`,`status`),
  ADD KEY `withdrawals_created_at_index` (`created_at`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `genealogy`
--
ALTER TABLE `genealogy`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kyc_documents`
--
ALTER TABLE `kyc_documents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT pour la table `ranks`
--
ALTER TABLE `ranks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `rank_history`
--
ALTER TABLE `rank_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commissions`
--
ALTER TABLE `commissions`
  ADD CONSTRAINT `commissions_from_user_id_foreign` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `commissions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `commissions_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `commissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `genealogy`
--
ALTER TABLE `genealogy`
  ADD CONSTRAINT `genealogy_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `genealogy_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `genealogy_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `kyc_documents`
--
ALTER TABLE `kyc_documents`
  ADD CONSTRAINT `kyc_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kyc_documents_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `rank_history`
--
ALTER TABLE `rank_history`
  ADD CONSTRAINT `rank_history_new_rank_id_foreign` FOREIGN KEY (`new_rank_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rank_history_old_rank_id_foreign` FOREIGN KEY (`old_rank_id`) REFERENCES `ranks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rank_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_rank_id_foreign` FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `withdrawals_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
