-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2026 at 01:01 AM
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
-- Database: `pembukuan`
--

-- --------------------------------------------------------

--
-- Table structure for table `brilink_accounts`
--

CREATE TABLE `brilink_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brilink_report_id` bigint(20) UNSIGNED NOT NULL,
  `nama_rekening` varchar(150) NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  `saldo_awal` bigint(20) NOT NULL DEFAULT 0,
  `saldo_akhir` bigint(20) NOT NULL DEFAULT 0,
  `selisih` bigint(20) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brilink_pengeluaran`
--

CREATE TABLE `brilink_pengeluaran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brilink_report_id` bigint(20) UNSIGNED NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `nominal_kas` bigint(20) NOT NULL DEFAULT 0,
  `kategori` varchar(100) DEFAULT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `paid_at` date DEFAULT NULL,
  `paid_amount` bigint(20) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brilink_ppob_transactions`
--

CREATE TABLE `brilink_ppob_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brilink_report_id` bigint(20) UNSIGNED NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `nominal` bigint(20) NOT NULL DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brilink_reports`
--

CREATE TABLE `brilink_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `import_id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_hash` varchar(64) NOT NULL,
  `periode_start` date DEFAULT NULL,
  `periode_end` date DEFAULT NULL,
  `total_omset_ppob` bigint(20) NOT NULL DEFAULT 0,
  `total_tarik_tunai_edc` bigint(20) NOT NULL DEFAULT 0,
  `total_tarik_tunai_mbanking` bigint(20) NOT NULL DEFAULT 0,
  `total_setoran_brilink` bigint(20) NOT NULL DEFAULT 0,
  `total_pendapatan_admin` bigint(20) NOT NULL DEFAULT 0,
  `validasi_kas_uang` bigint(20) NOT NULL DEFAULT 0,
  `manual_validasi_kas_uang` bigint(20) NOT NULL DEFAULT 0,
  `manual_kas_akhir` bigint(20) DEFAULT NULL,
  `manual_kas_awal` bigint(20) DEFAULT NULL,
  `manual_validasi_mutasi_rek` bigint(20) DEFAULT 0,
  `manual_validasi_mutasi_fisik` bigint(20) DEFAULT 0,
  `laba_kasir_pintar` bigint(20) NOT NULL DEFAULT 0,
  `penambahan_brangkas` bigint(20) NOT NULL DEFAULT 0,
  `total_pengeluaran_riil` bigint(20) NOT NULL DEFAULT 0,
  `total_pindah_saldo` bigint(20) NOT NULL DEFAULT 0,
  `volume_transaksi_agen` int(11) NOT NULL DEFAULT 0,
  `saldo_awal_total` bigint(20) NOT NULL DEFAULT 0,
  `saldo_akhir_total` bigint(20) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brilink_saldo_pindah`
--

CREATE TABLE `brilink_saldo_pindah` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brilink_report_id` bigint(20) UNSIGNED NOT NULL,
  `nominal` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brilink_setoran`
--

CREATE TABLE `brilink_setoran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brilink_report_id` bigint(20) UNSIGNED NOT NULL,
  `nominal_edc` bigint(20) NOT NULL DEFAULT 0,
  `administrasi_toko` bigint(20) NOT NULL DEFAULT 0,
  `keterangan` varchar(255) DEFAULT NULL,
  `jumlah_setoran_riil` bigint(20) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brilink_tarik_tunai`
--

CREATE TABLE `brilink_tarik_tunai` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brilink_report_id` bigint(20) UNSIGNED NOT NULL,
  `tipe` enum('edc_bri','mbanking') NOT NULL,
  `nominal` bigint(20) NOT NULL DEFAULT 0,
  `admin_toko` bigint(20) NOT NULL DEFAULT 0,
  `ket_mesin_edc` varchar(255) DEFAULT NULL,
  `kmc` bigint(20) NOT NULL DEFAULT 0,
  `saldo_masuk` bigint(20) NOT NULL DEFAULT 0,
  `keterangan_rek` varchar(150) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:25:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"view shopee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:11:\"view tiktok\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:16:\"view tiktok live\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:14:\"view web-sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"view brilink\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:18:\"view brilink saldo\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:24:\"view brilink pertumbuhan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:24:\"view brilink pengeluaran\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:17:\"view brilink ppob\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:20:\"view brilink setoran\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:24:\"view brilink tarik-tunai\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:19:\"view brilink profit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:21:\"view brilink brangkas\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:13:\"view tabungan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:11:\"view hutang\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:12:\"view piutang\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:10:\"view zakat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:13:\"view karyawan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:9:\"view gaji\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:10:\"view minus\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:18:\"view import shopee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:18:\"view import tiktok\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:19:\"view import brilink\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:13:\"view products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:11:\"view stores\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:1:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}}}', 1793404255);

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
-- Table structure for table `gaji_karyawan`
--

CREATE TABLE `gaji_karyawan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `periode` varchar(7) NOT NULL,
  `gaji_pokok` bigint(20) NOT NULL DEFAULT 0,
  `bonus` bigint(20) NOT NULL DEFAULT 0,
  `potongan` bigint(20) NOT NULL DEFAULT 0,
  `komisi` int(11) NOT NULL DEFAULT 0,
  `total_gaji` bigint(20) NOT NULL DEFAULT 0,
  `status` enum('draft','dibayar') NOT NULL DEFAULT 'draft',
  `tanggal_bayar` date DEFAULT NULL,
  `metode_bayar` enum('tunai','transfer') NOT NULL DEFAULT 'tunai',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hutangs`
--

CREATE TABLE `hutangs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `nama_pemberi_hutang` varchar(255) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `nominal` int(11) NOT NULL,
  `is_lunas` tinyint(1) NOT NULL DEFAULT 0,
  `tanggal_lunas` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `imports`
