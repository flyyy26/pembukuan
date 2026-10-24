-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2026 at 12:43 AM
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

--
-- Dumping data for table `brilink_accounts`
--

INSERT INTO `brilink_accounts` (`id`, `brilink_report_id`, `nama_rekening`, `keterangan`, `saldo_awal`, `saldo_akhir`, `selisih`, `created_at`, `updated_at`) VALUES
(1, 1, 'KAS', 'KAS', 122384000, 62253900, -60130100, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(2, 1, 'MANG SALAREA', 'PULSA', 936358, 936358, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(3, 1, 'LINK AJA', 'PULSA', 1880972, 1860474, -20498, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(4, 1, 'MITRALINK', 'PPOB', 10612, 10612, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(5, 1, 'PAYFAZZ', 'PPOB', 1408493, 320723, -1087770, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(6, 1, 'BUKU WARUNG', 'PPOB', 875467, 709247, -166220, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(7, 1, 'FASTPAY', 'PPOB', 1362516, 4160126, 2797610, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(8, 1, 'SALDO EDC AA', 'BRILINK', 19075137, 58663047, 39587910, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(9, 1, 'SALDO EDC TETEH', '', 5830734, 37124888, 31294154, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(10, 1, 'SALDO BRI 119 PENAMPUNGAN', 'BRILINK', 4315817, 104111817, 99796000, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(11, 1, 'SALDO BRI 1417 PENARIKAN', 'BRILINK', 10577729, 30577729, 20000000, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(12, 1, 'BCA', 'MBANKING', 70856563, 75299563, 4443000, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(13, 1, 'MANDIRI', 'MBANKING', 7656180, 20900680, 13244500, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(14, 1, 'BNI', 'MBANKING', 10133234, 10607234, 474000, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(15, 1, 'BSI', 'MBANKING', 600258, 600258, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(16, 1, 'BRANGKAS', 'BRANGKAS', 200000000, 0, -200000000, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(17, 1, 'SALDO BARANG', 'POS', 77973961, 77776314, -197647, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(18, 2, 'KAS', 'KAS', 32875600, 26599800, -6275800, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(19, 2, 'MANG SALAREA', 'PULSA', 0, 0, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(20, 2, 'LINK AJA', 'PULSA', 491075, 467477, -23598, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(21, 2, 'MITRALINK', 'PPOB', 0, 0, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(22, 2, 'PAYFAZZ', 'PPOB', 1437504, 8778004, 7340500, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(23, 2, 'BUKU WARUNG', 'PPOB', 470067, 396957, -73110, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(24, 2, 'FASTPAY', 'PPOB', 213207, 213207, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(25, 2, 'SALDO EDC GN 6833', 'BRILINK', 3303345, 0, -3303345, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(26, 2, 'SALDO EDC TETEH', '', 0, 0, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(27, 2, 'SALDO BRI 5539 PENAMPUNG', 'BRILINK', 5423674, 0, -5423674, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(28, 2, 'SALDO BRI 1417 PENARIKAN', 'BRILINK', 0, 0, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(29, 2, 'BCA', 'MBANKING', 0, 0, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(30, 2, 'MANDIRI', 'MBANKING', 0, 0, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(31, 2, 'BNI', 'MBANKING', 0, 0, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(32, 2, 'BSI', 'MBANKING', 0, 0, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(33, 2, 'BRANGKAS', 'BRANGKAS', 0, 0, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(34, 2, 'SALDO BARANG', 'POS', 10312031, 10307348, -4683, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(35, 2, 'SALDO EDC AA', 'BRILINK', 0, 37382945, 37382945, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(36, 2, 'SALDO BRI 5539 PENAMPUNGAN', 'BRILINK', 0, 8845474, 8845474, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(37, 3, 'KAS', 'KAS', 29331000, 29592000, 261000, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(38, 3, 'MANG SALAREA', 'PULSA', 0, 0, 0, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(39, 3, 'LINK AJA', 'PULSA', 41125, 41125, 0, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(40, 3, 'MITRALINK', 'PPOB', 0, 0, 0, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(41, 3, 'PAYFAZZ', 'PPOB', 5626570, 786865, -4839705, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(42, 3, 'BUKU WARUNG', 'PPOB', 0, 0, 0, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(43, 3, 'FASTPAY', 'PPOB', 4593226, 4495326, -97900, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(44, 3, 'SALDO EDC CRH', 'BRILINK', 2666918, 8021308, 5354390, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(45, 3, 'SALDO EDC TETEH', '', 0, 0, 0, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(46, 3, 'SALDO BRI 530 PENAMPUNGAN', 'BRILINK', 15399508, 294508, -15105000, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(47, 3, 'SALDO BRI 1417 PENARIKAN', 'BRILINK', 0, 0, 0, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(48, 3, 'BCA', 'MBANKING', 0, 0, 0, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(49, 3, 'MANDIRI', 'MBANKING', 0, 0, 0, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(50, 3, 'BNI', 'MBANKING', 0, 0, 0, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(51, 3, 'BSI', 'MBANKING', 0, 0, 0, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(52, 3, 'BRANGKAS', 'BRANGKAS', 0, 0, 0, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(53, 3, 'SALDO BARANG', 'POS', 0, 0, 0, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(54, 4, 'KAS', 'KAS', 45137000, 18138500, -26998500, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(55, 4, 'MANG SALAREA', 'PULSA', 0, 0, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(56, 4, 'LINK AJA', 'PULSA', 470137, 470137, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(57, 4, 'MITRALINK', 'PPOB', 0, 0, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(58, 4, 'PAYFAZZ', 'PPOB', 3009968, 4684318, 1674350, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(59, 4, 'BUKU WARUNG', 'PPOB', 355150, 282040, -73110, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(60, 4, 'FASTPAY', 'PPOB', 3935008, 4058308, 123300, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(61, 4, 'SALDO EDC 6535', 'BRILINK', 4676680, 0, -4676680, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(62, 4, 'SALDO EDC TETEH', '', 0, 0, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(63, 4, 'SALDO BRI 4468 PENAMPUNGAN', 'BRILINK', 305687, 8047687, 7742000, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(64, 4, 'SALDO BRI 1417 PENARIKAN', 'BRILINK', 0, 0, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(65, 4, 'BCA', 'MBANKING', 0, 0, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(66, 4, 'MANDIRI', 'MBANKING', 0, 0, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(67, 4, 'BNI AA', 'MBANKING', 3475762, 0, -3475762, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(68, 4, 'BSI', 'MBANKING', 0, 0, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(69, 4, 'BRANGKAS', 'BRANGKAS', 0, 0, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(70, 4, 'SALDO BARANG', 'POS', 5900731, 5891898, -8833, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(71, 4, 'SALDO EDC AA', 'BRILINK', 0, -31975740, -31975740, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(72, 4, 'BNI', 'MBANKING', 0, 13012262, 13012262, '2026-10-24 20:35:12', '2026-10-24 20:35:12');

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

--
-- Dumping data for table `brilink_pengeluaran`
--

INSERT INTO `brilink_pengeluaran` (`id`, `brilink_report_id`, `keterangan`, `nominal_kas`, `kategori`, `is_paid`, `paid_at`, `paid_amount`, `created_at`, `updated_at`) VALUES
(1, 1, 'tf kain aa', 583520, 'OP KONVEKSI', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(2, 1, 'fasfayy', 5001500, 'TOP UP PPOB', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(3, 1, 'paypazz', 10001500, 'TOP UP PPOB', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(4, 1, 'tf m iyus', 6045000, 'PIUTANG IYUS', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(5, 1, 'paypazz', 10001500, 'TOP UP PPOB', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(6, 1, 'kas gn', 21000000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(7, 1, 'kas crh', 10000000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(8, 1, 'bu haji', 100000, 'PIUTANG BU HAJI', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(9, 1, 'benang', 41000, 'OP KONVEKSI', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(10, 1, 'kas at', 40000000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(11, 1, 'bu haji', 200000, 'PIUTANG BU HAJI', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(12, 1, 'kain keras', 300000, 'OP KONVEKSI', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(13, 1, 'bu haji', 300000, 'PIUTANG BU HAJI', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(14, 1, 'm iyus', 50000, 'PIUTANG IYUS', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(15, 1, 'kas gn', 20000000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(16, 1, 'tsroll', 4500, 'PRIVE', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(17, 1, 'ts', 22000, 'PRIVE', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(18, 1, 'kas gn', 30000000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(19, 1, 'kas at', 30000000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(20, 1, 'm yayat s', 346500, 'OP KONVEKSI', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(21, 1, 'm iyus', 50000, 'PIUTANG IYUS', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(22, 1, 'kas at', 21000000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(23, 1, 'uang makan 14x18', 252000, 'OP KONVEKSI', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(24, 1, 'buhaji', 315000, 'PIUTANG BU HAJI', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(25, 1, 'm iyus', 230000, 'PIUTANG IYUS', 0, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(26, 2, 'paypaz', 5001500, 'TOP UP PPOB', 0, NULL, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(27, 2, 'koin kasir pintar', 61900, 'TOP UP PPOB', 0, NULL, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(28, 2, 'paypaz', 10001500, 'TOP UP PPOB', 0, NULL, 0, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(29, 4, 'galon', 23500, 'PRIVE', 0, NULL, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(30, 4, 'payfazz', 4001500, 'TOP UP PPOB', 0, NULL, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(31, 4, 'fastpay', 3001500, 'TOP UP PPOB', 0, NULL, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(32, 4, 'kas hm', 30000000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-24 20:35:12', '2026-10-24 20:35:12');

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

--
-- Dumping data for table `brilink_ppob_transactions`
--

INSERT INTO `brilink_ppob_transactions` (`id`, `brilink_report_id`, `kategori`, `nominal`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pulsa & Deposit', 22000, 'Transaksi baris 4', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(2, 1, 'Barang Kasir Pintar', 48500, 'Transaksi baris 4', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(3, 1, 'Barang Titip Jual', 16000, 'serba2k 8', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(4, 1, 'Fastpay', 1610000, 'Transaksi baris 4', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(5, 1, 'Payfazz', 655000, 'Transaksi baris 4', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(6, 1, 'Buku Warung', 24000, 'Transaksi baris 4', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(7, 1, 'Penambahan Kas', 100000000, 'BRANGKAS', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(8, 1, 'Barang Kasir Pintar', 7000, 'Transaksi baris 5', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(9, 1, 'Barang Titip Jual', 10000, 'parfum10k', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(10, 1, 'Fastpay', 278000, 'Transaksi baris 5', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(11, 1, 'Payfazz', 105000, 'Transaksi baris 5', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(12, 1, 'Buku Warung', 24000, 'Transaksi baris 5', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(13, 1, 'Penambahan Kas', 100000000, 'BRANGKAS', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(14, 1, 'Barang Kasir Pintar', 4500, 'Transaksi baris 6', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(15, 1, 'Barang Titip Jual', 5000, 'salman', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(16, 1, 'Fastpay', 20000, 'Transaksi baris 6', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(17, 1, 'Payfazz', 103000, 'Transaksi baris 6', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(18, 1, 'Buku Warung', 24000, 'Transaksi baris 6', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(19, 1, 'Penambahan Kas', 200000000, 'BRANGKAS', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(20, 1, 'Barang Kasir Pintar', 4000, 'Transaksi baris 7', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(21, 1, 'Barang Titip Jual', 5500, 'mizone', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(22, 1, 'Fastpay', 33940, 'Transaksi baris 7', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(23, 1, 'Payfazz', 303000, 'Transaksi baris 7', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(24, 1, 'Buku Warung', 104000, 'Transaksi baris 7', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(25, 1, 'Penambahan Kas', 30000000, 'OUTLET AT', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(26, 1, 'Barang Kasir Pintar', 4000, 'Transaksi baris 8', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(27, 1, 'Barang Titip Jual', 18000, 'v tsel 7/5', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(28, 1, 'Fastpay', 223000, 'Transaksi baris 8', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(29, 1, 'Payfazz', 493000, 'Transaksi baris 8', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(30, 1, 'Barang Kasir Pintar', 22000, 'Transaksi baris 9', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(31, 1, 'Fastpay', 26000, 'Transaksi baris 9', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(32, 1, 'Payfazz', 28000, 'Transaksi baris 9', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(33, 1, 'Barang Kasir Pintar', 12000, 'Transaksi baris 10', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(34, 1, 'Fastpay', 20000, 'Transaksi baris 10', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(35, 1, 'Payfazz', 263000, 'Transaksi baris 10', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(36, 1, 'Barang Kasir Pintar', 5500, 'Transaksi baris 11', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(37, 1, 'Payfazz', 1004000, 'Transaksi baris 11', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(38, 1, 'Barang Kasir Pintar', 20000, 'Transaksi baris 12', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(39, 1, 'Payfazz', 103000, 'Transaksi baris 12', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(40, 1, 'Barang Kasir Pintar', 15000, 'Transaksi baris 13', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(41, 1, 'Payfazz', 583520, 'Transaksi baris 13', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(42, 1, 'Barang Kasir Pintar', 1535000, 'Transaksi baris 14', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(43, 1, 'Payfazz', 45000, 'Transaksi baris 14', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(44, 1, 'Barang Kasir Pintar', 2500, 'Transaksi baris 15', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(45, 1, 'Payfazz', 1019000, 'Transaksi baris 15', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(46, 1, 'Barang Kasir Pintar', 2500, 'Transaksi baris 16', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(47, 1, 'Payfazz', 403000, 'Transaksi baris 16', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(48, 1, 'Barang Kasir Pintar', 15000, 'Transaksi baris 17', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(49, 1, 'Payfazz', 203000, 'Transaksi baris 17', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(50, 1, 'Barang Kasir Pintar', 10000, 'Transaksi baris 18', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(51, 1, 'Payfazz', 283000, 'Transaksi baris 18', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(52, 1, 'Barang Kasir Pintar', 7000, 'Transaksi baris 19', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(53, 1, 'Payfazz', 55000, 'Transaksi baris 19', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(54, 1, 'Barang Kasir Pintar', 4000, 'Transaksi baris 20', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(55, 1, 'Payfazz', 2505000, 'Transaksi baris 20', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(56, 1, 'Barang Kasir Pintar', 2000, 'Transaksi baris 21', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(57, 1, 'Payfazz', 253000, 'Transaksi baris 21', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(58, 1, 'Barang Kasir Pintar', 72000, 'Transaksi baris 22', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(59, 1, 'Payfazz', 147500, 'Transaksi baris 22', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(60, 1, 'Barang Kasir Pintar', 4500, 'Transaksi baris 23', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(61, 1, 'Payfazz', 6045000, 'Transaksi baris 23', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(62, 1, 'Barang Kasir Pintar', 2500, 'Transaksi baris 24', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(63, 1, 'Payfazz', 903000, 'Transaksi baris 24', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(64, 1, 'Barang Kasir Pintar', 9000, 'Transaksi baris 25', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(65, 1, 'Payfazz', 864000, 'Transaksi baris 25', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(66, 1, 'Barang Kasir Pintar', 4500, 'Transaksi baris 26', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(67, 1, 'Payfazz', 443000, 'Transaksi baris 26', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(68, 1, 'Barang Kasir Pintar', 15000, 'Transaksi baris 27', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(69, 1, 'Payfazz', 1184000, 'Transaksi baris 27', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(70, 1, 'Barang Kasir Pintar', 10000, 'Transaksi baris 28', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(71, 1, 'Payfazz', 453000, 'Transaksi baris 28', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(72, 1, 'Barang Kasir Pintar', 20000, 'Transaksi baris 29', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(73, 1, 'Payfazz', 205000, 'Transaksi baris 29', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(74, 1, 'Barang Kasir Pintar', 35000, 'Transaksi baris 30', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(75, 1, 'Payfazz', 203000, 'Transaksi baris 30', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(76, 1, 'Payfazz', 253000, 'Transaksi baris 31', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(77, 1, 'Payfazz', 310000, 'Transaksi baris 32', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(78, 1, 'Payfazz', 55000, 'Transaksi baris 33', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(79, 1, 'Payfazz', 153000, 'Transaksi baris 34', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(80, 1, 'Payfazz', 653000, 'Transaksi baris 35', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(81, 1, 'Payfazz', 200000, 'Transaksi baris 36', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(82, 1, 'Payfazz', 53000, 'Transaksi baris 37', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(83, 1, 'Payfazz', 405000, 'Transaksi baris 38', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(84, 1, 'Payfazz', 273000, 'Transaksi baris 39', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(85, 2, 'Pulsa & Deposit', 25000, 'Transaksi baris 4', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(86, 2, 'Barang Kasir Pintar', 3000, 'Transaksi baris 4', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(87, 2, 'Payfazz', 32000, 'Transaksi baris 4', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(88, 2, 'Buku Warung', 24000, 'Transaksi baris 4', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(89, 2, 'Penambahan Kas', 21000000, 'Transaksi baris 4', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(90, 2, 'Barang Kasir Pintar', 5000, 'Transaksi baris 5', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(91, 2, 'Payfazz', 1004000, 'Transaksi baris 5', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(92, 2, 'Buku Warung', 54000, 'Transaksi baris 5', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(93, 2, 'Penambahan Kas', 20000000, 'OUTLET HM', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(94, 2, 'Payfazz', 88000, 'Transaksi baris 6', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(95, 2, 'Penambahan Kas', 30000000, 'OUTLET HM', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(96, 2, 'Payfazz', 215000, 'Transaksi baris 7', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(97, 2, 'Payfazz', 153000, 'Transaksi baris 8', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(98, 2, 'Payfazz', 103000, 'Transaksi baris 9', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(99, 2, 'Payfazz', 273000, 'Transaksi baris 10', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(100, 2, 'Payfazz', 28000, 'Transaksi baris 11', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(101, 2, 'Payfazz', 210000, 'Transaksi baris 12', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(102, 2, 'Payfazz', 503000, 'Transaksi baris 13', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(103, 2, 'Payfazz', 1204000, 'Transaksi baris 14', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(104, 2, 'Payfazz', 452000, 'Transaksi baris 15', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(105, 2, 'Payfazz', 403000, 'Transaksi baris 16', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(106, 2, 'Payfazz', 233000, 'Transaksi baris 17', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(107, 2, 'Payfazz', 113000, 'Transaksi baris 18', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(108, 2, 'Payfazz', 205000, 'Transaksi baris 19', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(109, 2, 'Payfazz', 405000, 'Transaksi baris 20', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(110, 2, 'Payfazz', 1004000, 'Transaksi baris 21', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(111, 2, 'Payfazz', 605000, 'Transaksi baris 22', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(112, 2, 'Payfazz', 485000, 'Transaksi baris 23', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(113, 3, 'Fastpay', 100000, 'Transaksi baris 4', '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(114, 3, 'Payfazz', 203000, 'Transaksi baris 4', '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(115, 3, 'Payfazz', 185000, 'Transaksi baris 5', '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(116, 3, 'Payfazz', 405000, 'Transaksi baris 6', '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(117, 3, 'Payfazz', 1354000, 'Transaksi baris 7', '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(118, 3, 'Payfazz', 1105000, 'Transaksi baris 8', '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(119, 3, 'Payfazz', 653000, 'Transaksi baris 9', '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(120, 3, 'Payfazz', 160000, 'Transaksi baris 10', '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(121, 3, 'Payfazz', 714000, 'Transaksi baris 11', '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(122, 3, 'Payfazz', 54000, 'Transaksi baris 12', '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(123, 3, 'Payfazz', 53000, 'Transaksi baris 13', '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(124, 4, 'Barang Kasir Pintar', 44000, 'Transaksi baris 4', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(125, 4, 'Barang Titip Jual', 30000, 'PARFUM 10K', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(126, 4, 'Fastpay', 103000, 'Transaksi baris 4', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(127, 4, 'Payfazz', 505000, 'Transaksi baris 4', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(128, 4, 'Buku Warung', 24000, 'Transaksi baris 4', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(129, 4, 'Penambahan Kas', 40000000, 'OUTLET HM', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(130, 4, 'Barang Kasir Pintar', 12000, 'Transaksi baris 5', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(131, 4, 'Barang Titip Jual', 30000, 'PARFUM', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(132, 4, 'Fastpay', 783000, 'Transaksi baris 5', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(133, 4, 'Payfazz', 610000, 'Transaksi baris 5', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(134, 4, 'Buku Warung', 54000, 'Transaksi baris 5', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(135, 4, 'Penambahan Kas', 30000000, 'OUTLET HM', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(136, 4, 'Barang Titip Jual', 10000, 'SERBA 10K', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(137, 4, 'Fastpay', 503000, 'Transaksi baris 6', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(138, 4, 'Payfazz', 503000, 'Transaksi baris 6', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(139, 4, 'Penambahan Kas', 21000000, 'OUTLET HM', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(140, 4, 'Fastpay', 803000, 'Transaksi baris 7', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(141, 4, 'Payfazz', 605000, 'Transaksi baris 7', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(142, 4, 'Fastpay', 560000, 'Transaksi baris 8', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(143, 4, 'Payfazz', 103000, 'Transaksi baris 8', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(144, 4, 'Fastpay', 73000, 'Transaksi baris 9', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(145, 4, 'Payfazz', 23000, 'Transaksi baris 9', '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(146, 4, 'Fastpay', 63000, 'Transaksi baris 10', '2026-10-24 20:35:12', '2026-10-24 20:35:12');

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

--
-- Dumping data for table `brilink_reports`
--

INSERT INTO `brilink_reports` (`id`, `store_id`, `import_id`, `file_name`, `file_hash`, `periode_start`, `periode_end`, `total_omset_ppob`, `total_tarik_tunai_edc`, `total_tarik_tunai_mbanking`, `total_setoran_brilink`, `total_pendapatan_admin`, `validasi_kas_uang`, `manual_validasi_kas_uang`, `manual_kas_akhir`, `manual_kas_awal`, `manual_validasi_mutasi_rek`, `manual_validasi_mutasi_fisik`, `laba_kasir_pintar`, `penambahan_brangkas`, `total_pengeluaran_riil`, `total_pindah_saldo`, `volume_transaksi_agen`, `saldo_awal_total`, `saldo_akhir_total`, `status`, `error_message`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '221026HM_NEW_BUKU_BRILINK_PRO_V2.xlsx', 'af387d60385c7ec1295d0c271de4ddf18ef106e55901ba588e224085040a1509', '2026-10-22', '2026-10-22', 455565460, 164361000, 240263000, 95016545, 1341610, 0, -60130100, NULL, NULL, 0, 0, 396953, 200000000, 0, 0, 152, 10133234, 0, 'imported', NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(2, 2, 2, 'GN221026_NEW_BUKU_BRILINK_PRO_V2.xlsx', 'e7ad285001eee4f8d381138ea0abdec839975d56e3c5c208321dc545c796c577', '2026-10-22', '2026-10-22', 78829000, 50757000, 36194800, 11511000, 215500, 0, -6275800, NULL, NULL, 0, 0, 3317, 0, 0, 0, 48, 0, 0, 'imported', NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(3, 4, 3, '221026CRH_NEW_BUKU_BRILINK_PRO_V2.xlsx', 'ce5e1d238e0c3bef654cd24338075abf16f0e5c6b6db8c7aaefb6c315373df9c', '2026-10-22', '2026-10-22', 4986000, 6828000, 9467000, 1505000, 90500, 0, -9739000, NULL, NULL, 0, 0, 0, 0, 0, 0, 16, 0, 0, 'imported', NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(4, 3, 4, '221026AT_NEW_BUKU_BRILINK_PRO_V2.xlsx', 'bb05815ff04c815269b91d077d215f378bd60c8928005d572bc8e57a2190281a', '2026-10-22', '2026-10-22', 96441000, 39189000, 59593000, 12112500, 549060, 0, -26998500, NULL, NULL, 0, 0, 7167, 0, 0, 0, 53, 3475762, 0, 'imported', NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12');

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

--
-- Dumping data for table `brilink_setoran`
--

INSERT INTO `brilink_setoran` (`id`, `brilink_report_id`, `nominal_edc`, `administrasi_toko`, `keterangan`, `jumlah_setoran_riil`, `created_at`, `updated_at`) VALUES
(1, 1, 10001500, 0, 'EDC AA', 10001500, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(2, 1, 1501500, 2500, NULL, 1501500, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(3, 1, 6805000, 3000, NULL, 6805000, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(4, 1, 9155000, 4000, NULL, 9155000, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(5, 1, 10001500, 0, NULL, 10001500, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(6, 1, 13005000, 10000, NULL, 13005000, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(7, 1, 5001500, 5500, NULL, 5001500, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(8, 1, 628500, 1500, NULL, 628500, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(9, 1, 1205000, 0, NULL, 1205000, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(10, 1, 194529, 1500, NULL, 194529, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(11, 1, 1727500, 1500, NULL, 1727500, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(12, 1, 683239, 1500, NULL, 683239, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(13, 1, 372931, 1500, NULL, 372931, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(14, 1, 0, 3000, NULL, 0, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(15, 1, 5001500, 0, 'EDC TETEH', 5001500, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(16, 1, 1914096, 2500, NULL, 1914096, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(17, 1, 4105000, 2000, NULL, 4105000, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(18, 1, 10005000, 15000, NULL, 10005000, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(19, 1, 4950750, 2000, NULL, 4950750, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(20, 1, 5252500, 4500, NULL, 5252500, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(21, 1, 3505000, 2000, NULL, 3505000, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(22, 2, 10001500, 0, NULL, 10001500, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(23, 2, 1509500, 1500, NULL, 1509500, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(24, 3, 1505000, 0, NULL, 1505000, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(25, 4, 1751500, 2500, NULL, 1751500, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(26, 4, 2066500, 3500, NULL, 2066500, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(27, 4, 4001500, 0, NULL, 4001500, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(28, 4, 3001500, 0, NULL, 3001500, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(29, 4, 1291500, 2500, NULL, 1291500, '2026-10-24 20:35:12', '2026-10-24 20:35:12');

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

--
-- Dumping data for table `brilink_tarik_tunai`
--

INSERT INTO `brilink_tarik_tunai` (`id`, `brilink_report_id`, `tipe`, `nominal`, `admin_toko`, `ket_mesin_edc`, `kmc`, `saldo_masuk`, `keterangan_rek`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 1, 'edc_bri', 95000, 3000, 'EDC AA', 55140, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(2, 1, 'mbanking', 500000, 5000, NULL, 0, 0, 'BCA', NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(3, 1, 'edc_bri', 1004000, 4000, NULL, 146380, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(4, 1, 'mbanking', 1500000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(5, 1, 'edc_bri', 500000, 3000, NULL, 127890, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(6, 1, 'mbanking', 1509000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(7, 1, 'edc_bri', 10010000, 10000, NULL, 104200, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(8, 1, 'mbanking', 250000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(9, 1, 'edc_bri', 113000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(10, 1, 'mbanking', 250000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(11, 1, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(12, 1, 'mbanking', 1190000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(13, 1, 'edc_bri', 203000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(14, 1, 'mbanking', 3100000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(15, 1, 'edc_bri', 163000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(16, 1, 'mbanking', 1100000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(17, 1, 'edc_bri', 4006000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(18, 1, 'mbanking', 180000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(19, 1, 'edc_bri', 800000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(20, 1, 'mbanking', 3000000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(21, 1, 'edc_bri', 195000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(22, 1, 'mbanking', 100000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(23, 1, 'edc_bri', 1264000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(24, 1, 'mbanking', 3000000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(25, 1, 'edc_bri', 4006000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(26, 1, 'mbanking', 420000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(27, 1, 'edc_bri', 1024000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(28, 1, 'mbanking', 1200000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(29, 1, 'edc_bri', 503000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(30, 1, 'mbanking', 5500000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(31, 1, 'edc_bri', 5012000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(32, 1, 'mbanking', 500000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(33, 1, 'edc_bri', 10010000, 10000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(34, 1, 'mbanking', 600000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(35, 1, 'edc_bri', 3000000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(36, 1, 'mbanking', 700000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(37, 1, 'edc_bri', 160000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(38, 1, 'mbanking', 1300000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(39, 1, 'edc_bri', 253000, 0, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(40, 1, 'mbanking', 4600000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(41, 1, 'mbanking', 210000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(42, 1, 'edc_bri', 0, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(43, 1, 'mbanking', 100000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(44, 1, 'edc_bri', 790000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(45, 1, 'mbanking', 205000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(46, 1, 'edc_bri', 4689000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(47, 1, 'mbanking', 400000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(48, 1, 'edc_bri', 503000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(49, 1, 'mbanking', 350000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(50, 1, 'edc_bri', 10010000, 10000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(51, 1, 'mbanking', 905000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(52, 1, 'edc_bri', 503000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(53, 1, 'mbanking', 250000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(54, 1, 'edc_bri', 100000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(55, 1, 'mbanking', 300000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(56, 1, 'edc_bri', 4829000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(57, 1, 'mbanking', 200000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(58, 1, 'edc_bri', 2550000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(59, 1, 'mbanking', 11713000, 13000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(60, 1, 'edc_bri', 20000000, 22000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(61, 1, 'mbanking', 4600000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(62, 1, 'edc_bri', 2505000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(63, 1, 'mbanking', 12590000, 14000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(64, 1, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(65, 1, 'mbanking', 8940000, 8000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(66, 1, 'edc_bri', 3863000, 6000, NULL, 0, 0, NULL, 'plastik packing', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(67, 1, 'mbanking', 1535000, 0, NULL, 0, 0, NULL, 'plastik packing', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(68, 1, 'edc_bri', 503000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(69, 1, 'mbanking', 200000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(70, 1, 'edc_bri', 1154000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(71, 1, 'mbanking', 305000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(72, 1, 'edc_bri', 2005000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(73, 1, 'mbanking', 600000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(74, 1, 'mbanking', 500000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(75, 1, 'mbanking', 200000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(76, 1, 'mbanking', 600000, 5000, NULL, 0, 0, 'BRI', NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(77, 1, 'mbanking', 160000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(78, 1, 'mbanking', 15000000, 17000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(79, 1, 'mbanking', 550000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(80, 1, 'mbanking', 2000000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(81, 1, 'mbanking', 135000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(82, 1, 'mbanking', 245000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(83, 1, 'mbanking', 500000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(84, 1, 'mbanking', 510000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(85, 1, 'mbanking', 2000000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(86, 1, 'mbanking', 7750000, 8000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(87, 1, 'mbanking', 900000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(88, 1, 'mbanking', 10000000, 10000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(89, 1, 'mbanking', 3006000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(90, 1, 'mbanking', 12000000, 14000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(91, 1, 'mbanking', 6500000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(92, 1, 'mbanking', 355000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(93, 1, 'mbanking', 10020000, 0, NULL, 0, 0, NULL, 'th rika', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(94, 1, 'mbanking', 72000000, 74000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(95, 1, 'mbanking', 65000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(96, 1, 'mbanking', 450000, 5000, NULL, 0, 0, 'BRI', 'rek 5539', '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(97, 1, 'mbanking', 2500000, 5000, NULL, 0, 0, 'MANDIRI', NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(98, 1, 'mbanking', 1050000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(99, 1, 'mbanking', 300000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(100, 1, 'edc_bri', 2005000, 5000, 'EDC TETEH', 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(101, 1, 'mbanking', 5050000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(102, 1, 'edc_bri', 10500000, 10000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(103, 1, 'mbanking', 1000000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(104, 1, 'edc_bri', 2005000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(105, 1, 'mbanking', 8280000, 8000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(106, 1, 'edc_bri', 250000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(107, 1, 'edc_bri', 16018000, 18000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(108, 1, 'edc_bri', 503000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(109, 1, 'edc_bri', 9000000, 9000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(110, 1, 'edc_bri', 7008000, 8000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(111, 1, 'edc_bri', 1700000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(112, 1, 'mbanking', 1590000, 4000, NULL, 0, 0, 'BNI', NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(113, 1, 'edc_bri', 303000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(114, 1, 'edc_bri', 500000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(115, 1, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(116, 1, 'mbanking', 150000, 5000, NULL, 0, 0, 'QRIS', NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(117, 1, 'edc_bri', 50000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(118, 1, 'mbanking', 195000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(119, 1, 'edc_bri', 900000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(120, 1, 'mbanking', 155000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(121, 1, 'edc_bri', 603000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(122, 1, 'mbanking', 100000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(123, 1, 'edc_bri', 703000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(124, 1, 'mbanking', 175000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(125, 1, 'edc_bri', 300000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(126, 1, 'mbanking', 65000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(127, 1, 'edc_bri', 5000000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(128, 1, 'mbanking', 305000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(129, 1, 'edc_bri', 2147000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(130, 1, 'edc_bri', 3556000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(131, 1, 'edc_bri', 1104000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(132, 1, 'edc_bri', 269000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(133, 1, 'edc_bri', 600000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(134, 2, 'edc_bri', 993000, 3000, 'EDC AA', 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(135, 2, 'mbanking', 1066800, 5000, NULL, 0, 0, 'BRI', NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(136, 2, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(137, 2, 'mbanking', 700000, 0, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(138, 2, 'edc_bri', 803000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(139, 2, 'mbanking', 400000, 0, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(140, 2, 'edc_bri', 503000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(141, 2, 'mbanking', 5100000, 0, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(142, 2, 'edc_bri', 600000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(143, 2, 'mbanking', 105000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(144, 2, 'edc_bri', 1615000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(145, 2, 'mbanking', 2900000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(146, 2, 'edc_bri', 270000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(147, 2, 'mbanking', 855000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(148, 2, 'edc_bri', 3805000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(149, 2, 'mbanking', 200000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(150, 2, 'edc_bri', 600000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(151, 2, 'mbanking', 400000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(152, 2, 'edc_bri', 3355000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(153, 2, 'mbanking', 300000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(154, 2, 'edc_bri', 303000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(155, 2, 'mbanking', 550000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(156, 2, 'edc_bri', 3355000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(157, 2, 'mbanking', 200000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(158, 2, 'edc_bri', 1839000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(159, 2, 'mbanking', 855000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(160, 2, 'edc_bri', 2005000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(161, 2, 'mbanking', 1840000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(162, 2, 'edc_bri', 1104000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(163, 2, 'edc_bri', 17024000, 19000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(164, 2, 'edc_bri', 6579000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(165, 2, 'edc_bri', 800000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(166, 2, 'edc_bri', 600000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(167, 2, 'edc_bri', 1200000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(168, 2, 'edc_bri', 1800000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(169, 2, 'edc_bri', 600000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(170, 2, 'mbanking', 405000, 5000, NULL, 0, 0, 'MANDIRI', NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(171, 2, 'mbanking', 400000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(172, 2, 'mbanking', 950000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(173, 2, 'mbanking', 10000000, 10000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(174, 2, 'mbanking', 4510000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(175, 2, 'mbanking', 300000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(176, 2, 'mbanking', 2884000, 5000, NULL, 0, 0, 'BCA', NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(177, 2, 'mbanking', 800000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(178, 2, 'mbanking', 25000, 5000, NULL, 0, 0, 'BNI', 'BNI TTEH', '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(179, 2, 'mbanking', 449000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(180, 3, 'edc_bri', 600000, 3000, NULL, 10000, 0, NULL, NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(181, 3, 'mbanking', 595000, 5000, NULL, 0, 0, 'BRI', NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(182, 3, 'edc_bri', 1600000, 4000, NULL, 12500, 0, NULL, NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(183, 3, 'mbanking', 300000, 5000, NULL, 0, 0, 'BRI', NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(184, 3, 'edc_bri', 500000, 3000, NULL, 3000, 0, NULL, NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(185, 3, 'edc_bri', 2789000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(186, 3, 'edc_bri', 99000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(187, 3, 'edc_bri', 799000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(188, 3, 'edc_bri', 103000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(189, 3, 'edc_bri', 338000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(190, 3, 'mbanking', 62000, 5000, NULL, 0, 0, 'BCA', NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(191, 3, 'mbanking', 305000, 5000, NULL, 0, 0, 'BCA', NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(192, 3, 'mbanking', 100000, 5000, NULL, 0, 0, 'BCA', NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(193, 3, 'mbanking', 105000, 5000, NULL, 0, 0, 'BCA', NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(194, 3, 'mbanking', 8000000, 8000, NULL, 0, 0, 'BNI', 'BNI AA', '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(195, 4, 'edc_bri', 996000, 3000, NULL, 70890, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(196, 4, 'mbanking', 520000, 5000, NULL, 0, 0, 'BRI', NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(197, 4, 'edc_bri', 3906000, 6000, NULL, 40640, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(198, 4, 'mbanking', 5000000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(199, 4, 'edc_bri', 1000000, 4000, NULL, 45640, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(200, 4, 'mbanking', 205000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(201, 4, 'edc_bri', 153000, 3000, NULL, 65390, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(202, 4, 'mbanking', 1305000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(203, 4, 'edc_bri', 2200000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(204, 4, 'mbanking', 5000000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(205, 4, 'edc_bri', 800000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(206, 4, 'mbanking', 5007000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(207, 4, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(208, 4, 'mbanking', 1505000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(209, 4, 'edc_bri', 249000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(210, 4, 'mbanking', 600000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(211, 4, 'edc_bri', 6500000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(212, 4, 'mbanking', 600000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(213, 4, 'edc_bri', 1844000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(214, 4, 'edc_bri', 7600000, 8000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(215, 4, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(216, 4, 'edc_bri', 3006000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(217, 4, 'mbanking', 8000000, 8000, NULL, 0, 0, 'MANDIRI', NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(218, 4, 'edc_bri', 200000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(219, 4, 'mbanking', 2500000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(220, 4, 'edc_bri', 2900000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(221, 4, 'mbanking', 400000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(222, 4, 'edc_bri', 2650000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(223, 4, 'edc_bri', 211000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(224, 4, 'edc_bri', 300000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(225, 4, 'edc_bri', 273000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(226, 4, 'mbanking', 20022000, 22000, NULL, 0, 0, 'BCA', NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(227, 4, 'edc_bri', 2200000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(228, 4, 'mbanking', 6000000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(229, 4, 'edc_bri', 193000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(230, 4, 'mbanking', 55000, 5000, NULL, 0, 0, 'QRIS', NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(231, 4, 'mbanking', 405000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(232, 4, 'mbanking', 44000, 0, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(233, 4, 'mbanking', 355000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(234, 4, 'mbanking', 100000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(235, 4, 'mbanking', 250000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(236, 4, 'mbanking', 305000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(237, 4, 'mbanking', 105000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(238, 4, 'mbanking', 300000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(239, 4, 'mbanking', 105000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(240, 4, 'mbanking', 400000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(241, 4, 'mbanking', 75000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12'),
(242, 4, 'mbanking', 430000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12');

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

--
-- Dumping data for table `gaji_karyawan`
--

INSERT INTO `gaji_karyawan` (`id`, `karyawan_id`, `periode`, `gaji_pokok`, `bonus`, `potongan`, `komisi`, `total_gaji`, `status`, `tanggal_bayar`, `metode_bayar`, `created_at`, `updated_at`) VALUES
(12, 1, '2026-10', 1200000, 0, 0, 22800, 1222800, 'draft', NULL, 'tunai', '2026-10-24 22:02:00', '2026-10-24 22:02:00'),
(13, 2, '2026-10', 1200000, 0, 0, 22800, 1222800, 'draft', NULL, 'tunai', '2026-10-24 22:02:00', '2026-10-24 22:02:00'),
(14, 3, '2026-10', 1200000, 0, 0, 14400, 1214400, 'draft', NULL, 'tunai', '2026-10-24 22:02:00', '2026-10-24 22:05:16');

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

--
-- Dumping data for table `hutangs`
--

INSERT INTO `hutangs` (`id`, `tanggal`, `nama_pemberi_hutang`, `keterangan`, `nominal`, `is_lunas`, `tanggal_lunas`, `created_at`, `updated_at`) VALUES
(1, '2026-10-24', 'Setoran BRI', NULL, 10000000, 0, NULL, '2026-10-24 20:36:01', '2026-10-24 20:36:01');

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

--
-- Dumping data for table `imports`
--

INSERT INTO `imports` (`id`, `store_id`, `tipe`, `file_name`, `file_hash`, `periode_start`, `periode_end`, `total_rows`, `status`, `error_message`, `created_at`, `updated_at`) VALUES
(1, 1, 'brilink', '221026HM_NEW_BUKU_BRILINK_PRO_V2.xlsx', 'af387d60385c7ec1295d0c271de4ddf18ef106e55901ba588e224085040a1509', '2026-10-22', '2026-10-22', 280, 'imported', NULL, '2026-10-24 20:34:37', '2026-10-24 20:34:37'),
(2, 2, 'brilink', 'GN221026_NEW_BUKU_BRILINK_PRO_V2.xlsx', 'e7ad285001eee4f8d381138ea0abdec839975d56e3c5c208321dc545c796c577', '2026-10-22', '2026-10-22', 98, 'imported', NULL, '2026-10-24 20:34:51', '2026-10-24 20:34:51'),
(3, 4, 'brilink', '221026CRH_NEW_BUKU_BRILINK_PRO_V2.xlsx', 'ce5e1d238e0c3bef654cd24338075abf16f0e5c6b6db8c7aaefb6c315373df9c', '2026-10-22', '2026-10-22', 44, 'imported', NULL, '2026-10-24 20:35:02', '2026-10-24 20:35:02'),
(4, 3, 'brilink', '221026AT_NEW_BUKU_BRILINK_PRO_V2.xlsx', 'bb05815ff04c815269b91d077d215f378bd60c8928005d572bc8e57a2190281a', '2026-10-22', '2026-10-22', 99, 'imported', NULL, '2026-10-24 20:35:12', '2026-10-24 20:35:12');

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

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id`, `store_id`, `nama`, `gaji_pokok`, `is_active`, `tanggal_masuk`, `no_hp`, `created_at`, `updated_at`) VALUES
(1, 1, 'ESA', 1200000, 1, '2025-04-01', NULL, '2026-10-24 21:38:29', '2026-10-24 21:38:29'),
(2, 1, 'RAFLI', 1200000, 1, '2026-10-01', NULL, '2026-10-24 21:48:51', '2026-10-24 21:48:51'),
(3, 2, 'ARUM', 1200000, 1, '2026-10-01', NULL, '2026-10-24 21:49:45', '2026-10-24 21:49:45');

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
(49, '2026_10_25_044323_add_komisi_to_gaji_karyawan_table', 2),
(50, '2026_10_25_045605_create_store_komisi_settings_table', 3);

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
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sku_internal` varchar(255) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `hpp` int(11) NOT NULL DEFAULT 0,
  `harga_jual_default` int(11) DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_mappings`
--

CREATE TABLE `product_mappings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `marketplace_sku` varchar(255) NOT NULL,
  `marketplace_product_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
('4iO6UPZlgwpWm7h4s4TWONVyY1olJiFYSO6lVBao', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoicGFRNWduN2NSRkRSeUFuNTlZN2hOblkyY2dxMnA5dzhON1ltT3RtdiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYnJpbGluaz9zdG9yZT0zIjtzOjU6InJvdXRlIjtzOjEzOiJicmlsaW5rLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMDU5OWFkZDE2NzRhMzhlNjBhY2IwNGRlNTU1NDJhOThhNDViMGIwNWY3MDg5MWIxMzkyZTU1Y2I0OTJiZTU3YiI7fQ==', 1792881676);

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
(1, 'brilink', 'Brilink HM Pusat', NULL, 1, '2026-10-24 20:33:49', '2026-10-24 20:33:49'),
(2, 'brilink', 'Brilink Gunajaya', NULL, 1, '2026-10-24 20:33:55', '2026-10-24 20:33:55'),
(3, 'brilink', 'Brilink Atung', NULL, 1, '2026-10-24 20:34:00', '2026-10-24 20:34:00'),
(4, 'brilink', 'Brilink Cieurih', NULL, 1, '2026-10-24 20:34:05', '2026-10-24 20:34:05'),
(5, 'shopee', 'HM Barokah', NULL, 1, '2026-10-24 21:09:35', '2026-10-24 21:09:35'),
(6, 'tiktok', 'Barokah Sport', NULL, 1, '2026-10-24 21:09:40', '2026-10-24 21:09:40');

-- --------------------------------------------------------

--
-- Table structure for table `store_komisi_settings`
--

CREATE TABLE `store_komisi_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `komisi_per_transaksi` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `store_komisi_settings`
--

INSERT INTO `store_komisi_settings` (`id`, `store_id`, `komisi_per_transaksi`, `created_at`, `updated_at`) VALUES
(1, 6, 300, '2026-10-24 21:59:42', '2026-10-24 22:00:51'),
(2, 3, 300, '2026-10-24 21:59:42', '2026-10-24 22:00:51'),
(3, 4, 300, '2026-10-24 21:59:42', '2026-10-24 22:00:51'),
(4, 2, 300, '2026-10-24 21:59:42', '2026-10-24 22:00:51'),
(5, 1, 300, '2026-10-24 21:59:42', '2026-10-24 22:00:51'),
(6, 5, 300, '2026-10-24 21:59:42', '2026-10-24 22:00:51');

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

--
-- Dumping data for table `tabungan_accounts`
--

INSERT INTO `tabungan_accounts` (`id`, `nama`, `keterangan`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Rafli', NULL, 1, '2026-10-24 20:35:39', '2026-10-24 20:35:39');

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

--
-- Dumping data for table `tabungan_transactions`
--

INSERT INTO `tabungan_transactions` (`id`, `tabungan_account_id`, `tipe`, `nominal`, `tanggal`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 1, 'setoran', 100000, '2026-10-24', NULL, '2026-10-24 20:35:48', '2026-10-24 20:35:48');

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

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Asep Mubarok', 'admin@barokahsport.com', NULL, '$2y$12$I8WTLD4/9yLQhjnfYS7QWuA5VdITakHvYdRLgU2BHqoqJ1bxlOg7a', NULL, '2026-10-24 20:33:22', '2026-10-24 20:33:22');

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

--
-- Dumping data for table `zakat_barang_mandiri`
--

INSERT INTO `zakat_barang_mandiri` (`id`, `nama_barang`, `qty`, `harga_satuan`, `created_at`, `updated_at`) VALUES
(1, 'ONESET', 10, 100000, '2026-10-24 20:42:19', '2026-10-24 20:42:19');

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

--
-- Dumping data for table `zakat_pembayarans`
--

INSERT INTO `zakat_pembayarans` (`id`, `tanggal`, `total_zakat`, `total_saldo`, `total_piutang`, `total_barang`, `total_hutang`, `total_tabungan`, `keterangan`, `detail_breakdown`, `created_at`, `updated_at`) VALUES
(2, '2026-10-25', 16323368, 654744724, 7290000, 1000000, 10000000, 100000, NULL, '{\"saldo\":[{\"nama_rekening\":\"KAS\",\"keterangan\":\"KAS\",\"saldo\":136584200,\"zakat\":3414605},{\"nama_rekening\":\"LINK AJA\",\"keterangan\":\"PULSA\",\"saldo\":2839213,\"zakat\":70980},{\"nama_rekening\":\"MANG SALAREA\",\"keterangan\":\"PULSA\",\"saldo\":936358,\"zakat\":23409},{\"nama_rekening\":\"BUKU WARUNG\",\"keterangan\":\"PPOB\",\"saldo\":1388244,\"zakat\":34706},{\"nama_rekening\":\"FASTPAY\",\"keterangan\":\"PPOB\",\"saldo\":12926967,\"zakat\":323174},{\"nama_rekening\":\"MITRALINK\",\"keterangan\":\"PPOB\",\"saldo\":10612,\"zakat\":265},{\"nama_rekening\":\"PAYFAZZ\",\"keterangan\":\"PPOB\",\"saldo\":14569910,\"zakat\":364248},{\"nama_rekening\":\"SALDO BRI 119 PENAMPUNGAN\",\"keterangan\":\"BRILINK\",\"saldo\":104111817,\"zakat\":2602795},{\"nama_rekening\":\"SALDO BRI 1417 PENARIKAN\",\"keterangan\":\"BRILINK\",\"saldo\":30577729,\"zakat\":764443},{\"nama_rekening\":\"SALDO BRI 4468 PENAMPUNGAN\",\"keterangan\":\"BRILINK\",\"saldo\":8047687,\"zakat\":201192},{\"nama_rekening\":\"SALDO BRI 530 PENAMPUNGAN\",\"keterangan\":\"BRILINK\",\"saldo\":294508,\"zakat\":7363},{\"nama_rekening\":\"SALDO BRI 5539 PENAMPUNG\",\"keterangan\":\"BRILINK\",\"saldo\":0,\"zakat\":0},{\"nama_rekening\":\"SALDO BRI 5539 PENAMPUNGAN\",\"keterangan\":\"BRILINK\",\"saldo\":8845474,\"zakat\":221137},{\"nama_rekening\":\"SALDO EDC 6535\",\"keterangan\":\"BRILINK\",\"saldo\":0,\"zakat\":0},{\"nama_rekening\":\"SALDO EDC AA\",\"keterangan\":\"BRILINK\",\"saldo\":64070252,\"zakat\":1601756},{\"nama_rekening\":\"SALDO EDC CRH\",\"keterangan\":\"BRILINK\",\"saldo\":8021308,\"zakat\":200533},{\"nama_rekening\":\"SALDO EDC GN 6833\",\"keterangan\":\"BRILINK\",\"saldo\":0,\"zakat\":0},{\"nama_rekening\":\"BCA\",\"keterangan\":\"MBANKING\",\"saldo\":75299563,\"zakat\":1882489},{\"nama_rekening\":\"BNI\",\"keterangan\":\"MBANKING\",\"saldo\":23619496,\"zakat\":590487},{\"nama_rekening\":\"BNI AA\",\"keterangan\":\"MBANKING\",\"saldo\":0,\"zakat\":0},{\"nama_rekening\":\"BSI\",\"keterangan\":\"MBANKING\",\"saldo\":600258,\"zakat\":15006},{\"nama_rekening\":\"MANDIRI\",\"keterangan\":\"MBANKING\",\"saldo\":20900680,\"zakat\":522517},{\"nama_rekening\":\"BRANGKAS\",\"keterangan\":\"BRANGKAS\",\"saldo\":0,\"zakat\":0},{\"nama_rekening\":\"SALDO BARANG\",\"keterangan\":\"POS\",\"saldo\":93975560,\"zakat\":2349389},{\"nama_rekening\":\"SALDO EDC TETEH\",\"keterangan\":\"-\",\"saldo\":37124888,\"zakat\":928122}],\"saldo_online\":[{\"id\":1,\"marketplace\":\"Shopee\",\"saldo\":10000000,\"created_at\":\"2026-10-24T20:42:00.000000Z\",\"updated_at\":\"2026-10-24T20:42:00.000000Z\"}],\"piutang\":[{\"id\":4,\"keterangan\":\"tf m iyus\",\"kategori\":\"PIUTANG IYUS\",\"nominal\":6045000,\"terbayar\":0,\"sisa\":6045000,\"zakat\":151125,\"is_paid\":false},{\"id\":24,\"keterangan\":\"buhaji\",\"kategori\":\"PIUTANG BU HAJI\",\"nominal\":315000,\"terbayar\":0,\"sisa\":315000,\"zakat\":7875,\"is_paid\":false},{\"id\":13,\"keterangan\":\"bu haji\",\"kategori\":\"PIUTANG BU HAJI\",\"nominal\":300000,\"terbayar\":0,\"sisa\":300000,\"zakat\":7500,\"is_paid\":false},{\"id\":25,\"keterangan\":\"m iyus\",\"kategori\":\"PIUTANG IYUS\",\"nominal\":230000,\"terbayar\":0,\"sisa\":230000,\"zakat\":5750,\"is_paid\":false},{\"id\":11,\"keterangan\":\"bu haji\",\"kategori\":\"PIUTANG BU HAJI\",\"nominal\":200000,\"terbayar\":0,\"sisa\":200000,\"zakat\":5000,\"is_paid\":false},{\"id\":8,\"keterangan\":\"bu haji\",\"kategori\":\"PIUTANG BU HAJI\",\"nominal\":100000,\"terbayar\":0,\"sisa\":100000,\"zakat\":2500,\"is_paid\":false},{\"id\":14,\"keterangan\":\"m iyus\",\"kategori\":\"PIUTANG IYUS\",\"nominal\":50000,\"terbayar\":0,\"sisa\":50000,\"zakat\":1250,\"is_paid\":false},{\"id\":21,\"keterangan\":\"m iyus\",\"kategori\":\"PIUTANG IYUS\",\"nominal\":50000,\"terbayar\":0,\"sisa\":50000,\"zakat\":1250,\"is_paid\":false}],\"barang\":[{\"id\":1,\"nama_barang\":\"ONESET\",\"qty\":10,\"harga_satuan\":100000,\"total\":1000000,\"zakat\":25000}],\"tabungan\":[{\"id\":1,\"nama\":\"Rafli\",\"saldo\":100000}],\"hutang\":[{\"id\":1,\"tanggal\":\"2026-10-23T17:00:00.000000Z\",\"nama_pemberi_hutang\":\"Setoran BRI\",\"keterangan\":null,\"nominal\":10000000,\"is_lunas\":false,\"tanggal_lunas\":null,\"created_at\":\"2026-10-24T20:36:01.000000Z\",\"updated_at\":\"2026-10-24T20:36:01.000000Z\"}],\"zakat_saldo\":16368618,\"zakat_saldo_online\":250000,\"zakat_piutang\":182250,\"zakat_barang\":25000,\"zakat_hutang\":250000,\"zakat_tabungan\":2500,\"total_saldo\":654744724,\"total_piutang\":7290000,\"total_barang\":1000000,\"total_hutang\":10000000,\"total_tabungan\":100000,\"total_harta\":663034724,\"harta_bersih\":652934724,\"total_zakat\":16323368}', '2026-10-24 20:42:38', '2026-10-24 20:42:38'),
(3, '2025-10-25', 16323368, 654744724, 7290000, 1000000, 10000000, 100000, NULL, '{\"saldo\":[{\"nama_rekening\":\"KAS\",\"keterangan\":\"KAS\",\"saldo\":136584200,\"zakat\":3414605},{\"nama_rekening\":\"LINK AJA\",\"keterangan\":\"PULSA\",\"saldo\":2839213,\"zakat\":70980},{\"nama_rekening\":\"MANG SALAREA\",\"keterangan\":\"PULSA\",\"saldo\":936358,\"zakat\":23409},{\"nama_rekening\":\"BUKU WARUNG\",\"keterangan\":\"PPOB\",\"saldo\":1388244,\"zakat\":34706},{\"nama_rekening\":\"FASTPAY\",\"keterangan\":\"PPOB\",\"saldo\":12926967,\"zakat\":323174},{\"nama_rekening\":\"MITRALINK\",\"keterangan\":\"PPOB\",\"saldo\":10612,\"zakat\":265},{\"nama_rekening\":\"PAYFAZZ\",\"keterangan\":\"PPOB\",\"saldo\":14569910,\"zakat\":364248},{\"nama_rekening\":\"SALDO BRI 119 PENAMPUNGAN\",\"keterangan\":\"BRILINK\",\"saldo\":104111817,\"zakat\":2602795},{\"nama_rekening\":\"SALDO BRI 1417 PENARIKAN\",\"keterangan\":\"BRILINK\",\"saldo\":30577729,\"zakat\":764443},{\"nama_rekening\":\"SALDO BRI 4468 PENAMPUNGAN\",\"keterangan\":\"BRILINK\",\"saldo\":8047687,\"zakat\":201192},{\"nama_rekening\":\"SALDO BRI 530 PENAMPUNGAN\",\"keterangan\":\"BRILINK\",\"saldo\":294508,\"zakat\":7363},{\"nama_rekening\":\"SALDO BRI 5539 PENAMPUNG\",\"keterangan\":\"BRILINK\",\"saldo\":0,\"zakat\":0},{\"nama_rekening\":\"SALDO BRI 5539 PENAMPUNGAN\",\"keterangan\":\"BRILINK\",\"saldo\":8845474,\"zakat\":221137},{\"nama_rekening\":\"SALDO EDC 6535\",\"keterangan\":\"BRILINK\",\"saldo\":0,\"zakat\":0},{\"nama_rekening\":\"SALDO EDC AA\",\"keterangan\":\"BRILINK\",\"saldo\":64070252,\"zakat\":1601756},{\"nama_rekening\":\"SALDO EDC CRH\",\"keterangan\":\"BRILINK\",\"saldo\":8021308,\"zakat\":200533},{\"nama_rekening\":\"SALDO EDC GN 6833\",\"keterangan\":\"BRILINK\",\"saldo\":0,\"zakat\":0},{\"nama_rekening\":\"BCA\",\"keterangan\":\"MBANKING\",\"saldo\":75299563,\"zakat\":1882489},{\"nama_rekening\":\"BNI\",\"keterangan\":\"MBANKING\",\"saldo\":23619496,\"zakat\":590487},{\"nama_rekening\":\"BNI AA\",\"keterangan\":\"MBANKING\",\"saldo\":0,\"zakat\":0},{\"nama_rekening\":\"BSI\",\"keterangan\":\"MBANKING\",\"saldo\":600258,\"zakat\":15006},{\"nama_rekening\":\"MANDIRI\",\"keterangan\":\"MBANKING\",\"saldo\":20900680,\"zakat\":522517},{\"nama_rekening\":\"BRANGKAS\",\"keterangan\":\"BRANGKAS\",\"saldo\":0,\"zakat\":0},{\"nama_rekening\":\"SALDO BARANG\",\"keterangan\":\"POS\",\"saldo\":93975560,\"zakat\":2349389},{\"nama_rekening\":\"SALDO EDC TETEH\",\"keterangan\":\"-\",\"saldo\":37124888,\"zakat\":928122}],\"saldo_online\":[{\"id\":1,\"marketplace\":\"Shopee\",\"saldo\":10000000,\"created_at\":\"2026-10-24T20:42:00.000000Z\",\"updated_at\":\"2026-10-24T20:42:00.000000Z\"}],\"piutang\":[{\"id\":4,\"keterangan\":\"tf m iyus\",\"kategori\":\"PIUTANG IYUS\",\"nominal\":6045000,\"terbayar\":0,\"sisa\":6045000,\"zakat\":151125,\"is_paid\":false},{\"id\":24,\"keterangan\":\"buhaji\",\"kategori\":\"PIUTANG BU HAJI\",\"nominal\":315000,\"terbayar\":0,\"sisa\":315000,\"zakat\":7875,\"is_paid\":false},{\"id\":13,\"keterangan\":\"bu haji\",\"kategori\":\"PIUTANG BU HAJI\",\"nominal\":300000,\"terbayar\":0,\"sisa\":300000,\"zakat\":7500,\"is_paid\":false},{\"id\":25,\"keterangan\":\"m iyus\",\"kategori\":\"PIUTANG IYUS\",\"nominal\":230000,\"terbayar\":0,\"sisa\":230000,\"zakat\":5750,\"is_paid\":false},{\"id\":11,\"keterangan\":\"bu haji\",\"kategori\":\"PIUTANG BU HAJI\",\"nominal\":200000,\"terbayar\":0,\"sisa\":200000,\"zakat\":5000,\"is_paid\":false},{\"id\":8,\"keterangan\":\"bu haji\",\"kategori\":\"PIUTANG BU HAJI\",\"nominal\":100000,\"terbayar\":0,\"sisa\":100000,\"zakat\":2500,\"is_paid\":false},{\"id\":14,\"keterangan\":\"m iyus\",\"kategori\":\"PIUTANG IYUS\",\"nominal\":50000,\"terbayar\":0,\"sisa\":50000,\"zakat\":1250,\"is_paid\":false},{\"id\":21,\"keterangan\":\"m iyus\",\"kategori\":\"PIUTANG IYUS\",\"nominal\":50000,\"terbayar\":0,\"sisa\":50000,\"zakat\":1250,\"is_paid\":false}],\"barang\":[{\"id\":1,\"nama_barang\":\"ONESET\",\"qty\":10,\"harga_satuan\":100000,\"total\":1000000,\"zakat\":25000}],\"tabungan\":[{\"id\":1,\"nama\":\"Rafli\",\"saldo\":100000}],\"hutang\":[{\"id\":1,\"tanggal\":\"2026-10-23T17:00:00.000000Z\",\"nama_pemberi_hutang\":\"Setoran BRI\",\"keterangan\":null,\"nominal\":10000000,\"is_lunas\":false,\"tanggal_lunas\":null,\"created_at\":\"2026-10-24T20:36:01.000000Z\",\"updated_at\":\"2026-10-24T20:36:01.000000Z\"}],\"zakat_saldo\":16368618,\"zakat_saldo_online\":250000,\"zakat_piutang\":182250,\"zakat_barang\":25000,\"zakat_hutang\":250000,\"zakat_tabungan\":2500,\"total_saldo\":654744724,\"total_piutang\":7290000,\"total_barang\":1000000,\"total_hutang\":10000000,\"total_tabungan\":100000,\"total_harta\":663034724,\"harta_bersih\":652934724,\"total_zakat\":16323368}', '2026-10-24 20:46:22', '2026-10-24 20:46:22');

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
-- Dumping data for table `zakat_saldo_onlines`
--

INSERT INTO `zakat_saldo_onlines` (`id`, `marketplace`, `saldo`, `created_at`, `updated_at`) VALUES
(1, 'Shopee', 10000000, '2026-10-24 20:42:00', '2026-10-24 20:42:00');

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
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_sku_internal_unique` (`sku_internal`);

--
-- Indexes for table `product_mappings`
--
ALTER TABLE `product_mappings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_mappings_store_id_marketplace_sku_unique` (`store_id`,`marketplace_sku`),
  ADD KEY `product_mappings_product_id_foreign` (`product_id`),
  ADD KEY `product_mappings_marketplace_sku_index` (`marketplace_sku`);

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
  ADD UNIQUE KEY `users_email_unique` (`email`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `brilink_pengeluaran`
--
ALTER TABLE `brilink_pengeluaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `brilink_ppob_transactions`
--
ALTER TABLE `brilink_ppob_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `brilink_reports`
--
ALTER TABLE `brilink_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `brilink_setoran`
--
ALTER TABLE `brilink_setoran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `brilink_tarik_tunai`
--
ALTER TABLE `brilink_tarik_tunai`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gaji_karyawan`
--
ALTER TABLE `gaji_karyawan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `hutangs`
--
ALTER TABLE `hutangs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `imports`
--
ALTER TABLE `imports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `laporan_minus_karyawan`
--
ALTER TABLE `laporan_minus_karyawan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_mappings`
--
ALTER TABLE `product_mappings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shopee_income_rows`
--
ALTER TABLE `shopee_income_rows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shopee_order_items`
--
ALTER TABLE `shopee_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `store_komisi_settings`
--
ALTER TABLE `store_komisi_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tabungan_accounts`
--
ALTER TABLE `tabungan_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tabungan_transactions`
--
ALTER TABLE `tabungan_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tiktok_income`
--
ALTER TABLE `tiktok_income`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiktok_order_items`
--
ALTER TABLE `tiktok_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `zakat_kategoris`
--
ALTER TABLE `zakat_kategoris`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zakat_pembayarans`
--
ALTER TABLE `zakat_pembayarans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `zakat_saldo_onlines`
--
ALTER TABLE `zakat_saldo_onlines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Constraints for table `product_mappings`
--
ALTER TABLE `product_mappings`
  ADD CONSTRAINT `product_mappings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_mappings_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `tiktok_order_items`
--
ALTER TABLE `tiktok_order_items`
  ADD CONSTRAINT `tiktok_order_items_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
