<?php

include '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak!";
    exit;
}

$pembayaran_id = (int) $_POST['pembayaran_id'];
$aksi = $_POST['aksi'];

if ($aksi === 'terima') {
    $conn->query("UPDATE pembayaran SET status = 'Diproses Penyewa' WHERE id = $pembayaran_id");
} elseif ($aksi === 'tolak') {
    $conn->query("UPDATE pembayaran SET status = 'Ditolak' WHERE id = $pembayaran_id");
}

header("Location: konfirmasi.php");
exit;