--

CREATE TABLE `imports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipe` enum('shopee_order','shopee_income','tiktok_order','tiktok_income','brilink') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_hash` varchar(255) DEFAULT NULL,
  `periode_start` date DEFAULT NULL,
  `periode_end` date DEFAULT NULL,
  `total_rows` int(11) NOT NULL DEFAULT 0,
  `status` enum('pending','parsed','imported','failed') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `gaji_pokok` bigint(20) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `tanggal_masuk` date DEFAULT NULL,
  `no_hp` varchar(30) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporan_minus_karyawan`
--

CREATE TABLE `laporan_minus_karyawan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `karyawan_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `nominal` bigint(20) NOT NULL DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `sudah_dipotong` tinyint(1) NOT NULL DEFAULT 0,
  `gaji_karyawan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_10_14_195700_create_products_table', 1),
(5, '2026_10_14_195701_create_stores_table', 1),
(6, '2026_10_14_195702_create_imports_table', 1),
(7, '2026_10_14_195703_create_product_mappings_table', 1),
(8, '2026_10_14_195704_create_tiktok_order_items_table', 1),
(9, '2026_10_14_195705_create_tiktok_income_table', 1),
(10, '2026_10_14_195706_create_shopee_income_rows_table', 1),
(11, '2026_10_14_195707_create_sales_table', 1),
(12, '2026_10_14_210248_add_extra_columns_to_shopee_income_rows_table', 1),
(13, '2026_10_14_210531_add_raw_data_to_shopee_income_rows_table', 1),
(14, '2026_10_16_131300_add_gambar_to_products_table', 1),
(15, '2026_10_16_141402_create_shopee_order_items_table', 1),
(16, '2026_10_16_142012_update_imports_tipe_enum', 1),
(17, '2026_10_17_191559_create_brilink_reports_table', 1),
(18, '2026_10_17_191617_create_brilink_accounts_table', 1),
(19, '2026_10_17_191632_create_brilink_ppob_transactions_table', 1),
(20, '2026_10_17_191646_create_brilink_setoran_table', 1),
(21, '2026_10_17_191700_create_brilink_tarik_tunai_table', 1),
(22, '2026_10_17_191714_create_brilink_pengeluaran_table', 1),
(23, '2026_10_17_192933_add_brilink_to_stores_marketplace', 1),
(24, '2026_10_17_193236_add_brilink_to_imports_tipe', 1),
(25, '2026_10_18_143957_add_validasi_kas_to_brilink_reports_table', 1),
(26, '2026_10_18_233246_add_manual_validasi_kas_to_brilink_reports_table', 1),
(27, '2026_10_19_180432_add_manual_validasi_mutasi_rek_to_brilink_reports_table', 1),
(28, '2026_10_19_190743_add_manual_validasi_mutasi_fisik_to_brilink_reports_table', 1),
(29, '2026_10_20_021121_add_laba_kasir_pintar_to_brilink_reports_table', 1),
(30, '2026_10_20_021613_add_penambahan_brangkas_to_brilink_reports_table', 1),
(31, '2026_10_21_013509_add_keterangan_to_brilink_setoran_table', 1),
(32, '2026_10_21_014509_add_keterangan_columns_to_brilink_tarik_tunai_table', 1),
(33, '2026_10_22_015211_add_payment_tracking_to_brilink_pengeluaran', 1),
(34, '2026_10_22_022213_create_karyawan_table', 1),
(35, '2026_10_22_022217_create_gaji_karyawan_table', 1),
(36, '2026_10_22_022222_create_laporan_minus_karyawan_table', 1),
(37, '2026_10_23_003608_create_tabungan_accounts_table', 1),
(38, '2026_10_23_003615_create_tabungan_transactions_table', 1),
(39, '2026_10_23_124628_create_zakat_barangs_table', 1),
(40, '2026_10_23_133402_add_kategori_to_zakat_barang_table', 1),
(41, '2026_10_23_134900_create_zakat_kategoris_table', 1),
(42, '2026_10_24_024159_add_kas_awal_akhir_to_brilink_reports_table', 1),
(43, '2026_10_24_043117_add_stok_to_products_table', 1),
(44, '2026_10_25_015059_create_zakat_barang_mandiri_table', 1),
(45, '2026_10_25_015817_create_zakat_saldo_onlines_table', 1),
(46, '2026_10_25_021632_create_hutangs_table', 1),
(47, '2026_10_25_030953_create_zakat_pembayarans_table', 1),
(48, '2026_10_25_031436_add_detail_breakdown_to_zakat_pembayarans_table', 1),
(49, '2026_10_25_044323_add_komisi_to_gaji_karyawan_table', 1),
(50, '2026_10_25_045605_create_store_komisi_settings_table', 1),
(51, '2026_10_25_215746_add_tipe_komisi_to_store_komisi_settings_table', 1),
(52, '2026_10_25_220523_create_tiktok_live_sessions_table', 1),
(53, '2026_10_25_220535_create_tiktok_live_products_table', 1),
(54, '2026_10_25_221950_add_host_live_rate_to_store_komisi_settings_table', 1),
(55, '2026_10_26_005515_create_web_orders_snapshot_table', 1),
(56, '2026_10_26_005531_create_web_order_items_snapshot_table', 1),
(57, '2026_10_26_005540_create_web_sync_logs_table', 1),
(58, '2026_10_26_013604_add_komisi_host_live_global_to_store_komisi_settings_table', 1),
(59, '2026_10_26_041352_add_payment_proof_to_web_orders_snapshot_table', 1),
(60, '2026_10_26_042353_add_komisi_web_to_store_komisi_settings_table', 1),
(61, '2026_10_30_024734_create_brilink_saldo_pindahs_table', 2),
(62, '2026_10_30_054421_change_products_to_categories', 3),
(63, '2026_10_30_054448_drop_product_mappings_table', 3),
(64, '2026_10_30_062110_add_stok_and_drop_gambar_from_products', 4),
(65, '2026_10_30_063812_create_permission_tables', 5),
(66, '2026_10_30_063835_add_karyawan_fields_to_users_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_permissions`
--

