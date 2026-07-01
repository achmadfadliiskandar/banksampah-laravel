-- Adminer 4.8.4 MySQL 8.0.46-0ubuntu0.22.04.3 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `detail_setorans`;
CREATE TABLE `detail_setorans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `setoran_id` bigint unsigned NOT NULL,
  `kategori_id` bigint unsigned NOT NULL,
  `berat` decimal(8,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `path_foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detail_setorans_setoran_id_foreign` (`setoran_id`),
  KEY `detail_setorans_kategori_id_foreign` (`kategori_id`),
  CONSTRAINT `detail_setorans_kategori_id_foreign` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_sampahs` (`id`),
  CONSTRAINT `detail_setorans_setoran_id_foreign` FOREIGN KEY (`setoran_id`) REFERENCES `setorans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `detail_setorans` (`id`, `setoran_id`, `kategori_id`, `berat`, `subtotal`, `path_foto`, `created_at`, `updated_at`) VALUES
(1,	1,	3,	2.00,	1000.00,	NULL,	'2026-06-07 00:42:42',	'2026-06-07 00:42:42'),
(2,	1,	1,	3.00,	9000.00,	NULL,	'2026-06-07 00:42:42',	'2026-06-07 00:42:42'),
(3,	2,	4,	3.00,	12000.00,	NULL,	'2026-06-07 00:44:11',	'2026-06-07 00:44:11'),
(4,	2,	5,	5.00,	10000.00,	NULL,	'2026-06-07 00:44:11',	'2026-06-07 00:44:11'),
(20,	13,	1,	1.00,	3000.00,	NULL,	'2026-06-14 20:59:20',	'2026-06-14 21:13:31'),
(21,	13,	5,	3.00,	6000.00,	NULL,	'2026-06-14 20:59:20',	'2026-06-14 21:13:31'),
(22,	14,	2,	2.00,	3000.00,	NULL,	'2026-06-14 21:16:53',	'2026-06-14 21:16:53'),
(23,	14,	4,	3.00,	12000.00,	NULL,	'2026-06-14 21:16:53',	'2026-06-14 21:16:53'),
(24,	15,	5,	1.00,	2000.00,	'foto_setoran/sampah_6a3224c5417c9.jpg',	'2026-06-16 21:38:29',	'2026-06-16 21:38:29'),
(25,	15,	3,	1.00,	500.00,	'foto_setoran/sampah_6a3224c54a050.jpg',	'2026-06-16 21:38:29',	'2026-06-16 21:38:29'),
(26,	15,	5,	1.00,	2000.00,	'foto_setoran/sampah_6a3224c54c3cc.jpg',	'2026-06-16 21:38:29',	'2026-06-16 21:38:29'),
(27,	15,	1,	1.00,	3000.00,	'foto_setoran/sampah_6a3224c54ee1f.jpg',	'2026-06-16 21:38:29',	'2026-06-16 21:38:29'),
(28,	15,	2,	1.00,	1500.00,	'foto_setoran/sampah_6a3224c5516f0.jpg',	'2026-06-16 21:38:29',	'2026-06-16 21:38:29'),
(32,	18,	1,	2.00,	6000.00,	'foto_setoran/sampah_6a3ca71738935.jpg',	'2026-06-24 20:57:11',	'2026-06-24 20:57:11'),
(33,	18,	5,	1.00,	2000.00,	'foto_setoran/sampah_6a3ca7173c9b5.jpg',	'2026-06-24 20:57:11',	'2026-06-24 20:57:11'),
(34,	18,	2,	1.00,	1500.00,	'foto_setoran/sampah_6a3ca7173e07f.jpg',	'2026-06-24 20:57:11',	'2026-06-24 20:57:11'),
(35,	18,	2,	1.00,	1500.00,	'foto_setoran/sampah_6a3ca7173f248.jpg',	'2026-06-24 20:57:11',	'2026-06-24 20:57:11'),
(36,	19,	3,	1.00,	500.00,	'foto_setoran/sampah_6a3cff5be0011.jpg',	'2026-06-25 03:13:47',	'2026-06-25 03:13:47'),
(37,	19,	3,	1.00,	500.00,	'foto_setoran/sampah_6a3cff5be4e1d.jpg',	'2026-06-25 03:13:47',	'2026-06-25 03:13:47'),
(38,	19,	5,	1.00,	2000.00,	'foto_setoran/sampah_6a3cff5be60f1.jpg',	'2026-06-25 03:13:47',	'2026-06-25 03:13:47'),
(39,	19,	2,	1.00,	1500.00,	'foto_setoran/sampah_6a3cff5be73e5.jpg',	'2026-06-25 03:13:47',	'2026-06-25 03:13:47'),
(40,	19,	2,	1.00,	1500.00,	'foto_setoran/sampah_6a3cff5be85bf.jpg',	'2026-06-25 03:13:47',	'2026-06-25 03:13:47'),
(41,	19,	1,	1.00,	3000.00,	'foto_setoran/sampah_6a3cff5be9921.jpg',	'2026-06-25 03:13:47',	'2026-06-25 03:13:47');

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `kategori_sampahs`;
CREATE TABLE `kategori_sampahs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_kategori` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_jenis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` enum('organik','anorganik','b3') COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga_per_kg` decimal(10,2) NOT NULL,
  `satuan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kg',
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kategori_sampahs_nama_jenis_unique` (`nama_jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `kategori_sampahs` (`id`, `kode_kategori`, `nama_jenis`, `tipe`, `harga_per_kg`, `satuan`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1,	'plastic',	'Botol Plastik PET',	'anorganik',	3000.00,	'kg',	'Botol air mineral bening, botol kecap, botol sirup plastik.',	'2026-06-07 00:21:23',	'2026-06-07 00:21:23'),
(2,	'cardboard',	'Kardus Bekas / Box',	'anorganik',	1500.00,	'kg',	'Kardus cokelat kering, kotak sepatu, karton packing.',	'2026-06-07 00:21:23',	'2026-06-07 00:21:23'),
(3,	'glass',	'Botol Kaca Bening',	'anorganik',	500.00,	'kg',	'Botol sirup kaca, gelas kaca utuh, botol kecap kaca.',	'2026-06-07 00:21:23',	'2026-06-07 00:21:23'),
(4,	'metal',	'Besi Tua / Kaleng',	'anorganik',	4000.00,	'kg',	'Potongan besi, paku, kaleng soda aluminium, kaleng susu.',	'2026-06-07 00:21:23',	'2026-06-07 00:21:23'),
(5,	'paper',	'Kertas Bekas',	'anorganik',	2000.00,	'kg',	'Kertas HVS, koran, majalah, atau buku bekas.',	'2026-06-07 00:21:23',	'2026-06-07 00:21:23'),
(6,	'trash',	'Sisa Makanan Dapur',	'organik',	0.00,	'kg',	'Nasi basi, tulang ayam/ikan, sisa sayuran dapur.',	'2026-06-07 00:21:23',	'2026-06-22 21:09:10'),
(14,	'trash',	'Sampah Kebun / Ranting',	'organik',	0.00,	'kg',	NULL,	'2026-06-22 21:09:42',	'2026-06-22 21:09:42'),
(15,	'trash',	'Baterai Bekas / Aki',	'organik',	0.00,	'kg',	NULL,	'2026-06-22 21:10:02',	'2026-06-22 21:10:02');

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1,	'2014_10_12_000000_create_users_table',	1),
(2,	'2014_10_12_100000_create_password_reset_tokens_table',	1),
(3,	'2019_08_19_000000_create_failed_jobs_table',	1),
(4,	'2019_12_14_000001_create_personal_access_tokens_table',	1),
(5,	'2026_04_14_055108_create_kategori_sampahs_table',	1),
(6,	'2026_04_14_055320_create_setorans_table',	1),
(7,	'2026_04_14_055440_create_detail_setorans_table',	1);

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `setorans`;
CREATE TABLE `setorans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `admin_id` bigint unsigned DEFAULT NULL,
  `total_berat` decimal(8,2) NOT NULL DEFAULT '0.00',
  `total_harga` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','success','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setorans_kode_transaksi_unique` (`kode_transaksi`),
  KEY `setorans_user_id_foreign` (`user_id`),
  KEY `setorans_admin_id_foreign` (`admin_id`),
  CONSTRAINT `setorans_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`),
  CONSTRAINT `setorans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `setorans` (`id`, `kode_transaksi`, `user_id`, `admin_id`, `total_berat`, `total_harga`, `status`, `created_at`, `updated_at`) VALUES
(1,	'TRX-1780818162',	2,	1,	5.00,	10000.00,	'success',	'2026-06-07 00:42:42',	'2026-06-07 00:43:01'),
(2,	'TRX-1780818251',	3,	1,	8.00,	22000.00,	'success',	'2026-06-07 00:44:11',	'2026-06-07 00:44:16'),
(13,	'TRX-1781495960',	3,	1,	4.00,	9000.00,	'success',	'2026-06-14 20:59:20',	'2026-06-14 21:13:31'),
(14,	'TRX-1781497013',	2,	1,	5.00,	15000.00,	'success',	'2026-06-14 21:16:53',	'2026-06-14 21:17:45'),
(15,	'TRX-MLYMRBPM',	2,	1,	5.00,	9000.00,	'success',	'2026-06-16 21:38:29',	'2026-06-16 21:39:32'),
(18,	'TRX-V2AIRZ6S',	3,	1,	5.00,	11000.00,	'success',	'2026-06-24 20:57:11',	'2026-06-24 21:02:53'),
(19,	'TRX-U8XQSVVN',	2,	1,	6.00,	9000.00,	'success',	'2026-06-25 03:13:47',	'2026-06-25 03:16:03');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','nasabah') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'nasabah',
  `saldo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `kode_nasabah` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_kode_nasabah_unique` (`kode_nasabah`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `role`, `saldo`, `kode_nasabah`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1,	'Admin Pusat',	'admin@banksampah.com',	NULL,	'admin',	0.00,	NULL,	'$2y$12$SjFkUGzaUOrxUBicR8OyMOJj6B92ZbT2oPbmhvRq8UkrNff9N8iRG',	NULL,	'2026-06-07 00:21:23',	'2026-06-07 00:21:23'),
(2,	'Achmad Fadli Iskandar',	'fadli@nasabah.com',	NULL,	'nasabah',	10000.00,	'NSB-20260607-0001',	'$2y$12$GmaJjxZLiSSWYJSRiM/LQuCbtTXRxkqzbQft/c89gq9lW1KXjfMGu',	NULL,	'2026-06-07 00:21:23',	'2026-06-29 22:06:58'),
(3,	'Wisnu Saputra',	'wisnu@gmail.com',	NULL,	'nasabah',	10000.00,	'NSB-20260607002',	'$2y$12$sXeDVcXju8qph3gGDaHM0ea5nDMBk9CfB9luVPtB38AuEzMwqViNa',	NULL,	'2026-06-07 00:38:16',	'2026-06-29 22:06:45');

-- 2026-06-30 05:09:48
