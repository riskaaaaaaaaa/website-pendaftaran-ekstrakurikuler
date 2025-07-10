-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 10, 2025 at 07:29 PM
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
-- Database: `ekstrakurikuler`
--

-- --------------------------------------------------------

--
-- Table structure for table `ekskul`
--

CREATE TABLE `ekskul` (
  `id_ekskul` int(11) NOT NULL,
  `nama_ekskul` varchar(100) DEFAULT NULL,
  `pembina` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `kuota` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ekskul`
--

INSERT INTO `ekskul` (`id_ekskul`, `nama_ekskul`, `pembina`, `deskripsi`, `kuota`) VALUES
(1, 'English Club', 'Surya Budi', 'English Club adalah kegiatan ekstrakurikuler yang dirancang untuk meningkatkan kemampuan siswa dalam berbahasa Inggris secara aktif dan komunikatif. Kegiatan ini meliputi diskusi, storytelling, debate, speech, dan games edukatif yang menyenangkan. English Club juga mempersiapkan siswa untuk mengikuti lomba berbahasa Inggris tingkat lokal hingga nasional.', 15),
(2, 'bahasa indonesia', 'dhella', 'Ekstrakurikuler Bahasa Indonesia adalah wadah bagi siswa untuk mengembangkan minat, bakat, dan kemampuan dalam bidang kebahasaan dan kesastraan Indonesia. Kegiatan ini bertujuan untuk meningkatkan keterampilan berbahasa baik secara lisan maupun tulisan, serta menumbuhkan apresiasi terhadap karya sastra Indonesia.\r\nKegiatan Rutin:\r\n-Latihan menulis karya sastra (puisi, cerpen, artikel)\r\n-Lomba pidato dan debat Bahasa Indonesia\r\n-Diskusi dan bedah buku\r\n-Pelatihan pembacaan puisi dan drama\r\n-Persiapan lomba literasi tingkat sekolah hingga nasional', 10),
(8, 'Pencak Silat', 'bayu', 'Ekstrakurikuler Pencak Silat bertujuan mengembangkan keterampilan bela diri tradisional Indonesia sambil menanamkan nilai-nilai sportivitas, disiplin, dan pengendalian diri. Dalam kegiatan ini, siswa dilatih teknik dasar pencak silat, jurus, sparring, dan juga latihan fisik untuk meningkatkan stamina dan ketahanan tubuh.\r\n\r\n', 7),
(9, 'Pramuka', 'Beni', 'Pramuka (Praja Muda Karana) merupakan kegiatan ekstrakurikuler yang bertujuan membentuk karakter siswa agar mandiri, disiplin, bertanggung jawab, serta memiliki semangat gotong royong dan cinta tanah air. Kegiatan ini dilaksanakan melalui metode kepramukaan yang meliputi permainan, latihan baris-berbaris, penjelajahan, kemah, hingga kegiatan sosial.', 15),
(10, 'Karya Ilmiah Remaja (KIR)', 'Yusniaini', 'KIR adalah wadah bagi siswa yang tertarik di bidang penelitian dan penulisan ilmiah. Melalui kegiatan ini, siswa dilatih cara merancang penelitian, melakukan observasi, eksperimen, dan menyusun laporan ilmiah. Kegiatan ini juga menjadi persiapan untuk mengikuti lomba karya tulis ilmiah (LKTIN) dan kompetisi sains lainnya.', 18),
(11, 'Fotografi dan Desain Grafis', 'Arya Khoiri', 'Ekstrakurikuler ini mengajarkan keterampilan dasar fotografi dan desain grafis digital. Siswa akan belajar teknik pengambilan gambar, pengeditan menggunakan software (seperti Photoshop/Canva), serta pembuatan konten visual seperti poster, brosur, dan media sosial. Cocok bagi siswa yang tertarik pada dunia kreatif dan multimedia.', 10);

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id_pendaftaran` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `id_ekskul` int(11) DEFAULT NULL,
  `tanggal_daftar` date DEFAULT NULL,
  `status_pendaftaran` enum('pending','diterima','ditolak') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id_pendaftaran`, `id_siswa`, `id_ekskul`, `tanggal_daftar`, `status_pendaftaran`) VALUES
(1, 1, 2, '2025-06-21', 'pending'),
(36, 4, 1, '2025-06-21', 'diterima'),
(38, 4, 8, '2025-06-21', 'diterima'),
(39, 5, 1, '2025-06-27', 'diterima'),
(43, 5, 8, '2025-06-27', 'pending'),
(45, 4, 2, '2025-07-10', 'ditolak'),
(46, 6, 9, '2025-07-10', 'diterima'),
(48, 4, 9, '2025-07-10', 'pending'),
(49, 4, 11, '2025-07-10', 'diterima');

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `isi` text DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `tanggal`) VALUES
(6, ' PENGUMUMAN PENTING!', 'Batas waktu pendaftaran ekstrakurikuler tinggal 2 hari lagi!\r\n\r\n🗓 Pendaftaran ditutup pada: Jumat, 12 Juli 2025\r\n⏰ Pukul: 15.00 WIB\r\n📍 Silakan segera daftar ke ruang OSIS atau melalui link pendaftaran.\r\n\r\nBagi siswa yang belum mendaftar, segera tentukan pilihan dan bergabung di ekskul yang kamu minati!\r\nTerima kasih.', '2025-06-21 00:00:00'),
(14, 'PENDAFTARAN EKSKUL BAHASA INDONESIA DIBUKA!', 'Bagi kalian yang suka menulis cerpen, puisi, atau ingin jago pidato dan drama, yuk gabung ke Ekstrakurikuler Bahasa Indonesia!\r\n\r\n🎯 Kegiatan:\r\n🖋️ Menulis puisi & cerpen\r\n🎭 Drama & pembacaan puisi\r\n🎤 Pelatihan pidato dan lomba\r\n\r\n🗓 Pendaftaran: 1 – 12 Juli 2025', '2025-06-21 08:34:52'),
(16, 'PENGUMUMAN PENDAFTARAN EKSTRAKURIKULER', 'Diberitahukan kepada seluruh siswa kelas VII, VIII, dan IX bahwa pendaftaran kegiatan ekstrakurikuler tahun pelajaran 2025/2026 telah dibuka.\r\nAdapun jenis ekstrakurikuler yang tersedia antara lain:Pramuka,English Club,Bahasa Indonesia,Pencak Silat,Karya Ilmiah Remaja (KIR),Fotografi & Desain Grafis,Basket,Musik,dll.\r\n Waktu Pendaftaran:\r\n1 – 12 Juli 2025\r\n📍 Tempat: Ruang OSIS atau melalui link pendaftaran online (akan dibagikan oleh wali kelas)\r\n\r\nNote: Setiap siswa WAJIB mengikuti minimal 1 (satu) kegiatan ekstrakurikuler.', '2025-07-04 08:40:36');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `id_user`, `nama`, `nisn`, `kelas`, `jenis_kelamin`, `alamat`) VALUES
(1, 18, 'indah', '45245245', '12', '', 'Jalan sukamaju\r\n'),
(2, 20, 'riska', NULL, '12', NULL, NULL),
(4, 22, 'Alya Tuti', '134444444444', '10', '', 'jalan pengangsaan timur\r\n'),
(5, 24, 'satya', '77777777777', '11', '', ''),
(6, 25, 'ayuu', '', '10', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('admin','user') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(18, 'aaa', 'a01610228fe998f515a72dd730294d87', 'user'),
(20, 'aa', '$2y$10$TCyjuziu8gL76b8GqQqtXuJpTozeRt7EpcbFwxaR4llX.Kz9AXe.K', 'user'),
(22, 'alya', '$2y$10$KVE7Dxxx5UdgtvCEt7C1I.cOmwKF.ohoazKSyDZVSQZx9yHVqbQ3i', 'user'),
(23, 'admin', '$2y$10$049XrmJiuHZvh3VlDNZpHuDPalVFMDtRG9NYeyPbN8ap4EYfr8Ocy', 'admin'),
(24, 'ayu', '$2y$10$EUgJM/ABdbb70LtCtXEqFeLpQ5qkIPqb68uApjf4Zn.zdG1WwJP2i', 'user'),
(25, 'hay', '$2y$10$w.xSZSv7iGz8pLJGNsjLHeOCmsj008Wd.6hFF9ajsqCaa3yyTJ0Je', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ekskul`
--
ALTER TABLE `ekskul`
  ADD PRIMARY KEY (`id_ekskul`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id_pendaftaran`),
  ADD KEY `id_ekskul` (`id_ekskul`),
  ADD KEY `id_siswa` (`id_siswa`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ekskul`
--
ALTER TABLE `ekskul`
  MODIFY `id_ekskul` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id_pendaftaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `fk_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pendaftaran_ibfk_2` FOREIGN KEY (`id_ekskul`) REFERENCES `ekskul` (`id_ekskul`);

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
