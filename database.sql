-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Waktu pembuatan: 03 Jul 2025 pada 11.22
-- Versi server: 5.7.39
-- Versi PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_cosrent`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `chat`
--

CREATE TABLE `chat` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `pesan` text NOT NULL,
  `waktu_kirim` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_baca` enum('dibaca','belum_dibaca') DEFAULT 'belum_dibaca'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `chat`
--

INSERT INTO `chat` (`id`, `sender_id`, `receiver_id`, `pesan`, `waktu_kirim`, `status_baca`) VALUES
(9, 11, 10, 'bang', '2025-07-03 10:17:15', 'dibaca'),
(10, 11, 10, 'bang', '2025-07-03 10:17:22', 'dibaca'),
(11, 10, 11, 'oit', '2025-07-03 10:39:32', 'dibaca');

-- --------------------------------------------------------

--
-- Struktur dari tabel `katalog`
--

CREATE TABLE `katalog` (
  `id` int(11) NOT NULL,
  `penyewa_id` int(11) NOT NULL,
  `nama_kostum` varchar(100) NOT NULL,
  `series` varchar(100) NOT NULL,
  `karakter` varchar(100) NOT NULL,
  `ukuran` varchar(10) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `deskripsi` text,
  `kategori` varchar(50) NOT NULL,
  `harga_sewa` decimal(10,2) NOT NULL,
  `kota` varchar(50) NOT NULL,
  `provinsi` varchar(50) NOT NULL,
  `status` enum('tersedia','disewa','maintenance') NOT NULL DEFAULT 'tersedia',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `foto_kostum` varchar(255) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT '0.00',
  `jumlah_rating` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `katalog`
--

INSERT INTO `katalog` (`id`, `penyewa_id`, `nama_kostum`, `series`, `karakter`, `ukuran`, `gender`, `deskripsi`, `kategori`, `harga_sewa`, `kota`, `provinsi`, `status`, `visible`, `foto_kostum`, `rating`, `jumlah_rating`, `created_at`) VALUES
(5, 5, 'RAW', 'QR', 'QR', 'L', 'Pria', 'XXX', 'Kostum', '125000.00', 'Banda Aceh', 'Aceh', 'disewa', 1, '../uploads/katalog/kostum_686657ffc48c7.png', '0.00', 0, '2025-07-03 10:14:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` decimal(3,2) DEFAULT '0.00',
  `jumlah_rating` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`id`, `user_id`, `rating`, `jumlah_rating`) VALUES
(5, 11, '0.00', 0),
(6, 12, '0.00', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `penyewa`
--

CREATE TABLE `penyewa` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` decimal(3,2) DEFAULT '0.00',
  `jumlah_rating` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `penyewa`
--

INSERT INTO `penyewa` (`id`, `user_id`, `rating`, `jumlah_rating`) VALUES
(5, 10, '0.00', 0),
(6, 13, '0.00', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `pelanggan_id` int(11) NOT NULL,
  `katalog_id` int(11) NOT NULL,
  `tanggal_sewa` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status_pembayaran` enum('pending','dibayar','dibatalkan','selesai') NOT NULL DEFAULT 'pending',
  `status_peminjaman` enum('belum_diambil','dipinjam','dikembalikan','terlambat') DEFAULT 'belum_diambil',
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `catatan` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id`, `pelanggan_id`, `katalog_id`, `tanggal_sewa`, `tanggal_kembali`, `total_harga`, `status_pembayaran`, `status_peminjaman`, `bukti_pembayaran`, `catatan`, `created_at`, `updated_at`) VALUES
(2, 5, 5, '2025-07-03', '2025-07-05', '375000.00', 'dibayar', 'dipinjam', '../uploads/bukti/1751537938_1512-qris.png', 'RAW', '2025-07-03 10:18:58', '2025-07-03 10:39:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rating`
--

CREATE TABLE `rating` (
  `id` int(11) NOT NULL,
  `penilai_id` int(11) NOT NULL,
  `katalog_id` int(11) DEFAULT NULL,
  `penyewa_id` int(11) DEFAULT NULL,
  `type` enum('katalog','penyewa') NOT NULL,
  `nilai` int(11) NOT NULL,
  `komentar` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `alamat` text NOT NULL,
  `no_telepon` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `role` enum('admin','penyewa','pelanggan') NOT NULL,
  `ktp_path` varchar(255) DEFAULT NULL,
  `selfie_path` varchar(255) DEFAULT NULL,
  `profil_path` varchar(255) DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `bio` text,
  `verifikasi_ktp` tinyint(1) DEFAULT '0',
  `verifikasi_selfie_ktp` tinyint(1) DEFAULT '0',
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verification_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `fullname`, `alamat`, `no_telepon`, `email`, `password`, `nik`, `role`, `ktp_path`, `selfie_path`, `profil_path`, `provinsi`, `kota`, `bio`, `verifikasi_ktp`, `verifikasi_selfie_ktp`, `email_verified`, `email_verification_token`, `created_at`, `updated_at`) VALUES
(9, 'Trust Sec', 'Jln. Dewa 19', '089653148498', 'admin@gmail.com', '$2y$12$3rSEG3zWEcyYdr2Boumt7e33SZR6Blf6LJ2ctF4/mWratqK.la.xu', '1234567890123456', 'admin', '../uploads/ktp/ktp_68665202be06a5.18744910.jpg', '../uploads/selfie/selfie_68665202be1306.53186024.jpg', '../uploads/profil/profil_68665202be1950.33283484.jpg', 'Banten', 'Tangerang', 'Bio Admin', 1, 1, 1, NULL, '2025-07-03 09:48:50', '2025-07-03 09:50:14'),
(10, 'Penyewa', 'Jln. Dewa 19', '089653148412', 'penyewa@gmail.com', '$2y$12$4ZAXkzkGDa3cvbhlP/./gOr2UEep9/3AVOa5Z6.47I2U81s71lZ.y', '1234567890123451', 'penyewa', '../uploads/ktp/ktp_6866547694447.png', '../uploads/selfie/selfie_68665623f140e.png', '../uploads/profil/profil_686652be6b42f1.59016759.jpg', 'Banten', 'Serang', 'Bio Penyewa', 1, 1, 1, NULL, '2025-07-03 09:51:58', '2025-07-03 10:38:47'),
(11, 'Pelanggan', 'Jln. Mandor Muhi', '081293534819', 'pelanggan@gmail.com', '$2y$12$GSUqvOkrTnHnC1C8ZUBI7e0y6r9ozk3fSvx2A.2hQDGu4hYW02C6q', '1234567890123452', 'pelanggan', '../uploads/ktp/ktp_6866584657d876.92446734.png', '../uploads/selfie/selfie_68665846581303.90037865.png', '../uploads/profil/profil_68665846581766.55044924.png', 'Banten', 'Serang', 'Bio Pelanggan', 1, 1, 1, NULL, '2025-07-03 10:15:34', '2025-07-03 10:38:51'),
(12, 'Pelanggan 2', 'Jln. Dewa 19', '0896531484912', 'pelanggan2@gmail.com', '$2y$12$DhKD880KNWRy0DKMUq72/Obpt.MEude.jQyTdCiIuDHsy.2DnBfLW', '1234567890123450', 'pelanggan', '../uploads/ktp/ktp_686664f4867906.16488622.png', '../uploads/selfie/selfie_686664f4868ea1.09216821.png', '../uploads/profil/profil_686664f48692a1.74250507.png', 'Aceh', 'Banda Aceh', 'Bio Pelanggan 2', 0, 0, 1, NULL, '2025-07-03 11:09:40', '2025-07-03 11:10:01'),
(13, 'Penyewa 2', 'Jln. Dewa 19', '0896531484231', 'penyewa2@gmail.com', '$2y$12$D4pKLKY4YFLthNWF9vCejeGU1j6mU4rPADgz7c2GyOfeJbUDk/R7S', '1234567890123454', 'penyewa', '../uploads/ktp/ktp_686667e0cc5b16.62336593.png', '../uploads/selfie/selfie_686667e0cc70a7.63897113.png', '../uploads/profil/profil_686667e0cc7514.82740298.png', 'Aceh', 'Banda Aceh', 'Bio Penyewa 2', 0, 0, 1, NULL, '2025-07-03 11:22:08', '2025-07-03 11:22:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `katalog_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indeks untuk tabel `katalog`
--
ALTER TABLE `katalog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penyewa_id` (`penyewa_id`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `penyewa`
--
ALTER TABLE `penyewa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelanggan_id` (`pelanggan_id`),
  ADD KEY `katalog_id` (`katalog_id`);

--
-- Indeks untuk tabel `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_telepon` (`no_telepon`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- Indeks untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `katalog_id` (`katalog_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `katalog`
--
ALTER TABLE `katalog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `penyewa`
--
ALTER TABLE `penyewa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `rating`
--
ALTER TABLE `rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `katalog`
--
ALTER TABLE `katalog`
  ADD CONSTRAINT `katalog_ibfk_1` FOREIGN KEY (`penyewa_id`) REFERENCES `penyewa` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD CONSTRAINT `pelanggan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penyewa`
--
ALTER TABLE `penyewa`
  ADD CONSTRAINT `penyewa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`),
  ADD CONSTRAINT `pesanan_ibfk_2` FOREIGN KEY (`katalog_id`) REFERENCES `katalog` (`id`);

--
-- Ketidakleluasaan untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`katalog_id`) REFERENCES `katalog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
