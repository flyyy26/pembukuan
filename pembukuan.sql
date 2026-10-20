-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2026 at 12:21 AM
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
(1, 1, 'KAS', 'KAS', 97647700, 97647700, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(2, 1, 'MANG SALAREA', 'PULSA', 936358, 936358, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(3, 1, 'LINK AJA', 'PULSA', 2137451, 2137451, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(4, 1, 'MITRALINK', 'PPOB', 10612, 10612, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(5, 1, 'PAYFAZZ', 'PPOB', 9594353, 9594353, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(6, 1, 'BUKU WARUNG', 'PPOB', 565682, 565682, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(7, 1, 'FASTPAY', 'PPOB', 2536276, 2536276, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(8, 1, 'SALDO EDC AA', 'BRILINK', 0, 0, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(9, 1, 'SALDO EDC TETEH', '', 0, 0, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(10, 1, 'SALDO BRI 119 PENAMPUNGAN', 'BRILINK', 0, 0, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(11, 1, 'SALDO BRI 1417 PENARIKAN', 'BRILINK', 0, 0, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(12, 1, 'BCA', 'MBANKING', 0, 0, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(13, 1, 'MANDIRI', 'MBANKING', 0, 0, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(14, 1, 'BNI', 'MBANKING', 0, 0, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(15, 1, 'BSI', 'MBANKING', 0, 0, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(16, 1, 'BRANGKAS', 'BRANGKAS', 0, 0, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(17, 1, 'SALDO BARANG', 'POS', 0, 0, 0, '2026-10-20 18:57:50', '2026-10-20 18:57:50');

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brilink_pengeluaran`
--

INSERT INTO `brilink_pengeluaran` (`id`, `brilink_report_id`, `keterangan`, `nominal_kas`, `kategori`, `created_at`, `updated_at`) VALUES
(1, 1, 'fasfayy', 5001500, 'TOP UP PPOB', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(2, 1, 'tf bu haji', 8014400, 'PINDAH SALDO KAS INTERNAL', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(3, 1, 'kas at', 31200000, 'PINDAH SALDO KAS INTERNAL', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(4, 1, 'kas crh', 20000000, 'PINDAH SALDO KAS INTERNAL', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(5, 1, 'bu haji', 200000, 'PIUTANG BU HAJI', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(6, 1, 'perelek', 5000, 'PRIVE', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(7, 1, 'bu haji', 700000, 'PIUTANG BU HAJI', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(8, 1, 'bu haji', 182000, 'PIUTANG BU HAJI', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(9, 1, 'serba2k', 64000, 'PRIVE', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(10, 1, 'aa', 20000, 'PRIVE', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(11, 1, 'fruite tea', 97000, 'PRIVE', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(12, 1, 'm iyus', 250000, 'PIUTANG IYUS', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(13, 1, 'payfazz', 10001500, 'TOP UP PPOB', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(14, 1, 'kas crh', 10000000, 'PINDAH SALDO KAS INTERNAL', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(15, 1, 'kas at', 40000000, 'PINDAH SALDO KAS INTERNAL', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(16, 1, 'kas gn', 30000000, 'PINDAH SALDO KAS INTERNAL', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(17, 1, 'kas at', 31000000, 'PINDAH SALDO KAS INTERNAL', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(18, 1, 'kasbon t euis', 300000, 'OP KONVEKSI', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(19, 1, 'buhaji', 100000, 'PIUTANG BU HAJI', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(20, 1, 'bahan m entis', 1863000, 'OP KONVEKSI', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(21, 1, 'm entis', 1440000, 'OP KONVEKSI', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(22, 1, 'bila gudang', 154000, 'OP KONVEKSI', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(23, 1, 'siti gudang', 104000, 'OP KONVEKSI', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(24, 1, 'bila gudang', 217200, 'OP KONVEKSI', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(25, 1, 'm iyus', 54000, 'PIUTANG IYUS', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(26, 1, 'buhaji', 250000, 'PIUTANG BU HAJI', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(27, 1, 'm iyus', 230000, 'PIUTANG IYUS', '2026-10-20 18:57:50', '2026-10-20 18:57:50');

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
(1, 1, 'Pulsa & Deposit', 22000, 'Transaksi baris 4', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(2, 1, 'Barang Kasir Pintar', 48500, 'Transaksi baris 4', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(3, 1, 'Barang Titip Jual', 10000, 'salman 2', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(4, 1, 'Fastpay', 36114, 'Transaksi baris 4', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(5, 1, 'Payfazz', 23000, 'Transaksi baris 4', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(6, 1, 'Buku Warung', 24000, 'Transaksi baris 4', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(7, 1, 'Penambahan Kas', 100000000, 'Transaksi baris 4', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(8, 1, 'Pulsa & Deposit', 6000, 'Transaksi baris 5', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(9, 1, 'Barang Kasir Pintar', 4000, 'Transaksi baris 5', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(10, 1, 'Barang Titip Jual', 14000, 'serba 2k 7', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(11, 1, 'Fastpay', 114800, 'Transaksi baris 5', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(12, 1, 'Payfazz', 103000, 'Transaksi baris 5', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(13, 1, 'Buku Warung', 24000, 'Transaksi baris 5', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(14, 1, 'Penambahan Kas', 150000000, 'Transaksi baris 5', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(15, 1, 'Pulsa & Deposit', 21000, 'Transaksi baris 6', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(16, 1, 'Barang Kasir Pintar', 8000, 'Transaksi baris 6', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(17, 1, 'Barang Titip Jual', 10000, 'parfum 10k', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(18, 1, 'Fastpay', 427075, 'Transaksi baris 6', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(19, 1, 'Payfazz', 520000, 'Transaksi baris 6', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(20, 1, 'Buku Warung', 24000, 'Transaksi baris 6', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(21, 1, 'Penambahan Kas', 100000000, 'Transaksi baris 6', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(22, 1, 'Barang Kasir Pintar', 4500, 'Transaksi baris 7', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(23, 1, 'Barang Titip Jual', 25000, 'thermal mesin edc', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(24, 1, 'Fastpay', 830000, 'Transaksi baris 7', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(25, 1, 'Payfazz', 703000, 'Transaksi baris 7', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(26, 1, 'Buku Warung', 24000, 'Transaksi baris 7', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(27, 1, 'Penambahan Kas', 100000000, 'Transaksi baris 7', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(28, 1, 'Barang Kasir Pintar', 15000, 'Transaksi baris 8', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(29, 1, 'Barang Titip Jual', 5500, 'mizone', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(30, 1, 'Fastpay', 68849, 'Transaksi baris 8', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(31, 1, 'Payfazz', 203000, 'Transaksi baris 8', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(32, 1, 'Buku Warung', 54000, 'Transaksi baris 8', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(33, 1, 'Barang Kasir Pintar', 8000, 'Transaksi baris 9', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(34, 1, 'Fastpay', 393775, 'Transaksi baris 9', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(35, 1, 'Payfazz', 1555000, 'Transaksi baris 9', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(36, 1, 'Buku Warung', 54000, 'Transaksi baris 9', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(37, 1, 'Barang Kasir Pintar', 4500, 'Transaksi baris 10', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(38, 1, 'Fastpay', 123000, 'Transaksi baris 10', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(39, 1, 'Payfazz', 208000, 'Transaksi baris 10', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(40, 1, 'Buku Warung', 104000, 'Transaksi baris 10', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(41, 1, 'Barang Kasir Pintar', 7500, 'Transaksi baris 11', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(42, 1, 'Fastpay', 23000, 'Transaksi baris 11', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(43, 1, 'Payfazz', 753000, 'Transaksi baris 11', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(44, 1, 'Buku Warung', 54000, 'Transaksi baris 11', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(45, 1, 'Barang Kasir Pintar', 4000, 'Transaksi baris 12', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(46, 1, 'Fastpay', 36114, 'Transaksi baris 12', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(47, 1, 'Payfazz', 24000, 'Transaksi baris 12', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(48, 1, 'Buku Warung', 104000, 'Transaksi baris 12', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(49, 1, 'Barang Kasir Pintar', 2500, 'Transaksi baris 13', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(50, 1, 'Fastpay', 93000, 'Transaksi baris 13', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(51, 1, 'Payfazz', 15000, 'Transaksi baris 13', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(52, 1, 'Barang Kasir Pintar', 23500, 'Transaksi baris 14', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(53, 1, 'Fastpay', 30260, 'Transaksi baris 14', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(54, 1, 'Payfazz', 667000, 'Transaksi baris 14', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(55, 1, 'Barang Kasir Pintar', 4000, 'Transaksi baris 15', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(56, 1, 'Fastpay', 47549, 'Transaksi baris 15', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(57, 1, 'Payfazz', 805000, 'Transaksi baris 15', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(58, 1, 'Barang Kasir Pintar', 4500, 'Transaksi baris 16', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(59, 1, 'Fastpay', 103000, 'Transaksi baris 16', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(60, 1, 'Payfazz', 24000, 'Transaksi baris 16', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(61, 1, 'Barang Kasir Pintar', 20000, 'Transaksi baris 17', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(62, 1, 'Fastpay', 43000, 'Transaksi baris 17', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(63, 1, 'Payfazz', 360000, 'Transaksi baris 17', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(64, 1, 'Barang Kasir Pintar', 4500, 'Transaksi baris 18', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(65, 1, 'Fastpay', 610000, 'Transaksi baris 18', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(66, 1, 'Payfazz', 100000, 'Transaksi baris 18', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(67, 1, 'Barang Kasir Pintar', 20000, 'Transaksi baris 19', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(68, 1, 'Fastpay', 157806, 'Transaksi baris 19', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(69, 1, 'Payfazz', 213000, 'Transaksi baris 19', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(70, 1, 'Barang Kasir Pintar', 4500, 'Transaksi baris 20', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(71, 1, 'Fastpay', 120509, 'Transaksi baris 20', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(72, 1, 'Payfazz', 23000, 'Transaksi baris 20', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(73, 1, 'Fastpay', 853000, 'Transaksi baris 21', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(74, 1, 'Payfazz', 2005000, 'Transaksi baris 21', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(75, 1, 'Fastpay', 503000, 'Transaksi baris 22', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(76, 1, 'Payfazz', 560000, 'Transaksi baris 22', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(77, 1, 'Fastpay', 802000, 'Transaksi baris 23', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(78, 1, 'Payfazz', 360000, 'Transaksi baris 23', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(79, 1, 'Fastpay', 84212, 'Transaksi baris 24', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(80, 1, 'Payfazz', 753000, 'Transaksi baris 24', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(81, 1, 'Fastpay', 79878, 'Transaksi baris 25', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(82, 1, 'Payfazz', 1329000, 'Transaksi baris 25', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(83, 1, 'Fastpay', 247643, 'Transaksi baris 26', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(84, 1, 'Payfazz', 153000, 'Transaksi baris 26', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(85, 1, 'Fastpay', 77373, 'Transaksi baris 27', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(86, 1, 'Payfazz', 603000, 'Transaksi baris 27', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(87, 1, 'Fastpay', 343000, 'Transaksi baris 28', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(88, 1, 'Payfazz', 355000, 'Transaksi baris 28', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(89, 1, 'Fastpay', 175528, 'Transaksi baris 29', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(90, 1, 'Payfazz', 2015000, 'Transaksi baris 29', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(91, 1, 'Fastpay', 72799, 'Transaksi baris 30', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(92, 1, 'Payfazz', 153000, 'Transaksi baris 30', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(93, 1, 'Fastpay', 69532, 'Transaksi baris 31', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(94, 1, 'Payfazz', 100000, 'Transaksi baris 31', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(95, 1, 'Fastpay', 159169, 'Transaksi baris 32', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(96, 1, 'Fastpay', 283000, 'Transaksi baris 33', '2026-10-20 18:57:50', '2026-10-20 18:57:50');

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
(1, 1, 1, 'UPDATE FIX TEMPLATE BRILINK.xlsx', 'ca83836b7b096a3048647051c53be399788fb78bba05fe7c9b5c37fbd0066024', '2026-10-21', '2026-10-21', 472459985, 150707000, 218573500, 64997749, 1049310, 0, -22441100, 0, 0, 0, 0, 0, 0, 161, 0, 0, 'imported', NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50');

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
(1, 1, 3105000, 2000, 'EDC AA', 3105000, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(2, 1, 1705000, 0, NULL, 1705000, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(3, 1, 933000, 2000, NULL, 933000, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(4, 1, 1005000, 0, NULL, 1005000, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(5, 1, 2552500, 2500, NULL, 2552500, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(6, 1, 5001500, 0, NULL, 5001500, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(7, 1, 661500, 1500, NULL, 661500, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(8, 1, 2805000, 1000, NULL, 2805000, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(9, 1, 1655000, 0, NULL, 1655000, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(10, 1, 171500, 3000, NULL, 171500, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(11, 1, 3245500, 4500, NULL, 3245500, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(12, 1, 2505000, 1000, NULL, 2505000, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(13, 1, 595500, 4500, NULL, 595500, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(14, 1, 980660, 3000, NULL, 980660, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(15, 1, 1005000, 0, NULL, 1005000, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(16, 1, 10001500, 0, NULL, 10001500, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(17, 1, 1801500, 2500, NULL, 1801500, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(18, 1, 3283900, 3000, NULL, 3283900, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(19, 1, 1777500, 1500, 'EDC AA', 1777500, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(20, 1, 6990789, 4000, NULL, 6990789, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(21, 1, 36500, 1500, NULL, 36500, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(22, 1, 8014400, 0, 'EDC TETEH', 8014400, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(23, 1, 1355000, 0, NULL, 1355000, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(24, 1, 1205000, 0, NULL, 1205000, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(25, 1, 2605000, 1000, NULL, 2605000, '2026-10-20 18:57:50', '2026-10-20 18:57:50');

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
(1, 1, 'edc_bri', 5000000, 7000, 'EDC AA', 47250, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(2, 1, 'mbanking', 1550000, 5000, NULL, 0, 0, 'BCA', 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(3, 1, 'edc_bri', 6200000, 7000, NULL, 109060, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(4, 1, 'mbanking', 1105000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(5, 1, 'edc_bri', 14670000, 16000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(6, 1, 'mbanking', 500000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(7, 1, 'edc_bri', 253000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(8, 1, 'mbanking', 1420000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(9, 1, 'edc_bri', 103000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(10, 1, 'mbanking', 150000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(11, 1, 'edc_bri', 3126000, 6000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(12, 1, 'mbanking', 200000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(13, 1, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(14, 1, 'mbanking', 250000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(15, 1, 'edc_bri', 999000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(16, 1, 'mbanking', 2000000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(17, 1, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(18, 1, 'mbanking', 3000000, 6000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(19, 1, 'edc_bri', 2505000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(20, 1, 'mbanking', 755000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(21, 1, 'edc_bri', 603000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(22, 1, 'mbanking', 17000000, 19000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(23, 1, 'edc_bri', 2789000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(24, 1, 'mbanking', 200000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(25, 1, 'edc_bri', 311000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(26, 1, 'mbanking', 550000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(27, 1, 'edc_bri', 1950000, 4000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(28, 1, 'mbanking', 5338000, 7000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(29, 1, 'edc_bri', 1000000, 4000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(30, 1, 'mbanking', 6021000, 7000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(31, 1, 'edc_bri', 2500000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(32, 1, 'mbanking', 4170000, 6000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(33, 1, 'edc_bri', 403000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(34, 1, 'mbanking', 1005000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(35, 1, 'edc_bri', 5000000, 7000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(36, 1, 'mbanking', 5007000, 7000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(37, 1, 'edc_bri', 5000000, 7000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(38, 1, 'mbanking', 3656000, 6000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(39, 1, 'edc_bri', 2005000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(40, 1, 'mbanking', 300000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(41, 1, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(42, 1, 'mbanking', 2355000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(43, 1, 'edc_bri', 503000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(44, 1, 'mbanking', 525000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(45, 1, 'edc_bri', 2937000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(46, 1, 'mbanking', 100102000, 102000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(47, 1, 'edc_bri', 353000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(48, 1, 'mbanking', 4147000, 6000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(49, 1, 'edc_bri', 200000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(50, 1, 'mbanking', 1400000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(51, 1, 'edc_bri', 203000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(52, 1, 'mbanking', 400000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(53, 1, 'edc_bri', 349000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(54, 1, 'mbanking', 90000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(55, 1, 'edc_bri', 233000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(56, 1, 'mbanking', 100000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(57, 1, 'edc_bri', 253000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(58, 1, 'mbanking', 100000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(59, 1, 'edc_bri', 1004000, 4000, NULL, 0, 0, NULL, 'KIRANG INDOMARCO', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(60, 1, 'mbanking', 35000, 0, NULL, 0, 0, NULL, 'KIRANG INDOMARCO', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(61, 1, 'edc_bri', 103000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(62, 1, 'edc_bri', 5007000, 7000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(63, 1, 'edc_bri', 495000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(64, 1, 'edc_bri', 1100000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(65, 1, 'edc_bri', 4006000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(66, 1, 'edc_bri', 550000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(67, 1, 'mbanking', 400000, 5000, NULL, 0, 0, 'BRI', 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(68, 1, 'mbanking', 800000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(69, 1, 'mbanking', 7000000, 8000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(70, 1, 'mbanking', 1300000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(71, 1, 'mbanking', 1000000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(72, 1, 'mbanking', 2000000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(73, 1, 'mbanking', 500000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(74, 1, 'mbanking', 2500000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(75, 1, 'mbanking', 150000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(76, 1, 'mbanking', 8500000, 8000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(77, 1, 'mbanking', 360000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(78, 1, 'mbanking', 18950000, 20000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(79, 1, 'mbanking', 4000000, 6000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(80, 1, 'mbanking', 1000000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(81, 1, 'mbanking', 500000, 5000, NULL, 0, 0, 'MANDIRI', 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(82, 1, 'mbanking', 505000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(83, 1, 'mbanking', 1000000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(84, 1, 'mbanking', 500000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(85, 1, 'edc_bri', 503000, 3000, 'EDC TETEH', 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(86, 1, 'mbanking', 150000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(87, 1, 'edc_bri', 303000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(88, 1, 'edc_bri', 2338000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(89, 1, 'edc_bri', 903000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(90, 1, 'edc_bri', 2789000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(91, 1, 'edc_bri', 2000000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(92, 1, 'edc_bri', 500000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(93, 1, 'edc_bri', 300000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(94, 1, 'edc_bri', 2010000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(95, 1, 'edc_bri', 2000000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(96, 1, 'mbanking', 500000, 5000, NULL, 0, 0, 'QRIS', 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(97, 1, 'edc_bri', 2000000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(98, 1, 'mbanking', 305000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(99, 1, 'edc_bri', 840000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(100, 1, 'mbanking', 155000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(101, 1, 'edc_bri', 103000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(102, 1, 'mbanking', 205000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(103, 1, 'edc_bri', 1000000, 4000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(104, 1, 'mbanking', 155000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(105, 1, 'edc_bri', 3440000, 6000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(106, 1, 'mbanking', 205000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(107, 1, 'edc_bri', 550000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(108, 1, 'mbanking', 350000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(109, 1, 'edc_bri', 4000000, 6000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(110, 1, 'mbanking', 180000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(111, 1, 'edc_bri', 217000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(112, 1, 'mbanking', 205000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(113, 1, 'edc_bri', 503000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(114, 1, 'mbanking', 195000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(115, 1, 'edc_bri', 17834000, 19000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(116, 1, 'mbanking', 350000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(117, 1, 'edc_bri', 203000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(118, 1, 'mbanking', 455000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(119, 1, 'edc_bri', 1504000, 4000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(120, 1, 'mbanking', 245000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(121, 1, 'edc_bri', 2105000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(122, 1, 'mbanking', 200000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(123, 1, 'edc_bri', 203000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(124, 1, 'mbanking', 117500, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(125, 1, 'edc_bri', 303000, 3000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(126, 1, 'mbanking', 205000, 5000, NULL, 0, 0, NULL, 'TF', '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(127, 1, 'edc_bri', 1595000, 4000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(128, 1, 'edc_bri', 10010000, 10000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(129, 1, 'edc_bri', 103000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(130, 1, 'edc_bri', 179000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(131, 1, 'edc_bri', 2005000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(132, 1, 'edc_bri', 353000, 3000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(133, 1, 'edc_bri', 4770000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(134, 1, 'edc_bri', 2505000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(135, 1, 'edc_bri', 4006000, 6000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50'),
(136, 1, 'edc_bri', 2005000, 5000, NULL, 0, 0, NULL, NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50');

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
(1, 1, 'brilink', 'UPDATE FIX TEMPLATE BRILINK.xlsx', 'ca83836b7b096a3048647051c53be399788fb78bba05fe7c9b5c37fbd0066024', NULL, NULL, 301, 'imported', NULL, '2026-10-20 18:57:50', '2026-10-20 18:57:50');

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
(32, '2026_10_21_014509_add_keterangan_columns_to_brilink_tarik_tunai_table', 1);

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
('t9donX7Txoa09ZOaSelnvGzGpbqhfoqAr927alJF', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiZHhJaGJkZExvYVVTb3BxajlkaG5LOUxFOFBqS1pUOGFMcGlQUEFwcyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvdGlrdG9rL2ltcG9ydCI7czo1OiJyb3V0ZSI7czoxMzoidGlrdG9rLmltcG9ydCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6ImVjYzg1NjhhNGU5NmFhNzM3ZWZmY2RhNGNmNWRkNzcyNGI3Nzc5NTMxNGYxMzQ3YmQ2YTQ3MzAwMjlkNTA1ZTgiO30=', 1792530114);

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
(1, 'brilink', 'Brilink Sumelap', NULL, 1, '2026-10-20 18:57:43', '2026-10-20 18:57:43');

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
(1, 'Asep Mubarok', 'admin@barokahsport.com', NULL, '$2y$12$NrQllyYBNMJ/c63rIPyoo.xvDj9gZlNRI6JglG/27hFh92CukUq6q', NULL, '2026-10-20 18:57:27', '2026-10-20 18:57:27');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `brilink_pengeluaran`
--
ALTER TABLE `brilink_pengeluaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `brilink_ppob_transactions`
--
ALTER TABLE `brilink_ppob_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `brilink_reports`
--
ALTER TABLE `brilink_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `brilink_setoran`
--
ALTER TABLE `brilink_setoran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `brilink_tarik_tunai`
--
ALTER TABLE `brilink_tarik_tunai`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `imports`
--
ALTER TABLE `imports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

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
-- Constraints for table `imports`
--
ALTER TABLE `imports`
  ADD CONSTRAINT `imports_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL;

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
