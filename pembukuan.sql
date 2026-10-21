-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2026 at 09:52 PM
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
(18, 2, 'KAS', 'KAS', 67401500, 122384000, 54982500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(19, 2, 'MANG SALAREA', 'PULSA', 936358, 936358, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(20, 2, 'LINK AJA', 'PULSA', 2017260, 1880972, -136288, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(21, 2, 'MITRALINK', 'PPOB', 10612, 10612, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(22, 2, 'PAYFAZZ', 'PPOB', 1526858, 1408493, -118365, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(23, 2, 'BUKU WARUNG', 'PPOB', 20132, 875467, 855335, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(24, 2, 'FASTPAY', 'PPOB', 2763716, 1362516, -1401200, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(25, 2, 'SALDO EDC AA', 'BRILINK', 88161318, 19075137, -69086181, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(26, 2, 'SALDO EDC TETEH', '', 24822168, 5830734, -18991434, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(27, 2, 'SALDO BRI 119 PENAMPUNGAN', 'BRILINK', 120317, 0, -120317, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(28, 2, 'SALDO BRI 1417 PENARIKAN', 'BRILINK', 1177730, 0, -1177730, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(29, 2, 'BCA', 'MBANKING', 174762463, 70856563, -103905900, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(30, 2, 'MANDIRI', 'MBANKING', 267180, 7656180, 7389000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(31, 2, 'BNI', 'MBANKING', 10133234, 10133234, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(32, 2, 'BSI', 'MBANKING', 395258, 600258, 205000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(33, 2, 'BRANGKAS', 'BRANGKAS', 47000000, 200000000, 153000000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(34, 2, 'SALDO BARANG', 'POS', 78394275, 77973961, -420314, '2026-10-21 18:26:40', '2026-10-21 18:26:40');

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
(1, 2, 'payfazz', 10001500, 'TOP UP PPOB', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(2, 2, 'saldo at', 10005000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(3, 2, 'payfazz', 10001500, 'TOP UP PPOB', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(4, 2, 'pindah', 50005000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(5, 2, 'pindah', 20005000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(6, 2, 'pindah', 10005000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(7, 2, 'tf buhaji', 778600, 'PIUTANG BU HAJI', 0, NULL, 50000, '2026-10-21 18:26:40', '2026-10-21 18:56:58'),
(8, 2, 'thermal+plastik packing', 227000, 'OP KONVEKSI', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(9, 2, 'payfazz', 10001500, 'TOP UP PPOB', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(10, 2, 'bw', 40003250, 'TOP UP PPOB', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(11, 2, 'payfazz', 30004000, 'TOP UP PPOB', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(12, 2, 'buhaji', 100000, 'PIUTANG BU HAJI', 1, '2026-10-22', 100000, '2026-10-21 18:26:40', '2026-10-21 18:56:58'),
(13, 2, 'm solih', 484000, 'OP KONVEKSI', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(14, 2, 'm iyus', 100000, 'PIUTANG IYUS', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(15, 2, 'kepala sleting', 45000, 'OP KONVEKSI', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(16, 2, 'komisi hm', 1611900, 'PRIVE', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(17, 2, 'teh tarik', 12000, 'PRIVE', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(18, 2, 'buhaji', 100000, 'PIUTANG BU HAJI', 1, '2026-10-22', 100000, '2026-10-21 18:26:40', '2026-10-21 18:56:58'),
(19, 2, 'kas at', 31000000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(20, 2, 'kas gn', 20000000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(21, 2, 'paypazz', 15001500, 'TOP UP PPOB', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(22, 2, 'buku warung', 1001500, 'TOP UP PPOB', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(23, 2, 'benang', 39500, 'OP KONVEKSI', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(24, 2, 'm entis', 1381000, 'OP KONVEKSI', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(25, 2, 'kas at', 40000000, 'PINDAH SALDO KAS INTERNAL', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(26, 2, 'bu haji', 200000, 'PIUTANG BU HAJI', 1, '2026-10-22', 200000, '2026-10-21 18:26:40', '2026-10-21 18:56:58'),
(27, 2, ' saldo buhJI', 750000, 'PIUTANG BU HAJI', 1, '2026-10-22', 750000, '2026-10-21 18:26:40', '2026-10-21 18:56:58'),
(28, 2, 'M IYUS', 350000, 'PIUTANG IYUS', 1, '2026-10-22', 350000, '2026-10-21 18:26:40', '2026-10-21 19:06:23'),
(29, 2, 'AA', 100000, 'PRIVE', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(30, 2, 'JJN', 34500, 'PRIVE', 0, NULL, 0, '2026-10-21 18:26:40', '2026-10-21 18:26:40');

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
(8, 2, 'Pulsa & Deposit', 39000, 'Transaksi baris 4', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(9, 2, 'Barang Kasir Pintar', 97000, 'Transaksi baris 4', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(10, 2, 'Barang Titip Jual', 5000, 'salman', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(11, 2, 'Fastpay', 199525, 'Transaksi baris 4', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(12, 2, 'Payfazz', 24000, 'Transaksi baris 4', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(13, 2, 'Buku Warung', 40075000, 'Transaksi baris 4', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(14, 2, 'Penambahan Kas', 30000000, 'BRILINK ATUNG', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(15, 2, 'Pulsa & Deposit', 25000, 'Transaksi baris 5', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(16, 2, 'Barang Kasir Pintar', 11000, 'Transaksi baris 5', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(17, 2, 'Barang Titip Jual', 10000, 'serba 2k 5', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(18, 2, 'Fastpay', 770000, 'Transaksi baris 5', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(19, 2, 'Payfazz', 10020000, 'Transaksi baris 5', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(20, 2, 'Buku Warung', 24000, 'Transaksi baris 5', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(21, 2, 'Penambahan Kas', 47000000, 'BRANGKAS', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(22, 2, 'Pulsa & Deposit', 22000, 'Transaksi baris 6', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(23, 2, 'Barang Kasir Pintar', 15000, 'Transaksi baris 6', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(24, 2, 'Barang Titip Jual', 17500, 'axis 5/5', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(25, 2, 'Fastpay', 16000, 'Transaksi baris 6', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(26, 2, 'Payfazz', 855000, 'Transaksi baris 6', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(27, 2, 'Buku Warung', 104000, 'Transaksi baris 6', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(28, 2, 'Penambahan Kas', 120000000, 'BRANGKAS', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(29, 2, 'Pulsa & Deposit', 15000, 'Transaksi baris 7', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(30, 2, 'Barang Kasir Pintar', 227000, 'Transaksi baris 7', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(31, 2, 'Barang Titip Jual', 11000, 'v indosat 7/1', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(32, 2, 'Fastpay', 103000, 'Transaksi baris 7', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(33, 2, 'Payfazz', 403000, 'Transaksi baris 7', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(34, 2, 'Buku Warung', 24000, 'Transaksi baris 7', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(35, 2, 'Pulsa & Deposit', 27000, 'Transaksi baris 8', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(36, 2, 'Barang Kasir Pintar', 9000, 'Transaksi baris 8', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(37, 2, 'Barang Titip Jual', 5500, 'mizone', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(38, 2, 'Fastpay', 316075, 'Transaksi baris 8', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(39, 2, 'Payfazz', 405000, 'Transaksi baris 8', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(40, 2, 'Pulsa & Deposit', 17000, 'Transaksi baris 9', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(41, 2, 'Barang Kasir Pintar', 3500, 'Transaksi baris 9', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(42, 2, 'Barang Titip Jual', 18000, 'v tsel 7/5', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(43, 2, 'Payfazz', 153000, 'Transaksi baris 9', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(44, 2, 'Barang Kasir Pintar', 22000, 'Transaksi baris 10', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(45, 2, 'Barang Titip Jual', 10000, 'v tsel 7/1', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(46, 2, 'Payfazz', 778600, 'Transaksi baris 10', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(47, 2, 'Barang Kasir Pintar', 9000, 'Transaksi baris 11', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(48, 2, 'Payfazz', 203000, 'Transaksi baris 11', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(49, 2, 'Barang Kasir Pintar', 2500, 'Transaksi baris 12', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(50, 2, 'Payfazz', 10020000, 'Transaksi baris 12', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(51, 2, 'Barang Kasir Pintar', 18000, 'Transaksi baris 13', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(52, 2, 'Payfazz', 100000, 'Transaksi baris 13', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(53, 2, 'Barang Kasir Pintar', 7500, 'Transaksi baris 14', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(54, 2, 'Payfazz', 503000, 'Transaksi baris 14', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(55, 2, 'Barang Kasir Pintar', 4000, 'Transaksi baris 15', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(56, 2, 'Payfazz', 405000, 'Transaksi baris 15', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(57, 2, 'Barang Kasir Pintar', 20000, 'Transaksi baris 16', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(58, 2, 'Payfazz', 53000, 'Transaksi baris 16', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(59, 2, 'Barang Kasir Pintar', 56000, 'Transaksi baris 17', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(60, 2, 'Payfazz', 613000, 'Transaksi baris 17', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(61, 2, 'Barang Kasir Pintar', 7000, 'Transaksi baris 18', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(62, 2, 'Payfazz', 405000, 'Transaksi baris 18', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(63, 2, 'Barang Kasir Pintar', 4500, 'Transaksi baris 19', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(64, 2, 'Payfazz', 3415000, 'Transaksi baris 19', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(65, 2, 'Barang Kasir Pintar', 4000, 'Transaksi baris 20', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(66, 2, 'Payfazz', 63000, 'Transaksi baris 20', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(67, 2, 'Barang Kasir Pintar', 4000, 'Transaksi baris 21', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(68, 2, 'Payfazz', 30075000, 'Transaksi baris 21', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(69, 2, 'Barang Kasir Pintar', 7000, 'Transaksi baris 22', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(70, 2, 'Payfazz', 303000, 'Transaksi baris 22', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(71, 2, 'Barang Kasir Pintar', 7500, 'Transaksi baris 23', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(72, 2, 'Payfazz', 1004000, 'Transaksi baris 23', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(73, 2, 'Barang Kasir Pintar', 8000, 'Transaksi baris 24', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(74, 2, 'Payfazz', 10020000, 'Transaksi baris 24', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(75, 2, 'Barang Kasir Pintar', 17000, 'Transaksi baris 25', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(76, 2, 'Payfazz', 24000, 'Transaksi baris 25', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(77, 2, 'Barang Kasir Pintar', 4500, 'Transaksi baris 26', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(78, 2, 'Payfazz', 993000, 'Transaksi baris 26', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(79, 2, 'Payfazz', 255000, 'Transaksi baris 27', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(80, 2, 'Payfazz', 28000, 'Transaksi baris 28', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(81, 2, 'Payfazz', 405000, 'Transaksi baris 29', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(82, 2, 'Payfazz', 24000, 'Transaksi baris 30', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(83, 2, 'Payfazz', 153000, 'Transaksi baris 31', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(84, 2, 'Payfazz', 3207000, 'Transaksi baris 32', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(85, 2, 'Payfazz', 18000, 'Transaksi baris 33', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(86, 2, 'Payfazz', 153000, 'Transaksi baris 34', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(87, 2, 'Payfazz', 203000, 'Transaksi baris 35', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(88, 2, 'Payfazz', 53000, 'Transaksi baris 36', '2026-10-21 18:26:40', '2026-10-21 18:26:40');

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

INSERT INTO `brilink_reports` (`id`, `store_id`, `import_id`, `file_name`, `file_hash`, `periode_start`, `periode_end`, `total_omset_ppob`, `total_tarik_tunai_edc`, `total_tarik_tunai_mbanking`, `total_setoran_brilink`, `total_pendapatan_admin`, `validasi_kas_uang`, `manual_validasi_kas_uang`, `manual_validasi_mutasi_rek`, `manual_validasi_mutasi_fisik`, `laba_kasir_pintar`, `penambahan_brangkas`, `total_pengeluaran_riil`, `total_pindah_saldo`, `volume_transaksi_agen`, `saldo_awal_total`, `saldo_akhir_total`, `status`, `error_message`, `created_at`, `updated_at`) VALUES
(2, 1, 2, '211026HM_NEW_BUKU_BRILINK_PRO_V2.xlsx', '6e2948af8d0d6dce9a9599a13d3d0faa175530e30caf0bc6e1aee0b30ee99c15', '2026-10-22', '2026-10-22', 314755200, 149221000, 125342600, 317298615, 917000, 0, 54982500, 0, 0, 144686, 320000000, 0, 0, 180, 10133234, 200000000, 'imported', NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40');

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
(1, 2, 22005000, 19000, 'EDC AA', 22005000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(2, 2, 10001500, 0, NULL, 10001500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(3, 2, 1505000, 0, NULL, 1505000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(4, 2, 5005000, 15000, NULL, 5005000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(5, 2, 10005000, 0, NULL, 10005000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(6, 2, 2005000, 1000, NULL, 2005000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(7, 2, 10001500, 0, NULL, 10001500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(8, 2, 1601500, 2500, NULL, 1601500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(9, 2, 50005000, 0, NULL, 50005000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(10, 2, 20005000, 0, NULL, 20005000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(11, 2, 10005000, 0, NULL, 10005000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(12, 2, 756660, 3000, NULL, 756660, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(13, 2, 30004000, 0, NULL, 30004000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(14, 2, 6294500, 5500, NULL, 6294500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(15, 2, 15001500, 0, NULL, 15001500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(16, 2, 3651500, 4500, NULL, 3651500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(17, 2, 1001500, 0, NULL, 1001500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(18, 2, 115000, 3000, NULL, 115000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(19, 2, 6001500, 5500, NULL, 6001500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(20, 2, 8575900, 4000, NULL, 8575900, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(21, 2, 2401500, 3500, NULL, 2401500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(22, 2, 191500, 1500, NULL, 191500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(23, 2, 1840500, 2500, NULL, 1840500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(24, 2, 194529, 1500, NULL, 194529, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(25, 2, 31559220, 12000, NULL, 31559220, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(26, 2, 455372, 0, NULL, 455372, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(27, 2, 10001500, 0, 'EDC TETEH', 10001500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(28, 2, 303000, 2000, NULL, 303000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(29, 2, 12905684, 9000, NULL, 12905684, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(30, 2, 40003250, 0, NULL, 40003250, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(31, 2, 1005000, 0, NULL, 1005000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(32, 2, 423500, 1500, NULL, 423500, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(33, 2, 503000, 2000, NULL, 503000, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(34, 2, 1964500, 1500, NULL, 1964500, '2026-10-21 18:26:40', '2026-10-21 18:26:40');

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
(1, 2, 'edc_bri', 1004000, 4000, 'EDC AA', 0, 10000000, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(2, 2, 'mbanking', 300000, 5000, NULL, 0, 10000000, 'BCA', NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(3, 2, 'edc_bri', 7008000, 8000, NULL, 0, 10000000, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(4, 2, 'mbanking', 2000000, 5000, NULL, 0, 10000000, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(5, 2, 'edc_bri', 1004000, 4000, NULL, 0, 10000000, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(6, 2, 'mbanking', 150000, 5000, NULL, 0, 10000000, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(7, 2, 'edc_bri', 430000, 3000, NULL, 0, 10000000, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(8, 2, 'mbanking', 250000, 5000, NULL, 0, 10000000, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(9, 2, 'edc_bri', 4006000, 6000, NULL, 0, 10000000, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(10, 2, 'mbanking', 1000000, 5000, NULL, 0, 10000000, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(11, 2, 'edc_bri', 253000, 3000, NULL, 0, 30000000, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(12, 2, 'mbanking', 155000, 5000, NULL, 0, 30000000, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(13, 2, 'edc_bri', 6007000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(14, 2, 'mbanking', 50000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(15, 2, 'edc_bri', 403000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(16, 2, 'mbanking', 2900000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(17, 2, 'edc_bri', 30032000, 32000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(18, 2, 'mbanking', 2000000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(19, 2, 'edc_bri', 5000000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(20, 2, 'mbanking', 405000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(21, 2, 'edc_bri', 300000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(22, 2, 'mbanking', 2000000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(23, 2, 'edc_bri', 4630000, 6000, NULL, 0, 0, NULL, 'bayar wifi', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(24, 2, 'mbanking', 199600, 0, NULL, 0, 0, NULL, 'bayar wifi', '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(25, 2, 'edc_bri', 999000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(26, 2, 'mbanking', 380000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(27, 2, 'edc_bri', 9995000, 9000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(28, 2, 'mbanking', 3000000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(29, 2, 'edc_bri', 453000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(30, 2, 'mbanking', 2470000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(31, 2, 'edc_bri', 17019000, 19000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(32, 2, 'mbanking', 500000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(33, 2, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(34, 2, 'mbanking', 400000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(35, 2, 'edc_bri', 703000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(36, 2, 'mbanking', 805000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(37, 2, 'edc_bri', 300000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(38, 2, 'mbanking', 55000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(39, 2, 'edc_bri', 2005000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(40, 2, 'mbanking', 800000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(41, 2, 'edc_bri', 1704000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(42, 2, 'mbanking', 2000000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(43, 2, 'edc_bri', 2900000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(44, 2, 'mbanking', 110000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(45, 2, 'edc_bri', 203000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(46, 2, 'mbanking', 2305000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(47, 2, 'edc_bri', 253000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(48, 2, 'mbanking', 150000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(49, 2, 'edc_bri', 500000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(50, 2, 'mbanking', 430000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(51, 2, 'edc_bri', 470000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(52, 2, 'mbanking', 200000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(53, 2, 'edc_bri', 193000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(54, 2, 'mbanking', 1000000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(55, 2, 'edc_bri', 503000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(56, 2, 'mbanking', 625000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(57, 2, 'edc_bri', 7008000, 8000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(58, 2, 'mbanking', 485000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(59, 2, 'edc_bri', 2005000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(60, 2, 'mbanking', 24996000, 26000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(61, 2, 'edc_bri', 600000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(62, 2, 'mbanking', 705000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(63, 2, 'edc_bri', 287000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(64, 2, 'mbanking', 3100000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(65, 2, 'edc_bri', 1000000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(66, 2, 'mbanking', 975000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(67, 2, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(68, 2, 'mbanking', 518000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(69, 2, 'edc_bri', 203000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(70, 2, 'mbanking', 3459000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(71, 2, 'edc_bri', 1104000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(72, 2, 'mbanking', 3500000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(73, 2, 'edc_bri', 1504000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(74, 2, 'mbanking', 250000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(75, 2, 'edc_bri', 5000000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(76, 2, 'mbanking', 500000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(77, 2, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(78, 2, 'mbanking', 400000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(79, 2, 'edc_bri', 400000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(80, 2, 'mbanking', 1005000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(81, 2, 'edc_bri', 703000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(82, 2, 'mbanking', 650000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(83, 2, 'mbanking', 1979000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(84, 2, 'mbanking', 405000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(85, 2, 'mbanking', 260000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(86, 2, 'mbanking', 10900000, 0, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(87, 2, 'mbanking', 655000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(88, 2, 'mbanking', 1840000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(89, 2, 'mbanking', 1900000, 5000, NULL, 0, 0, 'BRI', NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(90, 2, 'mbanking', 45000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(91, 2, 'mbanking', 1500000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(92, 2, 'mbanking', 5000000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(93, 2, 'mbanking', 1995000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(94, 2, 'edc_bri', 736000, 3000, 'EDC TETEH', 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(95, 2, 'mbanking', 1000000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(96, 2, 'edc_bri', 500000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(97, 2, 'mbanking', 605000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(98, 2, 'edc_bri', 503000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(99, 2, 'mbanking', 14257000, 16000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(100, 2, 'edc_bri', 163000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(101, 2, 'mbanking', 1111000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(102, 2, 'edc_bri', 2055000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(103, 2, 'mbanking', 100000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(104, 2, 'edc_bri', 100000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(105, 2, 'mbanking', 5020000, 0, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(106, 2, 'edc_bri', 299000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(107, 2, 'mbanking', 70000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(108, 2, 'edc_bri', 213000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(109, 2, 'mbanking', 2000000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(110, 2, 'edc_bri', 295000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(111, 2, 'mbanking', 1092500, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(112, 2, 'edc_bri', 10010000, 10000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(113, 2, 'edc_bri', 495000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(114, 2, 'edc_bri', 100000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(115, 2, 'edc_bri', 3415000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(116, 2, 'edc_bri', 2005000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(117, 2, 'mbanking', 1554000, 4000, NULL, 0, 0, 'BNI', NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(118, 2, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(119, 2, 'edc_bri', 953000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(120, 2, 'edc_bri', 492000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(121, 2, 'edc_bri', 538000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(122, 2, 'edc_bri', 753000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(123, 2, 'mbanking', 205000, 5000, NULL, 0, 0, 'BSI', NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(124, 2, 'edc_bri', 483000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(125, 2, 'edc_bri', 3006000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(126, 2, 'mbanking', 500000, 5000, NULL, 0, 0, 'MANDIRI', NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(127, 2, 'mbanking', 500000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(128, 2, 'mbanking', 100000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(129, 2, 'mbanking', 605000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(130, 2, 'mbanking', 255000, 5000, NULL, 0, 0, 'QRIS', NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(131, 2, 'mbanking', 200000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(132, 2, 'mbanking', 185000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(133, 2, 'mbanking', 100000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(134, 2, 'mbanking', 205000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(135, 2, 'mbanking', 305000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(136, 2, 'mbanking', 208500, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(137, 2, 'mbanking', 150000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(138, 2, 'mbanking', 205000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(139, 2, 'mbanking', 300000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(140, 2, 'mbanking', 55000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(141, 2, 'mbanking', 105000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(142, 2, 'mbanking', 120000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(143, 2, 'mbanking', 125000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(144, 2, 'mbanking', 138000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(145, 2, 'mbanking', 55000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40'),
(146, 2, 'mbanking', 250000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40');

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

INSERT INTO `gaji_karyawan` (`id`, `karyawan_id`, `periode`, `gaji_pokok`, `bonus`, `potongan`, `total_gaji`, `status`, `tanggal_bayar`, `metode_bayar`, `created_at`, `updated_at`) VALUES
(2, 1, '2026-10', 1200000, 0, 100000, 1100000, 'dibayar', '2026-10-22', 'tunai', '2026-10-21 19:34:52', '2026-10-21 19:35:08'),
(3, 1, '2026-11', 1200000, 0, 0, 1200000, 'dibayar', '2026-10-22', 'tunai', '2026-10-21 19:42:14', '2026-10-21 19:42:39'),
(5, 1, '2026-12', 1200000, 0, 0, 1200000, 'dibayar', '2026-10-22', 'tunai', '2026-10-21 19:49:17', '2026-10-21 19:49:47');

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
(2, 1, 'brilink', '211026HM_NEW_BUKU_BRILINK_PRO_V2.xlsx', '6e2948af8d0d6dce9a9599a13d3d0faa175530e30caf0bc6e1aee0b30ee99c15', NULL, NULL, 308, 'imported', NULL, '2026-10-21 18:26:40', '2026-10-21 18:26:40');

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
(1, 1, 'ESA', 1200000, 1, '2026-10-20', NULL, '2026-10-21 19:33:36', '2026-10-21 19:41:29');

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

--
-- Dumping data for table `laporan_minus_karyawan`
--

INSERT INTO `laporan_minus_karyawan` (`id`, `karyawan_id`, `tanggal`, `nominal`, `keterangan`, `sudah_dipotong`, `gaji_karyawan_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-10-21', 100000, 'Ada minus saldo', 1, 2, '2026-10-21 19:34:48', '2026-10-21 19:35:08'),
(2, 1, '2026-10-21', 100000, 'hfj', 1, 3, '2026-10-21 19:42:00', '2026-10-21 19:42:39'),
(3, 1, '2026-10-21', 100000, 'segsd', 0, NULL, '2026-10-21 19:43:33', '2026-10-21 19:49:44'),
(4, 1, '2026-10-21', 100000, 'sdgsd', 0, NULL, '2026-10-21 19:48:51', '2026-10-21 19:49:44');

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
(33, '2026_10_22_015211_add_payment_tracking_to_brilink_pengeluaran', 2),
(34, '2026_10_22_022213_create_karyawan_table', 3),
(35, '2026_10_22_022217_create_gaji_karyawan_table', 3),
(36, '2026_10_22_022222_create_laporan_minus_karyawan_table', 3);

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
('FI5PkkIBMnENZf0eJj1EgESAgbm5HBiZkvcYwksF', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiY1ZIenFFU1V0YzRlU29WMVRQWnNna0JGcHJTaXBPdm5aUG1uc0huTCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAva2FyeWF3YW4iO3M6NToicm91dGUiO3M6MTQ6Imthcnlhd2FuLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiM2YzYzg3NjVjN2U5OTczOGExY2QwZDUxZTFlOGY3MDkwN2Q2MWM3NmE4NmQzMzdkZDhiZjBiMzcxZTA4NWJiMCI7fQ==', 1792612325);

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
(1, 'brilink', 'Brilink Sumelap', NULL, 1, '2026-10-21 18:24:51', '2026-10-21 18:24:51'),
(2, 'brilink', 'Brilink Gunajaya', NULL, 1, '2026-10-21 19:40:12', '2026-10-21 19:40:12');

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
(1, 'Asep Mubarok', 'admin@barokahsport.com', NULL, '$2y$12$NbXoWEpd0Fp1tMdFtHQxzOsyQOCJ.6qDs/nhxLc0y.7tDFNwNd41O', NULL, '2026-10-21 18:24:31', '2026-10-21 18:24:31');

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brilink_accounts`
--
ALTER TABLE `brilink_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `brilink_pengeluaran`
--
ALTER TABLE `brilink_pengeluaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `brilink_ppob_transactions`
--
ALTER TABLE `brilink_ppob_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `brilink_reports`
--
ALTER TABLE `brilink_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `brilink_setoran`
--
ALTER TABLE `brilink_setoran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `brilink_tarik_tunai`
--
ALTER TABLE `brilink_tarik_tunai`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gaji_karyawan`
--
ALTER TABLE `gaji_karyawan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `imports`
--
ALTER TABLE `imports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `laporan_minus_karyawan`
--
ALTER TABLE `laporan_minus_karyawan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