INSERT INTO `model_has_permissions` (`permission_id`, `model_type`, `model_id`) VALUES
(3, 'App\\Models\\User', 2);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view shopee', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(2, 'view tiktok', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(3, 'view tiktok live', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(4, 'view web-sales', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(5, 'view brilink', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(6, 'view brilink saldo', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(7, 'view brilink pertumbuhan', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(8, 'view brilink pengeluaran', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(9, 'view brilink ppob', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(10, 'view brilink setoran', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(11, 'view brilink tarik-tunai', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(12, 'view brilink profit', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(13, 'view brilink brangkas', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(14, 'view tabungan', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(15, 'view hutang', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(16, 'view piutang', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(17, 'view zakat', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(18, 'view karyawan', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(19, 'view gaji', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(20, 'view minus', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(21, 'view import shopee', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(22, 'view import tiktok', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(23, 'view import brilink', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(24, 'view products', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(25, 'view stores', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sku_prefix` varchar(255) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `hpp` int(11) NOT NULL DEFAULT 0,
  `stok` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33'),
(2, 'karyawan', 'web', '2026-10-29 23:39:33', '2026-10-29 23:39:33');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `marketplace` enum('shopee','tiktok','tokopedia','lazada') NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `marketplace_sku` varchar(255) DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `harga_jual` int(11) NOT NULL DEFAULT 0,
  `diskon_platform` int(11) NOT NULL DEFAULT 0,
  `diskon_seller` int(11) NOT NULL DEFAULT 0,
  `subtotal` int(11) NOT NULL DEFAULT 0,
  `net_revenue` int(11) NOT NULL DEFAULT 0,
  `biaya_komisi` int(11) NOT NULL DEFAULT 0,
  `biaya_layanan` int(11) NOT NULL DEFAULT 0,
  `biaya_ongkir` int(11) NOT NULL DEFAULT 0,
  `biaya_afiliasi` int(11) NOT NULL DEFAULT 0,
  `biaya_promosi` int(11) NOT NULL DEFAULT 0,
  `biaya_pajak` int(11) NOT NULL DEFAULT 0,
  `biaya_lainnya` int(11) NOT NULL DEFAULT 0,
  `hpp_total` int(11) DEFAULT NULL,
  `profit` int(11) DEFAULT NULL,
  `status` enum('matched','orphan','pending','adjustment','refund','manual') NOT NULL DEFAULT 'pending',
  `reconciliation_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('8TavIlJUVxwlh0pvYSrUFMUtRqFZH5DqFFbG6Ebu', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiSUp6R1RPQWtUNnc2N0tSWTF5dzBBTHVVWjRtSXZ6cGlOVmRCMzR3UiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAva2FyeWF3YW4iO3M6NToicm91dGUiO3M6MTQ6Imthcnlhd2FuLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiNjYxOTM2Y2YzYjk5NDE2ZTYzMjZiOWQ2MWQ5NmRmZDY5YWRiN2ZkZGM3ZWEwZWQ5OTJmNDliMTYwNjVlZWZkZiI7fQ==', 1793318468),
('lze9OkebiHiXKH0ZcxXeyFCHYn1Zhx5IUOSHwieO', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiUHdvMHh1NTAwa3p5eXZ4VDQ5TDlFdUJaVjZMbVdtTzIwTGltcldJZCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI4OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvc2hvcGVlIjtzOjU6InJvdXRlIjtzOjEyOiJzaG9wZWUuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiIxNWQ4Mjg1NmJmZjI0MGRhZGJkMDQwMTAxNmQ4NTFlZjUyODY2ZmNlMWE4MzJkMWZlYTUyNzkzM2E1YzYxZTI1Ijt9', 1793317869),
('T9PVmlTZTvUn3cgreovwzRmmRCAK62uQYXEYZOV6', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiRWRGVVFPeHVDNEMxUzBaR3NESVRuRjVQdmxXREppZ2tkSUxlR2pqWSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI4OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvc2hvcGVlIjtzOjU6InJvdXRlIjtzOjEyOiJzaG9wZWUuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiIxNWQ4Mjg1NmJmZjI0MGRhZGJkMDQwMTAxNmQ4NTFlZjUyODY2ZmNlMWE4MzJkMWZlYTUyNzkzM2E1YzYxZTI1Ijt9', 1793317958),
('ZuGP3cUKAAKsiftWDA1yrQ3Brsidtk5UZkAN28vG', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoibENIWU1kd1p6elZEVkRKeFVHR1pvN28xNzA1aUZkR0VCTWNFWklBSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9icmlsaW5rL3NhbGRvLWtlc2VsdXJ1aGFuIjtzOjU6InJvdXRlIjtzOjI1OiJicmlsaW5rLnNhbGRvLWtlc2VsdXJ1aGFuIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMTVkODI4NTZiZmYyNDBkYWRiZDA0MDEwMTZkODUxZWY1Mjg2NmZjZTFhODMyZDFmZWE1Mjc5MzNhNWM2MWUyNSI7fQ==', 1793318319);

-- --------------------------------------------------------

--
-- Table structure for table `shopee_income_rows`
--

CREATE TABLE `shopee_income_rows` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `import_id` bigint(20) UNSIGNED NOT NULL,
  `tipe_baris` enum('order','sku') NOT NULL,
  `no_pesanan` varchar(255) NOT NULL,
  `no_pengajuan` varchar(255) DEFAULT NULL,
  `id_produk` varchar(255) DEFAULT NULL,
  `nama_produk` varchar(500) DEFAULT NULL,
  `waktu_pesanan_dibuat` date DEFAULT NULL,
  `tanggal_dana_dilepaskan` date DEFAULT NULL,
  `tipe_pesanan` varchar(255) DEFAULT NULL,
  `total_penghasilan` int(11) NOT NULL DEFAULT 0,
  `harga_produk` int(11) NOT NULL DEFAULT 0,
  `biaya_administrasi` int(11) NOT NULL DEFAULT 0,
  `biaya_proses_pesanan` int(11) NOT NULL DEFAULT 0,
  `biaya_gratis_ongkir_xtra` int(11) NOT NULL DEFAULT 0,
  `biaya_transaksi` int(11) NOT NULL DEFAULT 0,
  `biaya_komisi_ams` int(11) NOT NULL DEFAULT 0,
  `pph_22` int(11) NOT NULL DEFAULT 0,
  `ongkos_kirim_dibayarkan` int(11) NOT NULL DEFAULT 0,
  `gratis_ongkir_dari_shopee` int(11) NOT NULL DEFAULT 0,
  `jumlah_pengembalian_dana` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `biaya_kampanye` int(11) NOT NULL DEFAULT 0,
  `biaya_isi_saldo_otomatis` int(11) NOT NULL DEFAULT 0,
  `premi` int(11) NOT NULL DEFAULT 0,
  `fbs_fee` int(11) NOT NULL DEFAULT 0,
  `voucher_disponsor_penjual` int(11) NOT NULL DEFAULT 0,
  `cashback_koin_disponsori_penjual` int(11) NOT NULL DEFAULT 0,
  `diskon_produk_dari_shopee` int(11) NOT NULL DEFAULT 0,
  `voucher_cofund_disponsor_penjual` int(11) NOT NULL DEFAULT 0,
  `cashback_koin_cofund_disponsori_penjual` int(11) NOT NULL DEFAULT 0,
  `ongkos_kirim_pengembalian_barang` int(11) NOT NULL DEFAULT 0,
  `return_to_seller_fee` int(11) NOT NULL DEFAULT 0,
  `pengembalian_biaya_kirim` int(11) NOT NULL DEFAULT 0,
  `potongan_ongkos_kirim_dari_jasa_kirim` int(11) NOT NULL DEFAULT 0,
  `raw_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shopee_order_items`
--

CREATE TABLE `shopee_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `import_id` bigint(20) UNSIGNED NOT NULL,
  `no_pesanan` varchar(255) NOT NULL,
  `status_pesanan` varchar(255) DEFAULT NULL,
  `status_pembatalan` varchar(255) DEFAULT NULL,
  `no_resi` varchar(255) DEFAULT NULL,
  `opsi_pengiriman` varchar(255) DEFAULT NULL,
  `waktu_pesanan_dibuat` datetime DEFAULT NULL,
  `waktu_pembayaran` datetime DEFAULT NULL,
  `waktu_pesanan_selesai` datetime DEFAULT NULL,
  `tipe_pesanan` varchar(255) DEFAULT NULL,
  `metode_pembayaran` varchar(255) DEFAULT NULL,
  `sku_induk` varchar(255) DEFAULT NULL,
  `nama_produk` varchar(255) DEFAULT NULL,
  `sku_varian` varchar(255) DEFAULT NULL,
  `nama_variasi` varchar(255) DEFAULT NULL,
  `harga_awal` int(11) NOT NULL DEFAULT 0,
  `harga_setelah_diskon` int(11) NOT NULL DEFAULT 0,
  `jumlah` int(11) NOT NULL DEFAULT 0,
  `returned_quantity` int(11) NOT NULL DEFAULT 0,
  `subtotal_pesanan` int(11) NOT NULL DEFAULT 0,
  `total_diskon` int(11) NOT NULL DEFAULT 0,
  `diskon_dari_penjual` int(11) NOT NULL DEFAULT 0,
  `diskon_dari_shopee` int(11) NOT NULL DEFAULT 0,
  `ongkos_kirim_dibayar_pembeli` int(11) NOT NULL DEFAULT 0,
  `total_pembayaran` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `marketplace` enum('shopee','tiktok','tokopedia','lazada','brilink') NOT NULL,
  `nama_toko` varchar(255) NOT NULL,
  `seller_id` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `marketplace`, `nama_toko`, `seller_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'brilink', 'Brilink HM Pusat', NULL, 1, '2026-10-26 14:19:11', '2026-10-26 14:19:11'),
(2, 'brilink', 'Brilink Gunajaya', NULL, 1, '2026-10-26 14:19:18', '2026-10-26 14:19:18'),
(3, 'brilink', 'Brilink Cieurih', NULL, 1, '2026-10-26 14:19:23', '2026-10-26 14:19:23'),
(4, 'brilink', 'Brilink Atung', NULL, 1, '2026-10-26 14:19:32', '2026-10-26 14:19:32'),
(5, 'tiktok', 'Barokah Sport', NULL, 1, '2026-10-26 14:30:13', '2026-10-26 14:30:13'),
(6, 'shopee', 'HM Barokah', NULL, 1, '2026-10-29 15:23:29', '2026-10-29 15:23:29');

-- --------------------------------------------------------

--
-- Table structure for table `store_komisi_settings`
--

CREATE TABLE `store_komisi_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `komisi_per_transaksi` int(11) NOT NULL DEFAULT 0,
  `komisi_host_live_per_trx` int(11) NOT NULL DEFAULT 0,
  `komisi_host_live_global` int(11) NOT NULL DEFAULT 0 COMMENT 'Komisi host live global per transaksi (berlaku semua toko non-BRILink)',
  `komisi_web_per_trx` int(11) NOT NULL DEFAULT 0,
  `tipe_komisi_web` varchar(10) NOT NULL DEFAULT 'rp',
  `tipe_komisi` varchar(10) NOT NULL DEFAULT 'rp',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `store_komisi_settings`
