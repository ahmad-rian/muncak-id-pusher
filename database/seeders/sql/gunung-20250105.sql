-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 05, 2025 at 01:35 PM
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
-- Dumping data for table `gunung`
--

INSERT INTO `gunung` (`id`, `kode_kabupaten_kota`, `nama`, `slug`, `deskripsi`, `long`, `lat`, `elev`, `point`, `created_at`, `updated_at`) VALUES
(1, '33.23', 'Sindoro', 'sindoro', 'Gunung Sindoro menjulang tinggi di tengah Pulau Jawa, berdiri megah dengan ketinggian sekitar 3.150 meter di atas permukaan laut. Layaknya dua saudara kembar, Gunung Sindoro dan Gunung Sumbing berdampingan, menciptakan lanskap alami yang memukau mata setiap pendaki dengan keindahan alam yang mempesona. Pendakian ke puncaknya adalah perjalanan melintasi keharmonisan antara alam dan manusia. Jalur yang menantang menjadi bagian tersendiri bagi para pendaki yang berjuang melawan lelah demi menyaksikan matahari pagi dari sudut Gunung Merbabu, Merapi, dan Lawu.', '109.99586908104298', '-7.3020264538039585', 3146, 0x000000000101000000588bab51bc7f5b409e75366c46351dc0, '2024-11-06 23:34:59', '2024-11-26 11:47:15'),
(2, '52.08', 'Rinjani', 'rinjani', 'Gunung Rinjani adalah gunung berapi aktif yang terletak di Pulau Lombok, Nusa Tenggara Barat, Indonesia. Dengan ketinggian mencapai 3.146 meter di atas permukaan laut, Gunung Rinjani adalah gunung tertinggi kedua di Indonesia setelah Gunung Semeru. Gunung ini terkenal dengan kaldera luasnya yang membentuk Danau Segara Anak, sebuah danau vulkanik yang merupakan salah satu daya tarik utama bagi para pendaki. Rinjani merupakan bagian dari Taman Nasional Gunung Rinjani yang menawarkan berbagai jenis ekosistem, termasuk hutan hujan tropis, padang rumput alpine, dan kawasan vulkanik. Pendakian ke puncak Gunung Rinjani menawarkan pemandangan spektakuler dan merupakan salah satu pengalaman mendaki yang paling populer di Indonesia.', '116.4579010559627', '-8.41196114070152', 3726, 0x000000000101000000790a3b404e1d5d405f121592ecd220c0, '2024-11-06 23:34:59', '2024-11-06 23:34:59'),
(3, '33.23', 'Sumbing', 'sumbing', 'Gunung Sumbing adalah gunung api yang terdapat di Jawa Tengah, Indonesia. (Ketinggian puncak 3.371 mdpl), gunung Sumbing merupakan gunung tertinggi ketiga di Pulau Jawa setelah Gunung Semeru dan Gunung Slamet. Gunung ini secara administratif terletak di tiga wilayah kabupaten, yaitu Kabupaten Magelang; Kabupaten Temanggung; dan Kabupaten Wonosobo. Bersama dengan Gunung Sindoro, Gunung Sumbing membentuk bentang alam gunung kembar, seperti Gunung Merapi dan Gunung Merbabu, apabila dilihat dari arah Temanggung.', '110.07267138333827', '-7.383850273910521', 3371, 0x000000000101000000c2b2dfa5a6845b407b06d40b10891dc0, '2024-10-04 21:28:21', '2024-11-12 08:32:39'),
(4, '33.03', 'Slamet', 'slamet', 'Gunung Slamet adalah gunung tertinggi di Jawa Tengah dan gunung tertinggi kedua di pulau Jawa, setelah Gunung Semeru. Gunung Slamet merupakan sebuah gunung berapi kerucut tipe A yang berada di Jawa Tengah, Indonesia, dan merupakan gunung tunggal yang terpisah dari pegunungan. Gunung Slamet memiliki ketinggian 3.432 mdpl dan terletak di antara 5 kabupaten, yaitu Kabupaten Banyumas, Kabupaten Purbalingga, Kabupaten Pemalang, Kabupaten Tegal dan Kabupaten Brebes.', '109.2166489050698', '-7.241508941777212', 3432, 0x0000000001010000004c7f5e93dd4d5b40b07fba1e4ef71cc0, '2024-12-12 11:14:57', '2024-12-12 11:14:57'),
(5, '35.20', 'Lawu', 'lawu', 'Gunung di perbatasan Jateng Jatim', '111.19415732467145', '-7.627400007420942', 3265, 0x00000000010100000089efd7126dcc5b404c88c52575821ec0, '2024-12-28 20:55:28', '2024-12-28 22:58:14'),
(6, '15.01', 'Kerinci', 'kerinci', 'Gunung api tertinggi di Indonesia', '101.26560201633565', '-1.695035305988286', 3805, 0x000000000101000000737a999fff505940878f4c57dd1efbbf, '2024-12-28 23:11:16', '2024-12-28 23:11:16'),
(7, '35.10', 'Ijen', 'ijen', 'Menuju kawah', '114.2445729537334', '-8.043944529630538', 2769, 0x00000000010100000059715115a78f5c40d933bbe57f1620c0, '2024-12-28 23:38:14', '2024-12-28 23:38:14'),
(8, '33.07', 'Sikunir', 'sikunir', 'Bukit populer melihat matahari terbit', '109.9297846109236', '-7.238428005029374', 2263, 0x0000000001010000006c0f5097817b5b40ed34907826f41cc0, '2024-12-29 04:53:03', '2024-12-29 04:59:08'),
(9, '35.08', 'Semeru', 'semeru', 'Gunung api tertinggi di Pulau Jawa', '112.92373148216639', '-8.108296263238147', 3676, 0x0000000001010000002d8ca66a1e3b5c4011c6999b723720c0, '2024-12-30 21:09:19', '2024-12-30 21:21:54'),
(10, '32.03', 'Gede', 'gede', 'Gunung Gede merupakan sebuah gunung berapi kerucut Tipe A yang berada di bagian barat Pulau Jawa, Indonesia. Gunung Gede berada dalam ruang lingkup Taman Nasional Gede Pangrango, yang merupakan salah satu dari lima taman nasional yang pertama kali diumumkan di Indonesia pada tahun 1980. Gunung ini berada di dua wilayah kabupaten yaitu Kabupaten Cianjur dan Sukabumi, dengan ketinggian 1.000 - 2.961 mdpl, dan berada pada lintang 106°51\' - 107°02\' BT dan 64°1\' - 65°1 LS. Suhu rata-rata di puncak gunung Gede adalah 18 °C di siang hari dan di malam hari suhu puncak berkisar 5 °C, dengan curah hujan rata-rata 3.600 mm/tahun (Source: Wikipedia).', '106.9826369016485', '-6.790093070717774', 2961, 0x000000000101000000141be385e3be5a40c01d6e280e291bc0, '2025-01-03 01:18:10', '2025-01-04 17:36:46'),
(11, '32.01', 'Salak', 'salak', 'Gunung dengan hutan hujan tropis lebat', '106.73376202182244', '-6.715760095965672', 2215, 0x0000000001010000008db1fbf4f5ae5a404ad1ef36f0dc1ac0, '2025-01-03 23:26:24', '2025-01-03 23:48:55'),
(14, '13.02', 'Talang', 'talang', 'Jalur populer', '100.68431777265796', '-0.9756390366184237', 2597, 0x000000000101000000cd68c5dccb2b594035445f5b6f38efbf, '2025-01-05 06:06:34', '2025-01-05 06:06:34');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
