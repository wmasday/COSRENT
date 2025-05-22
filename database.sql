-- Create the database
CREATE DATABASE IF NOT EXISTS db_cosplay;
USE db_cosplay;

-- Users table for authentication
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fullname VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'penyewa', 'pelanggan') NOT NULL,
    ktp_path VARCHAR(255),
    provinsi VARCHAR(100),
    kota VARCHAR(100),
    bio TEXT,
    verifikasi_ktp TINYINT(1) DEFAULT 0,
    email_verified TINYINT(1) DEFAULT 0,
    email_verification_token VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Penyewa (Costume Renters/Owners) table
CREATE TABLE penyewa (
    penyewa_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(15) NOT NULL,
    alamat TEXT NOT NULL,
    foto_profil VARCHAR(255),
    status_verifikasi ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    rating DECIMAL(3,2) DEFAULT 0.00,
    jumlah_rating INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Pelanggan (Customers) table
CREATE TABLE pelanggan (
    pelanggan_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(15) NOT NULL,
    alamat TEXT NOT NULL,
    foto_profil VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0.00,
    jumlah_rating INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Katalog (Costume Catalog) table
CREATE TABLE katalog (
    kostum_id INT PRIMARY KEY AUTO_INCREMENT,
    penyewa_id INT NOT NULL,
    nama_kostum VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    kategori VARCHAR(50) NOT NULL,
    ukuran VARCHAR(10) NOT NULL,
    harga_sewa DECIMAL(10,2) NOT NULL,
    status ENUM('tersedia', 'disewa', 'maintenance') NOT NULL DEFAULT 'tersedia',
    foto_kostum VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0.00,
    jumlah_rating INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penyewa_id) REFERENCES penyewa(penyewa_id) ON DELETE CASCADE
);

-- Pembayaran (Payments) table
CREATE TABLE pembayaran (
    pembayaran_id INT PRIMARY KEY AUTO_INCREMENT,
    pelanggan_id INT NOT NULL,
    kostum_id INT NOT NULL,
    tanggal_sewa DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    status_pembayaran ENUM('pending', 'dibayar', 'dibatalkan', 'selesai') NOT NULL DEFAULT 'pending',
    status_peminjaman ENUM('belum_diambil', 'dipinjam', 'dikembalikan', 'terlambat') DEFAULT 'belum_diambil',
    bukti_pembayaran VARCHAR(255),
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(pelanggan_id),
    FOREIGN KEY (kostum_id) REFERENCES katalog(kostum_id)
);

-- Chat table
CREATE TABLE chat (
    chat_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    pesan TEXT NOT NULL,
    waktu_kirim TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_baca ENUM('dibaca', 'belum_dibaca') DEFAULT 'belum_dibaca',
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);

-- Rating table for reviews
CREATE TABLE rating (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    pembayaran_id INT NOT NULL,
    penilai_id INT NOT NULL,
    dinilai_id INT NOT NULL,
    kostum_id INT NOT NULL,
    nilai INT NOT NULL CHECK (nilai BETWEEN 1 AND 5),
    komentar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pembayaran_id) REFERENCES pembayaran(pembayaran_id),
    FOREIGN KEY (penilai_id) REFERENCES users(user_id),
    FOREIGN KEY (dinilai_id) REFERENCES users(user_id),
    FOREIGN KEY (kostum_id) REFERENCES katalog(kostum_id)
);

-- Insert default admin user
INSERT INTO users (username, password, email, role) 
VALUES ('admin', '$2y$10$your_hashed_password', 'admin@cosplaylampung.com', 'admin'); 