-- -------------------------------------------------------------
-- TablePlus 6.1.8(574)
--
-- https://tableplus.com/
--
-- Database: pendakian_gis_db
-- Generation Time: 2024-12-01 9:21:57.0300 PM
-- -------------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


INSERT INTO `gunung` (`id`, `kode_kabupaten_kota`, `nama`, `slug`, `deskripsi`, `long`, `lat`, `elev`, `point`, `created_at`, `updated_at`) VALUES
(1, '33.23', 'Sindoro', 'sindoro', 'Gunung Sindoro menjulang tinggi di tengah Pulau Jawa, berdiri megah dengan ketinggian sekitar 3.150 meter di atas permukaan laut. Layaknya dua saudara kembar, Gunung Sindoro dan Gunung Sumbing berdampingan, menciptakan lanskap alami yang memukau mata setiap pendaki dengan keindahan alam yang mempesona. Pendakian ke puncaknya adalah perjalanan melintasi keharmonisan antara alam dan manusia. Jalur yang menantang menjadi bagian tersendiri bagi para pendaki yang berjuang melawan lelah demi menyaksikan matahari pagi dari sudut Gunung Merbabu, Merapi, dan Lawu.', '109.99586908104298', '-7.3020264538039585', 3146, ST_GeomFromText('POINT(109.995869081043 -7.302026453803959)'), '2024-11-07 06:34:59', '2024-11-26 18:47:15'),
(2, '52.08', 'Rinjani', 'rinjani', 'Gunung Rinjani adalah gunung berapi aktif yang terletak di Pulau Lombok, Nusa Tenggara Barat, Indonesia. Dengan ketinggian mencapai 3.146 meter di atas permukaan laut, Gunung Rinjani adalah gunung tertinggi kedua di Indonesia setelah Gunung Semeru. Gunung ini terkenal dengan kaldera luasnya yang membentuk Danau Segara Anak, sebuah danau vulkanik yang merupakan salah satu daya tarik utama bagi para pendaki. Rinjani merupakan bagian dari Taman Nasional Gunung Rinjani yang menawarkan berbagai jenis ekosistem, termasuk hutan hujan tropis, padang rumput alpine, dan kawasan vulkanik. Pendakian ke puncak Gunung Rinjani menawarkan pemandangan spektakuler dan merupakan salah satu pengalaman mendaki yang paling populer di Indonesia.', '116.4579010559627', '-8.41196114070152', 3726, ST_GeomFromText('POINT(116.4579010559627 -8.41196114070152)'), '2024-11-07 06:34:59', '2024-11-07 06:34:59'),
(3, '33.23', 'Sumbing', 'sumbing', 'Gunung Sumbing adalah gunung api yang terdapat di Jawa Tengah, Indonesia. (Ketinggian puncak 3.371 mdpl), gunung Sumbing merupakan gunung tertinggi ketiga di Pulau Jawa setelah Gunung Semeru dan Gunung Slamet. Gunung ini secara administratif terletak di tiga wilayah kabupaten, yaitu Kabupaten Magelang; Kabupaten Temanggung; dan Kabupaten Wonosobo. Bersama dengan Gunung Sindoro, Gunung Sumbing membentuk bentang alam gunung kembar, seperti Gunung Merapi dan Gunung Merbabu, apabila dilihat dari arah Temanggung.', '110.07267138333827', '-7.383850273910521', 3371, ST_GeomFromText('POINT(110.0726713833383 -7.383850273910521)'), '2024-10-05 04:28:21', '2024-11-12 15:32:39');


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;