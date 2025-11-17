-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 15, 2025 at 07:33 AM
-- Server version: 10.11.13-MariaDB-ubu2204
-- PHP Version: 8.3.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `compro_muncak`
--

--
-- Dumping data for table `negara`
--

INSERT INTO `negara` (`id`, `nama`, `slug`, `nama_lain`, `kode`, `created_at`, `updated_at`) VALUES
(1, 'Indonesia', 'indonesia', 'Indonesia', 'ID', '2025-08-01 23:21:10', '2025-08-01 23:31:30'),
(2, 'Malaysia', 'malaysia', 'Malaysia', 'MY', '2025-08-01 23:24:22', '2025-08-01 23:24:22');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
