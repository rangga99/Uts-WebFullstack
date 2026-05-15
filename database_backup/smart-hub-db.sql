-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2026 at 06:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart-hub-db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_code` varchar(30) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `duration_hours` decimal(4,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','confirmed','ongoing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `confirmed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_code`, `user_id`, `room_id`, `start_datetime`, `end_datetime`, `duration_hours`, `total_price`, `status`, `notes`, `confirmed_by`, `confirmed_at`, `cancelled_at`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(1, 'BK-20250512-001', 2, 1, '2026-05-16 10:00:53', '2026-05-16 13:00:53', 3.00, 225000.00, 'confirmed', NULL, 1, '2026-05-14 21:27:53', NULL, NULL, '2026-05-14 21:27:53', '2026-05-14 21:27:53'),
(2, 'BK-20260515-002', 2, 2, '2026-05-15 23:27:00', '2026-05-18 23:27:00', 72.00, 5040000.00, 'confirmed', 'meeting', 1, '2026-05-15 09:36:02', NULL, NULL, '2026-05-15 09:27:37', '2026-05-15 09:36:02');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(30) NOT NULL,
  `category` enum('camera','audio','lighting','computer','other') NOT NULL DEFAULT 'other',
  `brand` varchar(80) DEFAULT NULL,
  `model` varchar(80) DEFAULT NULL,
  `serial_number` varchar(80) DEFAULT NULL,
  `condition` enum('excellent','good','fair','needs_repair') NOT NULL DEFAULT 'good',
  `status` enum('available','checked_out','maintenance','retired') NOT NULL DEFAULT 'available',
  `description` text DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `location` varchar(100) NOT NULL DEFAULT 'Storage Room',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `name`, `code`, `category`, `brand`, `model`, `serial_number`, `condition`, `status`, `description`, `purchase_date`, `purchase_price`, `location`, `created_at`, `updated_at`) VALUES
(1, 'Canon EOS R5', 'CAM-001', 'camera', 'Canon', 'EOS R5', 'SN-CANON-R5-001', 'excellent', 'available', NULL, '2023-06-15', 45000000.00, 'Cabinet A-1', '2026-05-14 21:27:53', '2026-05-14 21:27:53'),
(2, 'Sony A7 III', 'CAM-002', 'camera', 'Sony', 'Alpha A7 III', 'SN-SONY-A7III-001', 'good', 'checked_out', NULL, '2022-03-10', 32000000.00, 'Cabinet A-1', '2026-05-14 21:27:53', '2026-05-15 09:23:30'),
(3, 'Rode NT1-A Microphone', 'AUD-001', 'audio', 'Rode', 'NT1-A', 'SN-RODE-NT1A-001', 'excellent', 'checked_out', NULL, NULL, NULL, 'Cabinet B-1', '2026-05-14 21:27:53', '2026-05-14 21:27:53'),
(4, 'Godox SL-60W LED Light', 'LIT-001', 'lighting', 'Godox', 'SL-60W', NULL, 'good', 'available', NULL, NULL, NULL, 'Studio A Storage', '2026-05-14 21:27:53', '2026-05-14 21:27:53');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_checkouts`
--

CREATE TABLE `equipment_checkouts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `checkout_code` varchar(30) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED DEFAULT NULL,
  `checked_out_at` datetime NOT NULL,
  `expected_return_at` datetime NOT NULL,
  `returned_at` datetime DEFAULT NULL,
  `status` enum('active','returned','overdue','lost') NOT NULL DEFAULT 'active',
  `condition_before` enum('excellent','good','fair','needs_repair') NOT NULL DEFAULT 'good',
  `condition_after` enum('excellent','good','fair','needs_repair') DEFAULT NULL,
  `notes_checkout` text DEFAULT NULL,
  `notes_return` text DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment_checkouts`
--