--

INSERT INTO `store_komisi_settings` (`id`, `store_id`, `komisi_per_transaksi`, `komisi_host_live_per_trx`, `komisi_host_live_global`, `komisi_web_per_trx`, `tipe_komisi_web`, `tipe_komisi`, `created_at`, `updated_at`) VALUES
(1, 5, 0, 0, 2000, 2, 'persen', 'rp', '2026-10-26 14:31:46', '2026-10-26 14:31:46');

-- --------------------------------------------------------

--
-- Table structure for table `tabungan_accounts`
--

CREATE TABLE `tabungan_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tabungan_transactions`
--

CREATE TABLE `tabungan_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tabungan_account_id` bigint(20) UNSIGNED NOT NULL,
  `tipe` enum('setoran','penarikan') NOT NULL,
  `nominal` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tiktok_income`
--

CREATE TABLE `tiktok_income` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `import_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `jenis_transaksi` varchar(255) DEFAULT NULL,
  `waktu_pemesanan` date DEFAULT NULL,
  `waktu_pembayaran_pesanan` date DEFAULT NULL,
  `jumlah_penyelesaian_pembayaran` int(11) NOT NULL DEFAULT 0,
  `total_pendapatan` int(11) NOT NULL DEFAULT 0,
  `subtotal_setelah_diskon_penjual` int(11) NOT NULL DEFAULT 0,
  `subtotal_sebelum_diskon` int(11) NOT NULL DEFAULT 0,
  `diskon_penjual` int(11) NOT NULL DEFAULT 0,
  `total_biaya` int(11) NOT NULL DEFAULT 0,
  `biaya_komisi_platform` int(11) NOT NULL DEFAULT 0,
  `komisi_afiliasi` int(11) NOT NULL DEFAULT 0,
  `komisi_dinamis` int(11) NOT NULL DEFAULT 0,
  `biaya_growth_xtra` int(11) NOT NULL DEFAULT 0,
  `biaya_pemrosesan_pesanan` int(11) NOT NULL DEFAULT 0,
  `pph_22` int(11) NOT NULL DEFAULT 0,
  `ongkir` int(11) NOT NULL DEFAULT 0,
  `detail_produk_terjual` varchar(255) DEFAULT NULL,
  `detail_produk_terjual_parsed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detail_produk_terjual_parsed`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tiktok_live_products`
--

CREATE TABLE `tiktok_live_products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tiktok_live_session_id` bigint(20) UNSIGNED NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `ukuran` varchar(255) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tiktok_live_sessions`
--

