-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 05, 2025 at 01:37 PM
-- Server version: 10.11.10-MariaDB-ubu2204
-- PHP Version: 8.3.15

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
-- Dumping data for table `rute_tingkat_kesulitan`
--

INSERT INTO `rute_tingkat_kesulitan` (`id`, `nama`, `slug`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'Pemula', 'pemula', NULL, '2024-12-04 19:55:20', '2024-12-10 10:44:18'),
(2, 'Menengah', 'menengah', NULL, '2024-12-10 10:44:25', '2024-12-10 10:44:25'),
(3, 'Ahli', 'ahli', NULL, '2024-12-10 10:44:30', '2024-12-10 10:44:30'),
(4, 'Profesional', 'profesional', NULL, '2024-12-19 06:24:09', '2024-12-19 06:24:09');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