INSERT INTO `equipment_checkouts` (`id`, `checkout_code`, `user_id`, `equipment_id`, `booking_id`, `checked_out_at`, `expected_return_at`, `returned_at`, `status`, `condition_before`, `condition_after`, `notes_checkout`, `notes_return`, `processed_by`, `created_at`, `updated_at`) VALUES
(1, 'CO-20250512-001', 3, 3, NULL, '2026-05-15 02:27:53', '2026-05-15 10:27:53', NULL, 'active', 'excellent', NULL, 'Untuk recording podcast episode 5', NULL, 1, '2026-05-14 21:27:53', '2026-05-14 21:27:53'),
(2, 'CO-20260515-002', 2, 2, NULL, '2026-05-15 16:23:30', '2026-05-20 20:23:00', NULL, 'active', 'good', NULL, 'pinjam poto shoot', NULL, 2, '2026-05-15 09:23:30', '2026-05-15 09:23:30');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2025_01_01_000001_create_users_table', 1),
(4, '2025_01_01_000002_create_rooms_table', 1),
(5, '2025_01_01_000003_create_equipment_table', 1),
(6, '2025_01_01_000004_create_bookings_table', 1),
(7, '2025_01_01_000005_create_equipment_checkouts_table', 1),
(8, '2026_05_15_041052_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'TestPC', 'aa0714ee00d40f57064d51952f76dcd3c7fa0feae0f73935a8b5058c39695cc4', '[\"admin\",\"equipment:read\",\"equipment:write\",\"booking:read\",\"booking:write\"]', NULL, '2026-06-14 04:37:04', '2026-05-14 21:37:04', '2026-05-14 21:37:04'),
(5, 'App\\Models\\User', 1, 'web-session', '662956449ab92f308dcc1902c51142d03a5ea6796a126cb254d2ff004db9222c', '[\"admin\",\"equipment:read\",\"equipment:write\",\"booking:read\",\"booking:write\"]', NULL, '2026-06-14 16:44:15', '2026-05-15 09:44:15', '2026-05-15 09:44:15'),
(6, 'App\\Models\\User', 2, 'web-session', '07059d91d39a16b5d1243c07bc75e4a5063a657e7cae53b114ac352f10110f76', '[\"member\",\"equipment:read\",\"equipment:checkout\",\"booking:read\",\"booking:create\"]', NULL, '2026-06-14 16:44:39', '2026-05-15 09:44:39', '2026-05-15 09:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `type` enum('workspace','studio','meeting') NOT NULL DEFAULT 'workspace',
  `capacity` tinyint(3) UNSIGNED NOT NULL,
  `description` text DEFAULT NULL,
  `facilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`facilities`)),
  `price_per_hour` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `code`, `type`, `capacity`, `description`, `facilities`, `price_per_hour`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 'Studio A — Photography', 'STUDIO-A', 'studio', 8, 'Studio foto profesional dengan backdrop dan pencahayaan lengkap.', '[\"Backdrop putih\",\"Backdrop hitam\",\"Ring light\",\"Reflector\",\"AC\"]', 75000.00, 1, '2026-05-14 21:27:53', '2026-05-14 21:27:53'),
(2, 'Studio BB — Podcast & Audio', 'STUDIO-B', 'studio', 5, 'Ruang rekaman audio terisolasi bunyi.', '[\"Soundproofing\",\"Mixer\",\"Microphone stand\",\"Headphone monitor\",\"AC\"]', 70000.00, 1, '2026-05-14 21:27:53', '2026-05-15 09:18:33'),
(3, 'Co-Working Space', 'COWORK-01', 'workspace', 20, 'Ruang kerja terbuka dengan meja dan kursi ergonomis.', '[\"WiFi 100Mbps\",\"Proyektor\",\"Whiteboard\",\"AC\",\"Colokan listrik\"]', 25000.00, 1, '2026-05-14 21:27:53', '2026-05-14 21:27:53'),
(4, 'Meeting Room — Inovasi', 'MTG-01', 'meeting', 10, 'Ruang rapat dengan fasilitas presentasi.', '[\"TV 65\\\"\",\"HDMI\",\"Whiteboard\",\"AC\",\"Teleconference\"]', 50000.00, 1, '2026-05-14 21:27:53', '2026-05-14 21:27:53');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member') NOT NULL DEFAULT 'member',
  `phone` varchar(20) DEFAULT NULL,
  `membership_number` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `phone`, `membership_number`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin SmartHub', '411253003@undira.ac.id', NULL, '$2y$12$6BHwefjtbhgtV4eaELslsuSiGHNfJccI1lkajEpMwtJF83nnhf/6G', 'admin', '081234560001', 'ADM-001', 1, 'AWOQJOufjStTznFhKmAnOJCcJd8mradZb6jtd7yWwidi6YJOL9dhS9legP9o', '2026-05-14 21:27:53', '2026-05-14 21:27:53'),
(2, 'Rangga Darmawan', 'rangga@gmail.com', NULL, '$2y$12$w8japn7vrfpBWzjZGjrBW.ZkX3kdAcgWKQbT68nvH4pyDQKOESZUm', 'member', '081234560010', 'MBR-2025-001', 1, 'AnZDwyQMICS7ncWHqUoa1YAmhuH1YwC06gc9ZRp5zy2R8r3xIAwvqVLdorS0', '2026-05-14 21:27:53', '2026-05-14 21:27:53'),
(3, 'Sari Dewi', 'sari@example.com', NULL, '$2y$12$lYGYTPqDjmNeKn5tHyOEbeEGQJAjn6QZBZGOaeY8xW75emZxawKba', 'member', '081234560011', 'MBR-2025-002', 1, NULL, '2026-05-14 21:27:53', '2026-05-14 21:27:53'),
(4, 'Saras', 'saras@gmail.com', NULL, '$2y$12$GhQX.RddS9G6wdGGRMuvPezP/P8fDbOKw6ri.zCVXGEwzOugvWVqa', 'member', '0857262626266', 'MBR-2026-003', 1, NULL, '2026-05-15 09:17:03', '2026-05-15 09:17:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bookings_booking_code_unique` (`booking_code`),
  ADD KEY `bookings_confirmed_by_foreign` (`confirmed_by`),
  ADD KEY `bookings_room_id_start_datetime_end_datetime_index` (`room_id`,`start_datetime`,`end_datetime`),
  ADD KEY `bookings_user_id_index` (`user_id`),
  ADD KEY `bookings_status_index` (`status`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipment_code_unique` (`code`),
  ADD UNIQUE KEY `equipment_serial_number_unique` (`serial_number`),
  ADD KEY `equipment_status_index` (`status`),
  ADD KEY `equipment_category_index` (`category`);

--
-- Indexes for table `equipment_checkouts`
--
ALTER TABLE `equipment_checkouts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipment_checkouts_checkout_code_unique` (`checkout_code`),
  ADD KEY `equipment_checkouts_booking_id_foreign` (`booking_id`),
  ADD KEY `equipment_checkouts_processed_by_foreign` (`processed_by`),
  ADD KEY `equipment_checkouts_user_id_index` (`user_id`),
  ADD KEY `equipment_checkouts_equipment_id_index` (`equipment_id`),
  ADD KEY `equipment_checkouts_status_index` (`status`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rooms_code_unique` (`code`),
  ADD KEY `rooms_type_index` (`type`),
  ADD KEY `rooms_is_available_index` (`is_available`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_membership_number_unique` (`membership_number`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_is_active_index` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `equipment_checkouts`
--
ALTER TABLE `equipment_checkouts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_confirmed_by_foreign` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `equipment_checkouts`
--
ALTER TABLE `equipment_checkouts`
  ADD CONSTRAINT `equipment_checkouts_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_checkouts_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_checkouts_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_checkouts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