CREATE TABLE `tiktok_live_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `karyawan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `total_transaksi` int(11) NOT NULL DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tiktok_order_items`
--

CREATE TABLE `tiktok_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `import_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `order_status` varchar(255) DEFAULT NULL,
  `order_substatus` varchar(255) DEFAULT NULL,
  `sku_id` varchar(255) DEFAULT NULL,
  `seller_sku` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `variation` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `sku_unit_original_price` int(11) NOT NULL DEFAULT 0,
  `sku_subtotal_before_discount` int(11) NOT NULL DEFAULT 0,
  `sku_platform_discount` int(11) NOT NULL DEFAULT 0,
  `sku_seller_discount` int(11) NOT NULL DEFAULT 0,
  `sku_subtotal_after_discount` int(11) NOT NULL DEFAULT 0,
  `order_amount` int(11) NOT NULL DEFAULT 0,
  `created_time` datetime DEFAULT NULL,
  `paid_time` datetime DEFAULT NULL,
  `shipped_time` datetime DEFAULT NULL,
  `delivered_time` datetime DEFAULT NULL,
  `cancelled_time` datetime DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `order_channel` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `karyawan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `can_login` tinyint(1) NOT NULL DEFAULT 0,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `karyawan_id`, `can_login`, `must_change_password`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, 0, 'Asep Mubarok', 'admin@barokahsport.com', NULL, '$2y$12$r5V5rAn7cI88WSByb1W76.m4gT6egDmmkFXPG2ksN7PJG3ATwlnRO', NULL, '2026-10-26 14:18:30', '2026-10-29 23:57:55'),
(2, NULL, 1, 1, 'SITI', 'siti@barokahsport.com', NULL, '$2y$12$/2qGd0ecw1kGEwE0WFIpburuG/1JUJAkDIQbGNh2bjGaqMx5xzJIS', NULL, '2026-10-29 23:50:36', '2026-10-29 23:50:36');

-- --------------------------------------------------------

--
-- Table structure for table `web_orders_snapshot`
--

CREATE TABLE `web_orders_snapshot` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `web_order_id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'completed',
  `payment_status` varchar(255) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `shipping_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `marketplace` varchar(255) DEFAULT NULL,
  `web_created_at` timestamp NULL DEFAULT NULL,
  `web_updated_at` timestamp NULL DEFAULT NULL,
  `synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `web_orders_snapshot`
--

INSERT INTO `web_orders_snapshot` (`id`, `web_order_id`, `order_number`, `customer_id`, `customer_name`, `customer_email`, `customer_phone`, `status`, `payment_status`, `payment_method`, `payment_proof`, `total_amount`, `shipping_cost`, `discount_amount`, `marketplace`, `web_created_at`, `web_updated_at`, `synced_at`, `created_at`, `updated_at`) VALUES
(1, 145, 'ORD-6ACF432BC3A2E', 4, 'Muhamad Rafli Repmihar Augst', NULL, '0895610411991', 'delivered', 'paid', 'BCA Transfer', 'payment-proofs/AvdAs7Wd4cTWxebon4LtieRjsFCqVRhX1mOBu2Ms.webp', 170600.00, 9100.00, 0.00, 'web', '2026-10-14 08:54:03', '2026-10-25 20:55:56', '2026-10-26 14:31:23', '2026-10-26 14:31:23', '2026-10-26 14:31:23');

-- --------------------------------------------------------

--
-- Table structure for table `web_order_items_snapshot`
--

CREATE TABLE `web_order_items_snapshot` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `web_order_id` bigint(20) UNSIGNED NOT NULL,
  `web_order_item_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `weight` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `web_order_items_snapshot`
