-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Jul 2025 pada 02.55
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lapangan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking`
--

CREATE TABLE `booking` (
  `id_booking` int(11) NOT NULL,
  `id_penyewa` int(11) DEFAULT NULL,
  `id_lapangan` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `order_id` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `booking`
--

INSERT INTO `booking` (`id_booking`, `id_penyewa`, `id_lapangan`, `tanggal`, `jam_mulai`, `jam_selesai`, `status`, `order_id`) VALUES
(21, 6, 1, '1970-01-01', '09:00:00', '10:00:00', 'pending', 'FC-1751992161-9734'),
(22, 6, 1, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1751992161-9734'),
(23, 6, 1, '1970-01-01', '09:00:00', '10:00:00', 'pending', 'FC-1751992161-3133'),
(24, 6, 1, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1751992161-3133'),
(25, 6, 1, '1970-01-01', '09:00:00', '10:00:00', 'pending', 'FC-1751992161-1885'),
(26, 6, 1, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1751992161-1885'),
(27, 6, 1, '1970-01-01', '09:00:00', '10:00:00', 'pending', 'FC-1751992161-9474'),
(28, 6, 1, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1751992161-9474'),
(29, 6, 2, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1751992234-7650'),
(30, 6, 2, '1970-01-01', '11:00:00', '12:00:00', 'pending', 'FC-1751992234-7650'),
(31, 6, 2, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1751992234-6596'),
(32, 6, 2, '1970-01-01', '11:00:00', '12:00:00', 'pending', 'FC-1751992234-6596'),
(33, 6, 2, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1751992234-3753'),
(34, 6, 2, '1970-01-01', '11:00:00', '12:00:00', 'pending', 'FC-1751992234-3753'),
(35, 6, 2, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1751992234-7740'),
(36, 6, 2, '1970-01-01', '11:00:00', '12:00:00', 'pending', 'FC-1751992234-7740'),
(37, 6, 4, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1751992382-5738'),
(38, 6, 4, '1970-01-01', '11:00:00', '12:00:00', 'pending', 'FC-1751992382-5738'),
(39, 6, 4, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1751992382-7083'),
(40, 6, 4, '1970-01-01', '11:00:00', '12:00:00', 'pending', 'FC-1751992382-7083'),
(41, 6, 4, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1751992383-8228'),
(42, 6, 4, '1970-01-01', '11:00:00', '12:00:00', 'pending', 'FC-1751992383-8228'),
(43, 6, 4, '1970-01-01', '10:00:00', '11:00:00', 'settlement', 'FC-1751992383-3398'),
(44, 6, 4, '1970-01-01', '11:00:00', '12:00:00', 'settlement', 'FC-1751992383-3398'),
(45, 1, 1, '2025-07-15', '08:00:00', '09:00:00', 'pending', 'FC-1751994686-7692'),
(46, 1, 1, '2025-07-15', '09:00:00', '10:00:00', 'pending', 'FC-1751994686-7692'),
(47, 1, 1, '2025-07-15', '08:00:00', '09:00:00', 'pending', 'FC-1751994687-5223'),
(48, 1, 1, '2025-07-15', '09:00:00', '10:00:00', 'pending', 'FC-1751994687-5223'),
(49, 1, 1, '2025-07-15', '08:00:00', '09:00:00', 'pending', 'FC-1751994687-4344'),
(50, 1, 1, '2025-07-15', '09:00:00', '10:00:00', 'pending', 'FC-1751994687-4344'),
(51, 1, 1, '2025-07-15', '08:00:00', '09:00:00', 'expire', 'FC-1751994687-8260'),
(52, 1, 1, '2025-07-15', '09:00:00', '10:00:00', 'expire', 'FC-1751994687-8260'),
(61, 1, 1, '2025-07-14', '08:00:00', '09:00:00', 'pending', 'FC-1751994728-9341'),
(62, 1, 1, '2025-07-14', '08:00:00', '09:00:00', 'pending', 'FC-1751994728-2534'),
(63, 1, 1, '2025-07-14', '08:00:00', '09:00:00', 'pending', 'FC-1751994729-7169'),
(64, 1, 1, '2025-07-14', '08:00:00', '09:00:00', 'settlement', 'FC-1751994729-5101'),
(65, 9, 4, '2025-07-09', '08:00:00', '09:00:00', 'pending', 'FC-1751995064-8262'),
(66, 9, 4, '2025-07-09', '09:00:00', '10:00:00', 'pending', 'FC-1751995064-8262'),
(67, 9, 4, '2025-07-09', '08:00:00', '09:00:00', 'pending', 'FC-1751995065-1151'),
(68, 9, 4, '2025-07-09', '09:00:00', '10:00:00', 'pending', 'FC-1751995065-1151'),
(69, 9, 4, '2025-07-09', '08:00:00', '09:00:00', 'pending', 'FC-1751995065-4268'),
(70, 9, 4, '2025-07-09', '09:00:00', '10:00:00', 'pending', 'FC-1751995065-4268'),
(71, 9, 4, '2025-07-09', '08:00:00', '09:00:00', 'settlement', 'FC-1751995065-3404'),
(72, 9, 4, '2025-07-09', '09:00:00', '10:00:00', 'settlement', 'FC-1751995065-3404'),
(73, 6, 2, '2025-07-09', '08:00:00', '09:00:00', 'pending', 'FC-1752021950-8625'),
(74, 6, 2, '2025-07-09', '09:00:00', '10:00:00', 'pending', 'FC-1752021950-8625'),
(75, 6, 2, '2025-07-09', '08:00:00', '09:00:00', 'pending', 'FC-1752021951-9500'),
(76, 6, 2, '2025-07-09', '09:00:00', '10:00:00', 'pending', 'FC-1752021951-9500'),
(77, 6, 2, '2025-07-09', '08:00:00', '09:00:00', 'pending', 'FC-1752021951-4570'),
(78, 6, 2, '2025-07-09', '09:00:00', '10:00:00', 'pending', 'FC-1752021951-4570'),
(79, 6, 2, '2025-07-09', '08:00:00', '09:00:00', 'settlement', 'FC-1752021951-4933'),
(80, 6, 2, '2025-07-09', '09:00:00', '10:00:00', 'settlement', 'FC-1752021951-4933'),
(81, 10, 4, '2025-07-15', '09:00:00', '10:00:00', 'pending', 'FC-1752665334-7400'),
(82, 10, 4, '2025-07-15', '10:00:00', '11:00:00', 'pending', 'FC-1752665334-7400'),
(83, 10, 4, '2025-07-15', '09:00:00', '10:00:00', 'pending', 'FC-1752665334-2310'),
(84, 10, 4, '2025-07-15', '10:00:00', '11:00:00', 'pending', 'FC-1752665334-2310'),
(85, 10, 4, '2025-07-15', '09:00:00', '10:00:00', 'pending', 'FC-1752665334-8456'),
(86, 10, 4, '2025-07-15', '10:00:00', '11:00:00', 'pending', 'FC-1752665334-8456'),
(87, 10, 4, '2025-07-15', '09:00:00', '10:00:00', 'settlement', 'FC-1752665334-4713'),
(88, 10, 4, '2025-07-15', '10:00:00', '11:00:00', 'settlement', 'FC-1752665334-4713'),
(89, 10, 1, '1970-01-01', '08:00:00', '09:00:00', 'pending', 'FC-1752665902-1442'),
(90, 10, 1, '1970-01-01', '08:00:00', '09:00:00', 'pending', 'FC-1752665902-7305'),
(91, 10, 1, '1970-01-01', '08:00:00', '09:00:00', 'pending', 'FC-1752665902-3058'),
(92, 10, 1, '1970-01-01', '08:00:00', '09:00:00', 'settlement', 'FC-1752665902-1512'),
(93, 10, 16, '1970-01-01', '09:00:00', '10:00:00', 'pending', 'FC-1752666865-5487'),
(94, 10, 16, '1970-01-01', '09:00:00', '10:00:00', 'pending', 'FC-1752666865-4631'),
(95, 10, 16, '1970-01-01', '09:00:00', '10:00:00', 'pending', 'FC-1752666867-4079'),
(96, 10, 16, '1970-01-01', '09:00:00', '10:00:00', 'settlement', 'FC-1752666867-2083'),
(97, 10, 18, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1752666938-9801'),
(98, 10, 18, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1752666938-8982'),
(99, 10, 18, '1970-01-01', '10:00:00', '11:00:00', 'pending', 'FC-1752666939-4211'),
(100, 10, 18, '1970-01-01', '10:00:00', '11:00:00', 'settlement', 'FC-1752666939-9981'),
(101, 10, 16, '2025-07-17', '11:00:00', '12:00:00', 'pending', 'FC-1752774168-8238'),
(102, 10, 16, '2025-07-17', '11:00:00', '12:00:00', 'pending', 'FC-1752774168-3998'),
(103, 10, 16, '2025-07-17', '11:00:00', '12:00:00', 'pending', 'FC-1752774169-7509'),
(104, 10, 16, '2025-07-17', '11:00:00', '12:00:00', 'settlement', 'FC-1752774169-6260'),
(105, 10, 16, '2025-07-17', '11:00:00', '12:00:00', 'pending', 'FC-1752778457-8218'),
(106, 10, 16, '2025-07-17', '11:00:00', '12:00:00', 'pending', 'FC-1752778457-5184'),
(107, 10, 16, '2025-07-17', '11:00:00', '12:00:00', 'pending', 'FC-1752778458-9679'),
(108, 10, 16, '2025-07-17', '11:00:00', '12:00:00', 'settlement', 'FC-1752778458-6915'),
(109, 10, 16, '2025-07-18', '21:00:00', '22:00:00', 'settlement', 'FC-1752781321-4408'),
(110, 10, 16, '2025-07-18', '19:00:00', '20:00:00', 'settlement', 'FC-1752782477-5200'),
(111, 10, 16, '2025-07-18', '18:00:00', '19:00:00', 'settlement', 'FC-1752783151-7422'),
(112, 10, 16, '2025-07-18', '17:00:00', '18:00:00', 'settlement', 'FC-1752783588-5585'),
(113, 10, 16, '2025-07-18', '18:00:00', '19:00:00', 'pending', 'FC-1752788760-3996'),
(114, 9, 17, '2025-07-31', '18:00:00', '19:00:00', 'pending', 'FC-1752789227-1487'),
(115, 9, 17, '2025-07-31', '19:00:00', '20:00:00', 'pending', 'FC-1752789227-1487'),
(116, 9, 17, '2025-07-31', '20:00:00', '21:00:00', 'pending', 'FC-1752789227-1487'),
(117, 9, 17, '2025-07-31', '21:00:00', '22:00:00', 'pending', 'FC-1752789227-1487'),
(118, 10, 17, '2025-07-17', '20:00:00', '21:00:00', 'pending', 'FC-1752789348-6277'),
(119, 10, 17, '2025-07-17', '21:00:00', '22:00:00', 'pending', 'FC-1752789348-6277'),
(120, 10, 16, '2025-07-30', '20:00:00', '21:00:00', 'pending', 'FC-1752789497-8210'),
(121, 10, 16, '2025-07-30', '21:00:00', '22:00:00', 'pending', 'FC-1752789497-8210');

-- --------------------------------------------------------

--
-- Struktur dari tabel `lap`
--

CREATE TABLE `lap` (
  `id_lapangan` int(11) NOT NULL,
  `nama_lapangan` varchar(100) DEFAULT NULL,
  `harga_jam` int(11) NOT NULL,
  `fasilitas` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `lap`
--

INSERT INTO `lap` (`id_lapangan`, `nama_lapangan`, `harga_jam`, `fasilitas`, `gambar`, `deleted_at`) VALUES
(1, 'Lapangan 1', 40000, 'Rumput Sintetis', '68778ca8dd214_BERGARANSI 10 TAHUN, 0813–1888–3437 Biaya Pembuatan Lantai Interlock Flooring KFI Sport.jpg', '2025-07-06 18:45:22'),
(2, 'Lapangan 2', 35000, 'Rumput Sintetis', '686ab360d367f_Analisa-Bisnis-Lapangan-Futsal.jpg', '2025-07-06 18:45:38'),
(4, 'Lapangan 3', 35000, 'Rumput Sintetis', '68778cb721b43_Ga Jadi Sedih Kaya Orang-orang Soalnya Lagi Banyak Proyek.jpg', '2025-07-06 18:45:42'),
(6, 'Lapangan 1', 40000, 'rumput sintetis', NULL, '2025-07-06 18:47:58'),
(7, 'Lapangan 1', 40000, 'rumput sintetis', NULL, '2025-07-06 18:44:29'),
(8, 'Lapangan 1', 40000, 'rumput sintetis', NULL, '2025-07-06 18:48:39'),
(9, 'Lapangan 1', 40000, 'rumput sintetis', NULL, '2025-07-06 18:48:53'),
(10, 'Lapangan 1', 40000, 'rumput sintetis', NULL, '2025-07-06 18:51:20'),
(11, 'Lapangan 1', 40000, 'rumput sintetis', NULL, '2025-07-06 18:51:48'),
(12, 'Lapangan 1', 40000, 'rumput sintetis', NULL, '2025-07-06 18:53:57'),
(13, 'Lapangan 1', 40000, 'rumput sintetis', NULL, '2025-07-06 18:56:41'),
(14, 'Lapangan 1', 40000, 'rumput sintetis', NULL, '2025-07-06 19:05:17'),
(15, 'Lapangan 1', 40000, 'rumput sintetis', NULL, '2025-07-06 19:05:14'),
(16, 'Lapangan 1', 32000, 'Rumput Sintetis', '6877928479425_Ga Jadi Sedih Kaya Orang-orang Soalnya Lagi Banyak Proyek.jpg', NULL),
(17, 'Lapangan 2', 40000, 'Rumput Sintetis', '687792a0757c0_BERGARANSI 10 TAHUN, 0813–1888–3437 Biaya Pembuatan Lantai Interlock Flooring KFI Sport.jpg', NULL),
(18, 'Lapangan 3', 30000, 'Rumput Sintetis', '687792d419992_686ab360d367f_Analisa-Bisnis-Lapangan-Futsal.jpg', NULL),
(19, 'Lapangan 3', 30000, 'Rumput Sintetis', '6877939980844_686ab360d367f_Analisa-Bisnis-Lapangan-Futsal.jpg', '2025-07-16 18:57:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penyewa`
--

