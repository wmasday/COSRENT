-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Waktu pembuatan: 25 Bulan Mei 2025 pada 12.24
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
(4, 3, 'JJK Icad', 'JUJUTSU KELAPA DUA', 'Icadshi', 'L', 'Pria', 'Icadshi Jualan Nasi, Beli Gak Si', 'Kostum', '25000.00', 'Banda Aceh', 'Aceh', 'disewa', 1, '../uploads/katalog/kostum_683070f1cd5c1.jpg', '4.00', 1, '2025-05-23 12:58:25');

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
(3, 7, '0.00', 0);

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
(3, 6, '5.00', 1),
(4, 8, '0.00', 0);

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
(1, 3, 4, '2025-05-24', '2025-05-25', '50000.00', 'dibayar', 'dipinjam', '../uploads/bukti/1748071850_istockphoto-1682296067-612x612.jpg', 'XXX', '2025-05-24 07:30:50', '2025-05-25 11:28:37');

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

--
-- Dumping data untuk tabel `rating`
--

INSERT INTO `rating` (`id`, `penilai_id`, `katalog_id`, `penyewa_id`, `type`, `nilai`, `komentar`, `created_at`) VALUES
(1, 7, 4, NULL, 'katalog', 4, NULL, '2025-05-25 12:11:10'),
(2, 7, NULL, 3, 'penyewa', 5, NULL, '2025-05-25 12:11:46');

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
  `role` enum('admin','penyewa','pelanggan') NOT NULL,
  `ktp_path` varchar(255) DEFAULT NULL,
  `profil_path` varchar(255) DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `bio` text,
  `verifikasi_ktp` tinyint(1) DEFAULT '0',
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verification_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `fullname`, `alamat`, `no_telepon`, `email`, `password`, `role`, `ktp_path`, `profil_path`, `provinsi`, `kota`, `bio`, `verifikasi_ktp`, `email_verified`, `email_verification_token`, `created_at`, `updated_at`) VALUES
(6, 'Penyewa', 'Jl. Penyewa', '62312312312', 'penyewa@gmail.com', '$2y$12$kRGR0Ddks9SyMtrnINCUGu5wJS7XFmHwHv4YOeJkF/8FWALEBtM1W', 'penyewa', '../uploads/ktp/ktp_683070a1f2e931.54122423.jpg', '../uploads/profil/profil_683070a1f2f717.81301634.jpg', 'Aceh', 'Banda Aceh', 'Bio Penyewa', 1, 1, NULL, '2025-05-23 12:57:05', '2025-05-25 06:41:31'),
(7, 'Pelanggan', 'Jln. Pelanggan', '63127846712', 'pelanggan@gmail.com', '$2y$12$V3KCQWljWzUsKct/bfoiKOeD0vFdY9ZCsmKZ0Fz6uAh.y/DqDUFwi', 'pelanggan', '../uploads/ktp/ktp_6830db720db941.39942442.jpg', '../uploads/profil/profil_6830db720dd361.74012499.jpg', 'Aceh', 'Banda Aceh', 'Bio Pelanggan', 1, 1, NULL, '2025-05-23 20:32:50', '2025-05-25 06:41:56'),
(8, 'Admin', 'Jln. Admin', '41241241241', 'admin@gmail.com', '$2y$12$d9uKtQNy7mlT8BVHiE3FYORgB5N86ySuyaXawqy.pe1QAzdcMW5pO', 'admin', '../uploads/ktp/ktp_683184ec647168.42152409.jpg', '../uploads/profil/profil_683184ec649625.02631897.jpg', 'Aceh', 'Banda Aceh', 'Bio Admin', 0, 1, NULL, '2025-05-24 08:35:56', '2025-05-25 06:23:44');

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
  ADD UNIQUE KEY `fullname` (`fullname`),
  ADD UNIQUE KEY `no_telepon` (`no_telepon`),
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT untuk tabel `katalog`
--
ALTER TABLE `katalog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `penyewa`
--
ALTER TABLE `penyewa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `rating`
--
ALTER TABLE `rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

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
