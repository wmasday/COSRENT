<?php

include '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyewa') {
    header("Location: ../auth/login.php");
    exit;
}

$penyewa_id    = $_SESSION['user_id'];
$kategori      = $_POST['kategori'];
$status        = $_POST['status'];
$judul         = $_POST['judul'];
$series_anime  = $_POST['series_anime'];
$karakter      = $_POST['karakter'];
$ukuran        = $_POST['ukuran'];
$gender        = $_POST['gender'];
$harga         = $_POST['harga'];
$provinsi      = $_POST['provinsi'];
$kota          = $_POST['kota'];
$deskripsi     = $_POST['deskripsi'];
$is_draft      = $_POST['is_draft'] == "1" ? 1 : 0;

// Upload file
$foto = $_FILES['foto']['name'];
$tmp  = $_FILES['foto']['tmp_name'];
$path = "uploads/kostum/" . time() . "_" . basename($foto);

if (move_uploaded_file($tmp, "../" . $path)) {
    $stmt = $conn->prepare("INSERT INTO kostum 
        (penyewa_id, foto_path, kategori, status, judul, series_anime, karakter, ukuran, gender, harga, provinsi, kota, deskripsi, is_draft) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("issssssssisssi", $penyewa_id, $path, $kategori, $status, $judul, $series_anime, $karakter, $ukuran, $gender, $harga, $provinsi, $kota, $deskripsi, $is_draft);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
    } else {
        echo "Gagal menyimpan kostum: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Gagal upload foto.";
}