CREATE TABLE `penyewa` (
  `id_penyewa` int(100) NOT NULL,
  `nama_penyewa` varchar(100) NOT NULL,
  `email_penyewa` varchar(100) NOT NULL,
  `no_telp` int(50) NOT NULL,
  `password_penyewa` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penyewa`
--

INSERT INTO `penyewa` (`id_penyewa`, `nama_penyewa`, `email_penyewa`, `no_telp`, `password_penyewa`) VALUES
(1, 'naufal dzakwan', 'nopal@gmail.com', 123456789, 'nopal123'),
(5, 'admin', 'admin@admin', 0, 'admin123'),
(6, 'Ncim', 'Ncim@gmail.com', 2147483647, 'Ncim1234'),
(7, 'rap', 'rap@gmail.com', 2147483647, 'rap1234567'),
(8, 'alep', 'alep@gmail.com', 2147483647, 'alep1231'),
(9, 'asraf fahruddin', 'arap@gmail.com', 2147483647, 'tamvangaming123'),
(10, 'Akim', 'akim@gmail.com', 2147483647, 'akim1234');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id_booking`),
  ADD KEY `id_penyewa` (`id_penyewa`),
  ADD KEY `id_lapangan` (`id_lapangan`);

--
-- Indeks untuk tabel `lap`
--
ALTER TABLE `lap`
  ADD PRIMARY KEY (`id_lapangan`);

--
-- Indeks untuk tabel `penyewa`
--
ALTER TABLE `penyewa`
  ADD PRIMARY KEY (`id_penyewa`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `booking`
--
ALTER TABLE `booking`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT untuk tabel `lap`
--
ALTER TABLE `lap`
  MODIFY `id_lapangan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `penyewa`
--
ALTER TABLE `penyewa`
  MODIFY `id_penyewa` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`id_penyewa`) REFERENCES `penyewa` (`id_penyewa`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`id_lapangan`) REFERENCES `lap` (`id_lapangan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