--

INSERT INTO `web_order_items_snapshot` (`id`, `web_order_id`, `web_order_item_id`, `product_id`, `product_variant_id`, `product_name`, `sku`, `category_name`, `price`, `quantity`, `subtotal`, `weight`, `created_at`, `updated_at`) VALUES
(1, 145, 151, 135, 2472, 'HM Barokah - One Set Olahraga Seragam Setelan Olahraga Running Jogging Bahan Tebal', 'SET XTC NAVY-M', 'SETELAN', 161500.00, 1, 161500.00, 0.00, '2026-10-26 14:31:23', '2026-10-26 14:31:23');

-- --------------------------------------------------------

--
-- Table structure for table `web_sync_logs`
--

CREATE TABLE `web_sync_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('orders','products','customers') NOT NULL DEFAULT 'orders',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `finished_at` timestamp NULL DEFAULT NULL,
  `records_created` int(11) NOT NULL DEFAULT 0,
  `records_updated` int(11) NOT NULL DEFAULT 0,
  `records_skipped` int(11) NOT NULL DEFAULT 0,
  `status` enum('running','success','failed') NOT NULL DEFAULT 'running',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `web_sync_logs`
--

INSERT INTO `web_sync_logs` (`id`, `type`, `started_at`, `finished_at`, `records_created`, `records_updated`, `records_skipped`, `status`, `error_message`, `created_at`, `updated_at`) VALUES
(1, 'orders', '2026-10-26 14:31:23', '2026-10-26 14:31:23', 1, 0, 0, 'success', NULL, '2026-10-26 14:31:22', '2026-10-26 14:31:23');

-- --------------------------------------------------------

--
-- Table structure for table `zakat_barang`
--

CREATE TABLE `zakat_barang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `qty` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `harga_satuan` bigint(20) UNSIGNED NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zakat_barang_mandiri`
--

CREATE TABLE `zakat_barang_mandiri` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zakat_kategoris`
--

CREATE TABLE `zakat_kategoris` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zakat_pembayarans`
--

CREATE TABLE `zakat_pembayarans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `total_zakat` int(11) NOT NULL,
  `total_saldo` int(11) NOT NULL,
  `total_piutang` int(11) NOT NULL,
  `total_barang` int(11) NOT NULL,
  `total_hutang` int(11) NOT NULL,
  `total_tabungan` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `detail_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detail_breakdown`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zakat_saldo_onlines`
--

CREATE TABLE `zakat_saldo_onlines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `marketplace` varchar(255) NOT NULL,
  `saldo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brilink_accounts`
--
ALTER TABLE `brilink_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brilink_accounts_brilink_report_id_index` (`brilink_report_id`);

--
-- Indexes for table `brilink_pengeluaran`
--
ALTER TABLE `brilink_pengeluaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brilink_pengeluaran_brilink_report_id_index` (`brilink_report_id`),
  ADD KEY `brilink_pengeluaran_kategori_index` (`kategori`);

--
-- Indexes for table `brilink_ppob_transactions`
--
ALTER TABLE `brilink_ppob_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brilink_ppob_transactions_brilink_report_id_index` (`brilink_report_id`),
  ADD KEY `brilink_ppob_transactions_kategori_index` (`kategori`);

--
-- Indexes for table `brilink_reports`
--
ALTER TABLE `brilink_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brilink_reports_file_hash_unique` (`file_hash`),
  ADD KEY `brilink_reports_import_id_foreign` (`import_id`),
  ADD KEY `brilink_reports_store_id_periode_start_index` (`store_id`,`periode_start`),
  ADD KEY `brilink_reports_status_index` (`status`);

