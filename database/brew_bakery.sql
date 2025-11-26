-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2025 at 02:34 PM
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
-- Database: `brew_bakery`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `nama`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Admin Brew Bakery', 'admin@brewbakery.com', '$2y$10$/ih0haemB3TU1o4Ty2j8fOxXlP8h5YNOyu9m8Lh5v48iqUUuUS.1i', '2025-11-17 14:00:35', '2025-11-17 15:13:30');

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `isi` longtext NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `judul`, `deskripsi`, `isi`, `foto`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Cara Membuat Roti Tawar Lembut dan Enak', 'Tips membuat roti tawar yang lembut dan empuk', '<p>Untuk membuat roti tawar yang lembut, Anda perlu:</p><ol><li>Gunakan tepung berkualitas tinggi</li><li>Jangan kurangi air</li><li>Fermentasi dengan waktu yang tepat</li><li>Panggang dengan suhu konsisten</li></ol>', '691c2e14eb463_1763454484.jpg', 1, '2025-11-17 14:00:35', '2025-11-25 10:41:24'),
(2, 'Resep Croissant Paling Enak', 'Croissant dengan laminating yang sempurna', '<p>Croissant yang bagus perlu:</p><ol><li>Butter berkualitas premium</li><li>Laminating yang hati-hati</li><li>Fermentasi dingin (cold fermentation)</li><li>Oven dengan suhu tinggi</li></ol>', '692522734513e_1764041331.jpg', 1, '2025-11-17 14:00:35', '2025-11-25 03:28:51'),
(3, 'Cara Membuat Cake Coklat Sederhana dan Lembut', 'Panduan lengkap membuat cake coklat yang lembut dan moist dengan bahan yang mudah ditemukan dan langkah yang sederhana. Cocok untuk pemula maupun yang ingin belajar membuat kue.', '<p><strong>Cara Membuat Cake Coklat Sederhana dan Lembut</strong></p><p>Cake coklat adalah salah satu kue yang paling disukai banyak orang karena rasa manisnya yang khas dan tekstur lembut ketika disantap. Meskipun terlihat rumit, sebenarnya membuat cake coklat bisa dilakukan dengan sangat mudah, bahkan oleh pemula. Berikut panduan lengkapnya.</p><h4><strong>Bahan-bahan</strong></h4><p>200 gram tepung terigu serbaguna</p><p>200 gram gula pasir</p><p>50 gram bubuk coklat</p><p>1 sdt baking powder</p><p>1/2 sdt baking soda</p><p>3 butir telur</p><p>200 ml susu cair</p><p>100 ml minyak sayur atau mentega cair</p><p>1 sdt vanila</p><p>1/2 sdt garam</p><h4><strong>Alat yang Dibutuhkan</strong></h4><p>Wadah/mangkuk</p><p>Mixer atau whisk</p><p>Loyang</p><p>Oven</p><h4><strong>Langkah-langkah Pembuatan</strong></h4><p><strong>Siapkan loyang dan oven</strong><br>Olesi loyang dengan margarin dan taburi sedikit tepung agar kue tidak lengket. Panaskan oven pada suhu 170°C.</p><p><strong>Campur bahan kering</strong><br>Ayak tepung terigu, bubuk coklat, baking powder, baking soda, dan garam ke dalam satu wadah. Aduk hingga tercampur rata.</p><p><strong>Kocok bahan basah</strong><br>Dalam wadah lain, kocok telur dan gula hingga larut dan mengembang. Tambahkan minyak dan vanila, lalu aduk kembali.</p><p><strong>Gabungkan adonan</strong><br>Masukkan bahan kering sedikit demi sedikit ke dalam campuran bahan basah. Tambahkan susu cair secara bertahap sambil terus diaduk hingga adonan halus dan tidak bergerindil.</p><p><strong>Panggang adonan</strong><br>Tuang adonan ke dalam loyang. Panggang selama ±35–45 menit atau sampai matang (cek dengan tusuk, jika tidak ada adonan yang menempel berarti sudah matang).</p><p><strong>Dinginkan dan sajikan</strong><br>Keluarkan cake dari oven, dinginkan sebentar, lalu keluarkan dari loyang. Cake coklat siap disajikan.</p><h4><strong>Tips agar Cake Tambah Lembut</strong></h4><p>Gunakan suhu oven stabil, jangan terlalu tinggi.</p><p>Jangan mengaduk adonan terlalu lama agar cake tidak bantat.</p><p>Bisa tambahkan cokelat batang leleh untuk rasa lebih rich.</p><h3><strong>Penutup</strong></h3><p>Membuat cake coklat ternyata tidak sesulit yang dibayangkan. Dengan bahan sederhana dan langkah yang mudah, kamu sudah bisa menghasilkan cake coklat lembut dan lezat untuk dinikmati bersama keluarga atau teman. Cocok untuk hidangan spesial ataupun camilan sehari-hari.</p>', '69258ed0c14c9_1764069072.jpg', 1, '2025-11-25 11:11:12', '2025-11-25 11:26:38'),
(4, 'Cara Membuat Cake Keju Super Lembut dan Harum', 'Panduan mudah membuat cake keju yang lembut, wangi, dan creamy dengan bahan yang sederhana dan cocok untuk pemula.', 'Cara Membuat Cake Keju Super Lembut dan Harum\r\n\r\nCake keju selalu menjadi pilihan favorit karena teksturnya yang empuk dan rasa gurih manis yang pas. Kue ini cocok untuk camilan sore, arisan, ulang tahun, ataupun kue jualan. Walau terlihat istimewa, cara membuatnya sangat mudah dan bisa dilakukan di rumah.\r\n\r\nBahan-bahan\r\n\r\n200 gram tepung terigu protein rendah\r\n\r\n4 butir telur\r\n\r\n180 gram gula pasir\r\n\r\n120 gram margarin / mentega, lelehkan\r\n\r\n100 ml susu cair\r\n\r\n80 gram keju cheddar parut\r\n\r\n1 sdt baking powder\r\n\r\n1/2 sdt vanila\r\n\r\nSejumput garam\r\n\r\nAlat yang Dibutuhkan\r\n\r\nMixer\r\n\r\nLoyang\r\n\r\nOven\r\n\r\nWadah & spatula\r\n\r\nCara Membuat\r\n\r\nPanaskan oven pada suhu 170°C dan siapkan loyang dengan olesan mentega dan taburan tepung.\r\n\r\nKocok telur dan gula sampai mengembang, pucat, dan kental.\r\n\r\nMasukkan vanila dan susu cair, kemudian aduk perlahan hingga tercampur.\r\n\r\nAyak tepung, baking powder, dan garam, lalu masukkan ke dalam adonan sedikit demi sedikit.\r\n\r\nTambahkan mentega leleh dan keju parut, aduk pelan dengan teknik aduk balik (folding) agar adonan tetap mengembang.\r\n\r\nTuang adonan ke loyang dan panggang selama 35–45 menit hingga matang (cek dengan tusuk).\r\n\r\nSetelah dingin, keluarkan dari loyang dan taburi keju parut sebagai topping.\r\n\r\nTips Agar Cake Keju Tambah Enak\r\n\r\nGunakan keju cheddar kualitas baik agar aromanya lebih harum.\r\n\r\nJangan mengaduk adonan terlalu lama setelah mencampur tepung.\r\n\r\nUntuk rasa lebih creamy, bisa tambahkan 2 sdm krim keju (cream cheese).\r\n\r\nPenutup\r\n\r\nDengan bahan sederhana dan cara pembuatan yang simpel, cake keju bisa dibuat kapan pun di rumah. Teksturnya lembut, aromanya wangi, dan cocok untuk semua suasana — dari santai di rumah sampai acara keluarga. Selamat mencoba!', '69259234bd779_1764069940.jpg', 1, '2025-11-25 11:25:40', '2025-11-25 11:25:40');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `jumlah` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `nama`, `deskripsi`, `created_at`) VALUES
(1, 'Roti Tawar', 'Roti tawar premium dengan bahan berkualitas tinggi', '2025-11-17 14:00:35'),
(2, 'Pastry & Croissant', 'Pastry lezat dengan rasa butter yang nikmat', '2025-11-17 14:00:35'),
(3, 'Kue Basah', 'Kue basah pilihan dengan berbagai varian rasa', '2025-11-17 14:00:35'),
(4, 'Donut & Kolak', 'Donut variatif dengan topping menarik', '2025-11-17 14:00:35'),
(5, 'Bolu & Spesial', 'Bolu tradisional dan modern dengan resep spesial', '2025-11-17 14:00:35');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `nama`, `email`, `password`, `no_hp`, `alamat`, `foto_profil`, `created_at`, `updated_at`) VALUES
(3, 'dim', 'dim@gmail.com', '$2y$10$9a4/P4.9XBrqwHNV9ZWEjOCPFmILqJnxkd9wNBSvuGYhypyzItNKG', '08123456789', '', '691c309f9bacf_1763455135.jpg', '2025-11-17 14:17:56', '2025-11-18 08:38:55');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `dibaca` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `customer_id`, `order_id`, `judul`, `pesan`, `dibaca`, `created_at`) VALUES
(1, 3, 1, 'Pesanan Berhasil Dibuat', 'Pesanan #BBK-20251117171050-952 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-17 16:10:50'),
(2, 3, 1, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-17 16:16:53'),
(3, 3, 1, 'Pembayaran Diterima', 'Pembayaran Anda telah diverifikasi. Pesanan akan segera dikemas.', 0, '2025-11-17 16:23:18'),
(4, 3, 1, 'Pembayaran Diterima', 'Pembayaran Anda telah diverifikasi. Pesanan akan segera dikemas.', 0, '2025-11-17 16:24:39'),
(5, 3, 1, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Menunggu Verifikasi', 0, '2025-11-17 16:24:57'),
(6, 3, 1, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-17 16:25:07'),
(7, 3, 1, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Pembayaran Diterima', 0, '2025-11-17 16:25:48'),
(8, 3, 1, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-17 16:26:15'),
(9, 3, 1, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Selesai', 0, '2025-11-17 16:29:43'),
(10, 3, 2, 'Pesanan Berhasil Dibuat', 'Pesanan #BBK-20251117174434-624 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-17 16:44:34'),
(11, 3, 2, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-17 16:44:51'),
(12, 3, 2, 'Pembayaran Ditolak', 'Alasan: bukti tidak sesuai', 0, '2025-11-17 16:45:58'),
(13, 3, 3, 'Pesanan Berhasil Dibuat', 'Pesanan #BBK-20251118044235-875 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-18 03:42:35'),
(14, 3, 3, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-18 03:42:57'),
(15, 3, 3, 'Pembayaran Diterima', 'Pembayaran Anda telah diverifikasi. Pesanan akan segera dikemas.', 0, '2025-11-18 03:43:41'),
(16, 3, 3, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Menunggu Bukti Pembayaran', 0, '2025-11-18 03:43:48'),
(17, 3, 3, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Menunggu Verifikasi', 0, '2025-11-18 03:43:51'),
(18, 3, 3, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Pembayaran Diterima', 0, '2025-11-18 03:43:57'),
(19, 3, 3, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-18 03:44:27'),
(20, 3, 3, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Selesai', 0, '2025-11-18 03:44:33'),
(21, 3, 4, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Menunggu Verifikasi', 0, '2025-11-18 06:57:20'),
(22, 3, 4, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Menunggu Bukti Pembayaran', 0, '2025-11-18 06:57:23'),
(23, 3, 4, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Selesai', 0, '2025-11-18 06:57:30'),
(24, 3, 5, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251118080013-2829 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-18 07:00:13'),
(25, 3, 5, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-18 07:07:35'),
(26, 3, 5, 'Pembayaran Diterima', 'Pembayaran Anda telah diverifikasi. Pesanan akan segera dikemas.', 0, '2025-11-18 07:32:11'),
(27, 3, 5, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-18 07:32:18'),
(28, 3, 5, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Selesai', 0, '2025-11-18 07:33:44'),
(29, 3, 6, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251118093322-1332 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-18 08:33:22'),
(30, 3, 6, 'Pesanan Dibatalkan', 'Pesanan Anda berhasil dibatalkan.', 0, '2025-11-18 08:36:52'),
(31, 3, 7, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251118095917-9781 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-18 08:59:17'),
(32, 3, 7, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-18 08:59:25'),
(33, 3, 7, 'Pembayaran Diterima', 'Pembayaran Anda telah diverifikasi. Pesanan akan segera dikemas.', 0, '2025-11-18 10:00:39'),
(34, 3, 7, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Menunggu Verifikasi', 0, '2025-11-18 10:00:51'),
(35, 3, 7, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Pembayaran Ditolak', 0, '2025-11-18 10:00:58'),
(36, 3, 8, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251118110207-7716 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-18 10:02:07'),
(37, 3, 8, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-18 10:02:21'),
(38, 3, 8, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Pembayaran Diterima', 0, '2025-11-18 10:03:52'),
(39, 3, 8, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-18 10:04:23'),
(40, 3, 8, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Selesai', 0, '2025-11-18 10:04:29'),
(41, 3, 9, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251118112723-5958 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-18 10:27:23'),
(42, 3, 9, 'Pesanan Dibatalkan', 'Pesanan Anda berhasil dibatalkan.', 0, '2025-11-18 10:28:13'),
(43, 3, 10, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251118124122-9292 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-18 11:41:22'),
(44, 3, 10, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-18 11:41:33'),
(45, 3, 10, 'Pembayaran Diterima', 'Pembayaran Anda telah diverifikasi. Pesanan akan segera dikemas.', 0, '2025-11-18 11:45:58'),
(46, 3, 10, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Menunggu Verifikasi', 0, '2025-11-18 11:47:27'),
(47, 3, 10, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Pembayaran Diterima', 0, '2025-11-18 11:48:16'),
(48, 3, 10, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-18 11:48:35'),
(49, 3, 10, 'Pesanan Selesai', 'Anda telah mengkonfirmasi penerimaan barang.', 0, '2025-11-18 11:49:21'),
(50, 3, 10, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-18 11:49:38'),
(51, 3, 10, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Selesai', 0, '2025-11-18 11:49:58'),
(52, 3, 10, 'Pesanan Selesai', 'Anda telah mengkonfirmasi penerimaan barang.', 0, '2025-11-18 12:19:44'),
(53, 3, 11, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251118142157-6268 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-18 13:21:57'),
(54, 3, 11, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-18 13:22:06'),
(55, 3, 11, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Selesai', 0, '2025-11-18 13:27:02'),
(56, 3, 12, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251118144052-1987 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-18 13:40:52'),
(57, 3, 12, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-18 13:41:00'),
(58, 3, 12, 'Pembayaran Diterima', 'Pembayaran Anda telah diverifikasi. Pesanan akan segera dikemas.', 0, '2025-11-18 13:41:23'),
(59, 3, 13, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251118144252-3825 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-18 13:42:52'),
(60, 3, 13, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-18 13:43:02'),
(61, 3, 13, 'Pesanan Dibatalkan', 'Pesanan Anda berhasil dibatalkan.', 0, '2025-11-18 13:43:33'),
(66, 3, 15, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251125033825-7070 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-25 02:38:25'),
(67, 3, 15, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-25 02:38:33'),
(68, 3, 15, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Menunggu Bukti Pembayaran', 0, '2025-11-25 02:39:27'),
(69, 3, 15, 'Pesanan Dibatalkan', 'Pesanan Anda berhasil dibatalkan.', 0, '2025-11-25 02:49:12'),
(70, 3, 16, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251125035007-3120 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-25 02:50:07'),
(71, 3, 16, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-25 02:50:21'),
(72, 3, 16, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Pembayaran Diterima', 0, '2025-11-25 02:50:41'),
(73, 3, 16, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-25 02:50:50'),
(74, 3, 16, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Selesai', 0, '2025-11-25 02:51:10'),
(75, 3, 17, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251125055225-7973 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-25 04:52:25'),
(76, 3, 17, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-25 04:52:34'),
(77, 3, 17, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Pembayaran Diterima', 0, '2025-11-25 04:53:07'),
(78, 3, 17, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-25 04:53:28'),
(79, 3, 17, 'Pesanan Selesai', 'Anda telah mengkonfirmasi penerimaan barang.', 0, '2025-11-25 04:53:49'),
(80, 3, 18, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251125055840-4775 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-25 04:58:40'),
(81, 3, 18, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-25 04:58:49'),
(82, 3, 18, 'Pembayaran Diterima', 'Pembayaran Anda telah diverifikasi. Pesanan akan segera dikemas.', 0, '2025-11-25 04:59:03'),
(83, 3, 18, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-25 04:59:11'),
(84, 3, 18, 'Pesanan Selesai', 'Anda telah mengkonfirmasi penerimaan barang.', 0, '2025-11-25 04:59:26'),
(85, 3, 19, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251125061011-6069 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-25 05:10:11'),
(86, 3, 19, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-25 05:10:22'),
(87, 3, 19, 'Pembayaran Diterima', 'Pembayaran Anda telah diverifikasi. Pesanan akan segera dikemas.', 0, '2025-11-25 05:11:17'),
(88, 3, 19, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-25 05:11:21'),
(89, 3, 19, 'Pesanan Selesai', 'Anda telah mengkonfirmasi penerimaan barang.', 0, '2025-11-25 05:11:42'),
(90, 3, 20, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251125080324-4378 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-25 07:03:24'),
(91, 3, 20, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-25 07:03:38'),
(92, 3, 20, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Pembayaran Diterima', 0, '2025-11-25 07:50:02'),
(93, 3, 20, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-25 07:50:05'),
(94, 3, 20, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Selesai', 0, '2025-11-25 07:50:07'),
(95, 3, 21, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251125101741-3258 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-25 09:17:41'),
(96, 3, 21, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-25 09:20:35'),
(97, 3, 21, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Pembayaran Diterima', 0, '2025-11-25 09:21:31'),
(98, 3, 21, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-25 09:22:15'),
(99, 3, 21, 'Pesanan Selesai', 'Anda telah mengkonfirmasi penerimaan barang.', 0, '2025-11-25 09:22:38'),
(100, 3, 22, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251125102634-6471 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-25 09:26:34'),
(101, 3, 22, 'Pesanan Dibatalkan', 'Pesanan Anda berhasil dibatalkan.', 0, '2025-11-25 09:27:14'),
(102, 3, 12, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-26 01:58:00'),
(103, 3, 12, 'Pesanan Selesai', 'Anda telah mengkonfirmasi penerimaan barang.', 0, '2025-11-26 01:59:07'),
(104, 3, 23, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251126093309-8765 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-26 08:33:09'),
(105, 3, 23, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-26 08:36:06'),
(106, 3, 23, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Pembayaran Diterima', 0, '2025-11-26 08:36:52'),
(107, 3, 23, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-26 08:37:15'),
(108, 3, 23, 'Pesanan Selesai', 'Anda telah mengkonfirmasi penerimaan barang.', 0, '2025-11-26 08:37:30'),
(109, 3, 23, 'Pesanan Selesai', 'Anda telah mengkonfirmasi penerimaan barang.', 0, '2025-11-26 08:37:50'),
(110, 3, 24, 'Pesanan Berhasil Dibuat', 'Pesanan #ORD-20251126095344-1470 berhasil dibuat. Silakan upload bukti pembayaran.', 0, '2025-11-26 08:53:44'),
(111, 3, 24, 'Bukti Pembayaran Terkirim', 'Bukti pembayaran Anda telah diterima. Admin akan verifikasi dalam waktu 1x24 jam.', 0, '2025-11-26 08:57:07'),
(112, 3, 24, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Pembayaran Diterima', 0, '2025-11-26 08:57:34'),
(113, 3, 24, 'Status Pesanan Berubah', 'Status pesanan Anda sekarang: Siap Dikirim', 0, '2025-11-26 08:58:39'),
(114, 3, 24, 'Pesanan Selesai', 'Anda telah mengkonfirmasi penerimaan barang.', 0, '2025-11-26 08:58:55');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `no_pesanan` varchar(50) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `ongkir` decimal(10,2) DEFAULT 0.00,
  `total_bayar` decimal(10,2) NOT NULL,
  `wilayah` varchar(100) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `status` enum('menunggu_bukti','menunggu_verifikasi','diterima','ditolak','siap_kirim','selesai') DEFAULT 'menunggu_bukti',
  `alasan_penolakan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `no_pesanan`, `total_harga`, `ongkir`, `total_bayar`, `wilayah`, `alamat_lengkap`, `status`, `alasan_penolakan`, `created_at`, `updated_at`) VALUES
(1, 3, 'BBK-20251117171050-952', 15000.00, 70000.00, 85000.00, 'Bali', 'jl. ketintang', 'selesai', NULL, '2025-11-17 16:10:50', '2025-11-17 16:29:43'),
(2, 3, 'BBK-20251117174434-624', 15000.00, 10000.00, 25000.00, 'dalam kota', 'jl. ketintang', 'ditolak', 'bukti tidak sesuai', '2025-11-17 16:44:34', '2025-11-17 16:45:58'),
(3, 3, 'BBK-20251118044235-875', 36000.00, 10000.00, 46000.00, 'dalam kota', 'karah', 'selesai', NULL, '2025-11-18 03:42:35', '2025-11-18 03:44:33'),
(4, 3, 'ORD-20251118074659-7249', 600000.00, 10000.00, 610000.00, 'dalam kota', 'jl. ketintang', 'selesai', NULL, '2025-11-18 06:46:59', '2025-11-18 06:57:30'),
(5, 3, 'ORD-20251118080013-2829', 15000.00, 10000.00, 25000.00, 'dalam kota', 'jl. ketintang', 'selesai', NULL, '2025-11-18 07:00:13', '2025-11-18 07:33:44'),
(6, 3, 'ORD-20251118093322-1332', 10000.00, 10000.00, 20000.00, 'dalam kota', 'jl. ketintang', 'ditolak', NULL, '2025-11-18 08:33:22', '2025-11-18 08:36:52'),
(7, 3, 'ORD-20251118095917-9781', 15000.00, 10000.00, 25000.00, 'dalam kota', 'jl. ketintang', 'ditolak', NULL, '2025-11-18 08:59:17', '2025-11-18 10:00:58'),
(8, 3, 'ORD-20251118110207-7716', 25000.00, 10000.00, 35000.00, 'dalam kota', 'jemur sari', 'selesai', NULL, '2025-11-18 10:02:07', '2025-11-18 10:04:29'),
(9, 3, 'ORD-20251118112723-5958', 15000.00, 10000.00, 25000.00, 'dalam kota', 'jl. ketintang', 'ditolak', NULL, '2025-11-18 10:27:23', '2025-11-18 10:28:13'),
(10, 3, 'ORD-20251118124122-9292', 45000.00, 10000.00, 55000.00, 'dalam kota', 'jl. ketintang', 'selesai', NULL, '2025-11-18 11:41:22', '2025-11-18 11:49:58'),
(11, 3, 'ORD-20251118142157-6268', 15000.00, 20000.00, 35000.00, 'Luar kota', 'jl. ketintang', 'selesai', NULL, '2025-11-18 13:21:57', '2025-11-18 13:27:02'),
(12, 3, 'ORD-20251118144052-1987', 15000.00, 10000.00, 25000.00, 'dalam kota', 'jl. ketintang', 'selesai', NULL, '2025-11-18 13:40:52', '2025-11-26 01:59:07'),
(13, 3, 'ORD-20251118144252-3825', 15000.00, 10000.00, 25000.00, 'dalam kota', 'j', 'ditolak', NULL, '2025-11-18 13:42:52', '2025-11-18 13:43:33'),
(15, 3, 'ORD-20251125033825-7070', 15000.00, 10000.00, 25000.00, 'dalam kota', 'jl. ketintang', 'ditolak', NULL, '2025-11-25 02:38:25', '2025-11-25 02:49:12'),
(16, 3, 'ORD-20251125035007-3120', 10000.00, 10000.00, 20000.00, 'dalam kota', 'jl. ketintang', 'selesai', NULL, '2025-11-25 02:50:07', '2025-11-25 02:51:10'),
(17, 3, 'ORD-20251125055225-7973', 10000.00, 12000.00, 22000.00, 'Asemrowo', 'tes', 'selesai', NULL, '2025-11-25 04:52:25', '2025-11-25 04:53:49'),
(18, 3, 'ORD-20251125055840-4775', 15000.00, 15000.00, 30000.00, 'benowo', 'jl. ketintang', 'selesai', NULL, '2025-11-25 04:58:40', '2025-11-25 04:59:26'),
(19, 3, 'ORD-20251125061011-6069', 15000.00, 12000.00, 27000.00, 'Asemrowo', 'tes', 'selesai', NULL, '2025-11-25 05:10:11', '2025-11-25 05:11:42'),
(20, 3, 'ORD-20251125080324-4378', 15000.00, 12000.00, 27000.00, 'Asemrowo', 'jl. ketintang', 'selesai', NULL, '2025-11-25 07:03:24', '2025-11-25 07:50:07'),
(21, 3, 'ORD-20251125101741-3258', 18000.00, 10000.00, 28000.00, 'Dukuh Pakis', 'jl. ketintang', 'selesai', NULL, '2025-11-25 09:17:41', '2025-11-25 09:22:38'),
(22, 3, 'ORD-20251125102634-6471', 15000.00, 10000.00, 25000.00, 'Dukuh Pakis', 'jl. ketintang', 'ditolak', NULL, '2025-11-25 09:26:34', '2025-11-25 09:27:14'),
(23, 3, 'ORD-20251126093309-8765', 13000.00, 12000.00, 25000.00, 'Asemrowo', 'jl. ketintang', 'selesai', NULL, '2025-11-26 08:33:09', '2025-11-26 08:37:30'),
(24, 3, 'ORD-20251126095344-1470', 10000.00, 12000.00, 22000.00, 'Gubeng', 'jl. ketintang', 'selesai', NULL, '2025-11-26 08:53:44', '2025-11-26 08:58:55');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `jumlah`, `harga`, `created_at`) VALUES
(1, 1, 1, 1, 15000.00, '2025-11-17 16:10:50'),
(2, 2, 1, 1, 15000.00, '2025-11-17 16:44:34'),
(3, 3, 2, 2, 18000.00, '2025-11-18 03:42:35'),
(4, 4, 1, 40, 15000.00, '2025-11-18 06:46:59'),
(5, 5, 1, 1, 15000.00, '2025-11-18 07:00:13'),
(6, 6, 17, 1, 10000.00, '2025-11-18 08:33:22'),
(7, 7, 1, 1, 15000.00, '2025-11-18 08:59:17'),
(8, 8, 1, 1, 15000.00, '2025-11-18 10:02:07'),
(9, 8, 17, 1, 10000.00, '2025-11-18 10:02:07'),
(10, 9, 1, 1, 15000.00, '2025-11-18 10:27:23'),
(11, 10, 1, 3, 15000.00, '2025-11-18 11:41:22'),
(12, 11, 1, 1, 15000.00, '2025-11-18 13:21:57'),
(13, 12, 1, 1, 15000.00, '2025-11-18 13:40:52'),
(14, 13, 1, 1, 15000.00, '2025-11-18 13:42:52'),
(16, 15, 1, 1, 15000.00, '2025-11-25 02:38:25'),
(17, 16, 17, 1, 10000.00, '2025-11-25 02:50:07'),
(18, 17, 17, 1, 10000.00, '2025-11-25 04:52:25'),
(19, 18, 1, 1, 15000.00, '2025-11-25 04:58:40'),
(20, 19, 1, 1, 15000.00, '2025-11-25 05:10:11'),
(21, 20, 1, 1, 15000.00, '2025-11-25 07:03:24'),
(22, 21, 2, 1, 18000.00, '2025-11-25 09:17:41'),
(23, 22, 1, 1, 15000.00, '2025-11-25 09:26:34'),
(24, 23, 1, 1, 15000.00, '2025-11-26 08:33:09'),
(25, 24, 17, 1, 10000.00, '2025-11-26 08:53:44');

-- --------------------------------------------------------

--
-- Table structure for table `payment_proofs`
--

CREATE TABLE `payment_proofs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `bukti_file` varchar(255) NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_proofs`
--

INSERT INTO `payment_proofs` (`id`, `order_id`, `bukti_file`, `verified_at`, `verified_by`, `created_at`) VALUES
(1, 1, '691b4c6361e0c.jpg', '2025-11-17 16:24:39', 1, '2025-11-17 16:16:53'),
(2, 2, '691b5103875e6.png', NULL, NULL, '2025-11-17 16:44:51'),
(3, 3, '691beb41c3679.png', '2025-11-18 03:43:41', 1, '2025-11-18 03:42:57'),
(4, 5, '691c1b371683a_1763449655.jpg', '2025-11-18 07:32:11', 1, '2025-11-18 07:07:35'),
(5, 7, '691c356de0d45_1763456365.jpg', '2025-11-18 10:00:39', 1, '2025-11-18 08:59:25'),
(6, 8, '691c442dac790_1763460141.jpg', NULL, NULL, '2025-11-18 10:02:21'),
(7, 10, '691c5b6de2500_1763466093.jpg', '2025-11-18 11:45:58', 1, '2025-11-18 11:41:33'),
(8, 11, '691c72fe92546_1763472126.jpg', NULL, NULL, '2025-11-18 13:22:06'),
(9, 12, '691c776c9a4a3_1763473260.png', '2025-11-18 13:41:23', 1, '2025-11-18 13:41:00'),
(10, 13, '691c77e6c7830_1763473382.jpg', NULL, NULL, '2025-11-18 13:43:02'),
(12, 15, '692516a968604_1764038313.jpg', NULL, NULL, '2025-11-25 02:38:33'),
(13, 16, '6925196d796b2_1764039021.jpg', NULL, NULL, '2025-11-25 02:50:21'),
(14, 17, '692536121004f_1764046354.jpg', NULL, NULL, '2025-11-25 04:52:34'),
(15, 18, '69253789d2d55_1764046729.jpg', '2025-11-25 04:59:03', 1, '2025-11-25 04:58:49'),
(16, 19, '69253a3e546de_1764047422.jpg', '2025-11-25 05:11:17', 1, '2025-11-25 05:10:22'),
(17, 20, '692554ca59487_1764054218.jpg', NULL, NULL, '2025-11-25 07:03:38'),
(18, 21, '692574e376334_1764062435.jpg', NULL, NULL, '2025-11-25 09:20:35'),
(19, 23, '6926bbf68a7ee_1764146166.jpg', NULL, NULL, '2025-11-26 08:36:06'),
(20, 24, '6926c0e34b1bd_1764147427.jpg', NULL, NULL, '2025-11-26 08:57:07');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `diskon_tipe` enum('persentase','nominal') DEFAULT NULL,
  `diskon_nilai` decimal(10,2) DEFAULT NULL,
  `diskon_aktif` tinyint(1) DEFAULT 0,
  `stok` int(11) DEFAULT 0,
  `foto_utama` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `nama`, `deskripsi`, `harga`, `diskon_tipe`, `diskon_nilai`, `diskon_aktif`, `stok`, `foto_utama`, `created_at`, `updated_at`) VALUES
(1, 1, 'Roti Tawar Putih', 'Roti tawar putih klasik, lembut dan empuk', 15000.00, 'nominal', 2000.00, 1, 37, '691c06a0b5b4c.jpg', '2025-11-17 14:00:35', '2025-11-26 08:33:09'),
(2, 1, 'Roti Tawar Coklat', 'Roti tawar coklat dengan aroma coklat yang wangi', 18000.00, NULL, NULL, 0, 42, '691c2035ba3c1_1763450933.jpg', '2025-11-17 14:00:35', '2025-11-25 09:17:41'),
(17, 1, 'Roti Tawar manis', 'roti tawar manis dan lezat', 10000.00, 'persentase', 10.00, 0, 35, '692584825d39a_1764066434.jpg', '2025-11-18 06:39:40', '2025-11-26 08:53:44');

-- --------------------------------------------------------

--
-- Table structure for table `product_photos`
--

CREATE TABLE `product_photos` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `ulasan` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `customer_id`, `order_id`, `rating`, `ulasan`, `created_at`) VALUES
(1, 17, 3, 8, 4, 'rasanya enak', '2025-11-25 04:55:54'),
(2, 1, 3, 1, 5, 'produk sangat unggul', '2025-11-25 04:59:50'),
(3, 2, 3, 3, 5, '', '2025-11-25 09:23:43'),
(4, 1, 3, 23, 5, 'produk berkualitas', '2025-11-26 08:43:22'),
(5, 2, 3, 21, 5, 'oke', '2025-11-26 08:52:02'),
(6, 17, 3, 24, 5, '', '2025-11-26 08:59:03');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_costs`
--

CREATE TABLE `shipping_costs` (
  `id` int(11) NOT NULL,
  `wilayah` varchar(100) NOT NULL,
  `ongkir` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_costs`
--

INSERT INTO `shipping_costs` (`id`, `wilayah`, `ongkir`, `created_at`, `updated_at`) VALUES
(18, 'Gayungan', 10000.00, '2025-11-25 02:56:47', '2025-11-25 02:56:47'),
(19, 'Jambangan', 10000.00, '2025-11-25 02:57:10', '2025-11-25 02:57:10'),
(20, 'Wonokromo', 10000.00, '2025-11-25 02:57:22', '2025-11-25 02:57:22'),
(21, 'Karang Pilang', 10000.00, '2025-11-25 02:57:35', '2025-11-25 02:57:35'),
(22, 'Dukuh Pakis', 10000.00, '2025-11-25 02:57:47', '2025-11-25 02:57:47'),
(23, 'Wiyung', 10000.00, '2025-11-25 02:58:00', '2025-11-25 02:58:00'),
(24, 'Tenggilis Mejoyo', 12000.00, '2025-11-25 02:58:16', '2025-11-25 02:58:16'),
(25, 'Rungkut', 12000.00, '2025-11-25 02:58:30', '2025-11-25 02:58:30'),
(26, 'Gunung Anyar', 12000.00, '2025-11-25 02:58:42', '2025-11-25 02:58:42'),
(27, 'Sukolilo', 12000.00, '2025-11-25 02:59:05', '2025-11-25 02:59:05'),
(28, 'Sukomanunggal', 12000.00, '2025-11-25 02:59:23', '2025-11-25 02:59:23'),
(29, 'Tandes', 12000.00, '2025-11-25 02:59:39', '2025-11-25 02:59:39'),
(30, 'Asemrowo', 12000.00, '2025-11-25 03:00:15', '2025-11-25 03:00:15'),
(31, 'Genteng', 12000.00, '2025-11-25 03:00:39', '2025-11-25 03:00:39'),
(32, 'Gubeng', 12000.00, '2025-11-25 03:00:58', '2025-11-25 03:00:58'),
(33, 'Tegalsari', 12000.00, '2025-11-25 03:01:13', '2025-11-25 03:01:13'),
(34, 'Simokerto', 12000.00, '2025-11-25 03:01:30', '2025-11-25 03:01:30'),
(35, 'Tambaksari', 12000.00, '2025-11-25 03:01:46', '2025-11-25 03:01:46'),
(36, 'Mulyorejo', 15000.00, '2025-11-25 03:02:02', '2025-11-25 03:02:40'),
(37, 'kenjeran', 15000.00, '2025-11-25 03:03:06', '2025-11-25 03:03:06'),
(38, 'semampir', 15000.00, '2025-11-25 03:03:28', '2025-11-25 03:03:28'),
(39, 'pabean', 15000.00, '2025-11-25 03:03:49', '2025-11-25 03:03:49'),
(40, 'krembangan', 15000.00, '2025-11-25 03:04:03', '2025-11-25 03:04:03'),
(41, 'benowo', 15000.00, '2025-11-25 03:04:16', '2025-11-25 03:04:16'),
(42, 'pakal', 15000.00, '2025-11-25 03:04:36', '2025-11-25 03:04:36'),
(43, 'lakarsantri', 15000.00, '2025-11-25 03:04:51', '2025-11-25 03:04:51'),
(44, 'sambikerep', 15000.00, '2025-11-25 03:05:07', '2025-11-25 03:05:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_carts_customer` (`customer_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_notifications_customer` (`customer_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_pesanan` (`no_pesanan`),
  ADD KEY `idx_orders_customer` (`customer_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order_items_order` (`order_id`);

--
-- Indexes for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category` (`category_id`);

--
-- Indexes for table `product_photos`
--
ALTER TABLE `product_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_reviews_product` (`product_id`);

--
-- Indexes for table `shipping_costs`
--
ALTER TABLE `shipping_costs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wilayah` (`wilayah`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `product_photos`
--
ALTER TABLE `product_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `shipping_costs`
--
ALTER TABLE `shipping_costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD CONSTRAINT `payment_proofs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_proofs_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_photos`
--
ALTER TABLE `product_photos`
  ADD CONSTRAINT `product_photos_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