--
-- Indexes for table `brilink_saldo_pindah`
--
ALTER TABLE `brilink_saldo_pindah`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brilink_saldo_pindah_brilink_report_id_foreign` (`brilink_report_id`);

--
-- Indexes for table `brilink_setoran`
--
ALTER TABLE `brilink_setoran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brilink_setoran_brilink_report_id_index` (`brilink_report_id`);

--
-- Indexes for table `brilink_tarik_tunai`
--
ALTER TABLE `brilink_tarik_tunai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brilink_tarik_tunai_brilink_report_id_index` (`brilink_report_id`),
  ADD KEY `brilink_tarik_tunai_tipe_index` (`tipe`);

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
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `gaji_karyawan`
--
ALTER TABLE `gaji_karyawan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gaji_karyawan_karyawan_id_periode_unique` (`karyawan_id`,`periode`),
  ADD KEY `gaji_karyawan_periode_index` (`periode`);

--
-- Indexes for table `hutangs`
--
ALTER TABLE `hutangs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `imports`
--
ALTER TABLE `imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `imports_store_id_foreign` (`store_id`),
  ADD KEY `imports_tipe_periode_start_periode_end_index` (`tipe`,`periode_start`,`periode_end`);

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
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `karyawan_store_id_foreign` (`store_id`);

--
-- Indexes for table `laporan_minus_karyawan`
--
ALTER TABLE `laporan_minus_karyawan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laporan_minus_karyawan_gaji_karyawan_id_foreign` (`gaji_karyawan_id`),
  ADD KEY `laporan_minus_karyawan_karyawan_id_sudah_dipotong_index` (`karyawan_id`,`sudah_dipotong`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_sku_internal_unique` (`sku_prefix`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_product_id_foreign` (`product_id`),
  ADD KEY `sales_store_id_tanggal_index` (`store_id`,`tanggal`),
  ADD KEY `sales_status_index` (`status`),
  ADD KEY `sales_order_id_index` (`order_id`),
  ADD KEY `sales_marketplace_sku_index` (`marketplace_sku`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shopee_income_rows`
--
ALTER TABLE `shopee_income_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shopee_income_rows_import_id_foreign` (`import_id`),
  ADD KEY `shopee_income_rows_no_pesanan_index` (`no_pesanan`),
  ADD KEY `shopee_income_rows_id_produk_index` (`id_produk`);

--
-- Indexes for table `shopee_order_items`
--
ALTER TABLE `shopee_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shopee_order_items_import_id_foreign` (`import_id`),
  ADD KEY `shopee_order_items_no_pesanan_index` (`no_pesanan`),
  ADD KEY `shopee_order_items_sku_induk_index` (`sku_induk`),
  ADD KEY `shopee_order_items_sku_varian_index` (`sku_varian`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `store_komisi_settings`
--
ALTER TABLE `store_komisi_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_komisi_settings_store_id_foreign` (`store_id`);

--
-- Indexes for table `tabungan_accounts`
--
ALTER TABLE `tabungan_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tabungan_accounts_nama_index` (`nama`),
  ADD KEY `tabungan_accounts_is_active_index` (`is_active`);

--
-- Indexes for table `tabungan_transactions`
--
ALTER TABLE `tabungan_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tabungan_transactions_tabungan_account_id_tanggal_index` (`tabungan_account_id`,`tanggal`),
  ADD KEY `tabungan_transactions_tipe_tanggal_index` (`tipe`,`tanggal`);

--
-- Indexes for table `tiktok_income`
--
ALTER TABLE `tiktok_income`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tiktok_income_import_id_foreign` (`import_id`),
  ADD KEY `tiktok_income_order_id_index` (`order_id`);

--
-- Indexes for table `tiktok_live_products`
--
ALTER TABLE `tiktok_live_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tiktok_live_products_tiktok_live_session_id_foreign` (`tiktok_live_session_id`);

--
-- Indexes for table `tiktok_live_sessions`
--
ALTER TABLE `tiktok_live_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tiktok_live_sessions_store_id_foreign` (`store_id`),
  ADD KEY `tiktok_live_sessions_karyawan_id_foreign` (`karyawan_id`);

--
-- Indexes for table `tiktok_order_items`
--
ALTER TABLE `tiktok_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tiktok_order_items_import_id_foreign` (`import_id`),
  ADD KEY `tiktok_order_items_order_id_index` (`order_id`),
  ADD KEY `tiktok_order_items_sku_id_index` (`sku_id`),
  ADD KEY `tiktok_order_items_seller_sku_index` (`seller_sku`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_karyawan_id_foreign` (`karyawan_id`);

--
-- Indexes for table `web_orders_snapshot`
--
ALTER TABLE `web_orders_snapshot`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `web_orders_snapshot_web_order_id_unique` (`web_order_id`),
  ADD KEY `web_orders_snapshot_web_created_at_status_index` (`web_created_at`,`status`),
  ADD KEY `web_orders_snapshot_order_number_index` (`order_number`),
  ADD KEY `web_orders_snapshot_status_index` (`status`),
  ADD KEY `web_orders_snapshot_web_created_at_index` (`web_created_at`);

--
-- Indexes for table `web_order_items_snapshot`
--
ALTER TABLE `web_order_items_snapshot`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `web_order_items_snapshot_web_order_item_id_unique` (`web_order_item_id`),
  ADD KEY `web_order_items_snapshot_web_order_id_product_id_index` (`web_order_id`,`product_id`),
  ADD KEY `web_order_items_snapshot_web_order_id_index` (`web_order_id`),
  ADD KEY `web_order_items_snapshot_product_id_index` (`product_id`),
  ADD KEY `web_order_items_snapshot_product_variant_id_index` (`product_variant_id`),
  ADD KEY `web_order_items_snapshot_sku_index` (`sku`);

--
-- Indexes for table `web_sync_logs`
--
ALTER TABLE `web_sync_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `web_sync_logs_status_index` (`status`);

--
-- Indexes for table `zakat_barang`
--
ALTER TABLE `zakat_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `zakat_barang_nama_barang_index` (`nama_barang`),
  ADD KEY `zakat_barang_kategori_index` (`kategori`);

--
-- Indexes for table `zakat_barang_mandiri`
--
ALTER TABLE `zakat_barang_mandiri`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zakat_kategoris`
--
ALTER TABLE `zakat_kategoris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zakat_kategoris_nama_unique` (`nama`);

--
-- Indexes for table `zakat_pembayarans`
--
ALTER TABLE `zakat_pembayarans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zakat_saldo_onlines`
--
ALTER TABLE `zakat_saldo_onlines`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brilink_accounts`
--
ALTER TABLE `brilink_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=668;

--
-- AUTO_INCREMENT for table `brilink_pengeluaran`
--
ALTER TABLE `brilink_pengeluaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=556;

--
-- AUTO_INCREMENT for table `brilink_ppob_transactions`
--
ALTER TABLE `brilink_ppob_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1997;

--
-- AUTO_INCREMENT for table `brilink_reports`
--
ALTER TABLE `brilink_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `brilink_saldo_pindah`
--
ALTER TABLE `brilink_saldo_pindah`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `brilink_setoran`
--
ALTER TABLE `brilink_setoran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=626;

--
-- AUTO_INCREMENT for table `brilink_tarik_tunai`
--
ALTER TABLE `brilink_tarik_tunai`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3167;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gaji_karyawan`
--
ALTER TABLE `gaji_karyawan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hutangs`
--
ALTER TABLE `hutangs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `imports`
--
ALTER TABLE `imports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `laporan_minus_karyawan`
--
ALTER TABLE `laporan_minus_karyawan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4186;

--
-- AUTO_INCREMENT for table `shopee_income_rows`
--
ALTER TABLE `shopee_income_rows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1597;

--
-- AUTO_INCREMENT for table `shopee_order_items`
--
ALTER TABLE `shopee_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=995;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `store_komisi_settings`
--
ALTER TABLE `store_komisi_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tabungan_accounts`
--
ALTER TABLE `tabungan_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tabungan_transactions`
--
ALTER TABLE `tabungan_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiktok_income`
--
ALTER TABLE `tiktok_income`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1547;

--
-- AUTO_INCREMENT for table `tiktok_live_products`
--
ALTER TABLE `tiktok_live_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiktok_live_sessions`
--
ALTER TABLE `tiktok_live_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiktok_order_items`
--
ALTER TABLE `tiktok_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1469;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `web_orders_snapshot`
--
ALTER TABLE `web_orders_snapshot`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `web_order_items_snapshot`
--
ALTER TABLE `web_order_items_snapshot`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `web_sync_logs`
--
ALTER TABLE `web_sync_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `zakat_barang`
--
ALTER TABLE `zakat_barang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zakat_barang_mandiri`
--
ALTER TABLE `zakat_barang_mandiri`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zakat_kategoris`
--
ALTER TABLE `zakat_kategoris`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zakat_pembayarans`
--
ALTER TABLE `zakat_pembayarans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zakat_saldo_onlines`
--
ALTER TABLE `zakat_saldo_onlines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `brilink_accounts`
--
ALTER TABLE `brilink_accounts`
  ADD CONSTRAINT `brilink_accounts_brilink_report_id_foreign` FOREIGN KEY (`brilink_report_id`) REFERENCES `brilink_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brilink_pengeluaran`
--
ALTER TABLE `brilink_pengeluaran`
  ADD CONSTRAINT `brilink_pengeluaran_brilink_report_id_foreign` FOREIGN KEY (`brilink_report_id`) REFERENCES `brilink_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brilink_ppob_transactions`
--
ALTER TABLE `brilink_ppob_transactions`
  ADD CONSTRAINT `brilink_ppob_transactions_brilink_report_id_foreign` FOREIGN KEY (`brilink_report_id`) REFERENCES `brilink_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brilink_reports`
--
ALTER TABLE `brilink_reports`
  ADD CONSTRAINT `brilink_reports_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `brilink_reports_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brilink_saldo_pindah`
--
ALTER TABLE `brilink_saldo_pindah`
  ADD CONSTRAINT `brilink_saldo_pindah_brilink_report_id_foreign` FOREIGN KEY (`brilink_report_id`) REFERENCES `brilink_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brilink_setoran`
--
ALTER TABLE `brilink_setoran`
  ADD CONSTRAINT `brilink_setoran_brilink_report_id_foreign` FOREIGN KEY (`brilink_report_id`) REFERENCES `brilink_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brilink_tarik_tunai`
--
ALTER TABLE `brilink_tarik_tunai`
  ADD CONSTRAINT `brilink_tarik_tunai_brilink_report_id_foreign` FOREIGN KEY (`brilink_report_id`) REFERENCES `brilink_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gaji_karyawan`
--
ALTER TABLE `gaji_karyawan`
  ADD CONSTRAINT `gaji_karyawan_karyawan_id_foreign` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `imports`
--
ALTER TABLE `imports`
  ADD CONSTRAINT `imports_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD CONSTRAINT `karyawan_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `laporan_minus_karyawan`
--
ALTER TABLE `laporan_minus_karyawan`
  ADD CONSTRAINT `laporan_minus_karyawan_gaji_karyawan_id_foreign` FOREIGN KEY (`gaji_karyawan_id`) REFERENCES `gaji_karyawan` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `laporan_minus_karyawan_karyawan_id_foreign` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shopee_income_rows`
--
ALTER TABLE `shopee_income_rows`
  ADD CONSTRAINT `shopee_income_rows_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shopee_order_items`
--
ALTER TABLE `shopee_order_items`
  ADD CONSTRAINT `shopee_order_items_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `store_komisi_settings`
--
ALTER TABLE `store_komisi_settings`
  ADD CONSTRAINT `store_komisi_settings_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tabungan_transactions`
--
ALTER TABLE `tabungan_transactions`
  ADD CONSTRAINT `tabungan_transactions_tabungan_account_id_foreign` FOREIGN KEY (`tabungan_account_id`) REFERENCES `tabungan_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tiktok_income`
--
ALTER TABLE `tiktok_income`
  ADD CONSTRAINT `tiktok_income_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tiktok_live_products`
--
ALTER TABLE `tiktok_live_products`
  ADD CONSTRAINT `tiktok_live_products_tiktok_live_session_id_foreign` FOREIGN KEY (`tiktok_live_session_id`) REFERENCES `tiktok_live_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tiktok_live_sessions`
--
ALTER TABLE `tiktok_live_sessions`
  ADD CONSTRAINT `tiktok_live_sessions_karyawan_id_foreign` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tiktok_live_sessions_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tiktok_order_items`
--
ALTER TABLE `tiktok_order_items`
  ADD CONSTRAINT `tiktok_order_items_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_karyawan_id_foreign` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
