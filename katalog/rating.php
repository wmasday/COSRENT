<?php
include '../includes/config.php';
include '../auth/auth.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Silakan login untuk memberi rating.";
    exit;
}

$penilai_id = $_SESSION['user_id'];  // Ini adalah user yang memberikan rating
$id = (int) ($_POST['id'] ?? 0);
$jenis = $_POST['jenis'] ?? '';
$nilai = (int) ($_POST['nilai'] ?? 0);

if (!in_array($jenis, ['katalog', 'penyewa']) || $id <= 0 || $nilai < 1 || $nilai > 5) {
    http_response_code(400);
    echo "Data tidak valid.";
    exit;
}

// Cek apakah user sudah memberi rating ke target ini sebelumnya
$check = $conn->prepare("SELECT id FROM rating WHERE penilai_id = ? AND {$jenis}_id = ? AND type = ?");
$check->bind_param("iis", $penilai_id, $id, $jenis);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "Anda sudah memberikan rating sebelumnya.";
    exit;
}

// Simpan rating
if ($jenis === 'katalog') {
    $stmt = $conn->prepare("INSERT INTO rating (penilai_id, katalog_id, type, nilai) VALUES (?, ?, ?, ?)");
} else {
    $stmt = $conn->prepare("INSERT INTO rating (penilai_id, penyewa_id, type, nilai) VALUES (?, ?, ?, ?)");
}
$stmt->bind_param("iisi", $penilai_id, $id, $jenis, $nilai);
$stmt->execute();

// Hitung ulang rata-rata dan jumlah rating
$result = $conn->query("SELECT AVG(nilai) AS rata2, COUNT(*) AS jumlah FROM rating WHERE {$jenis}_id = $id AND type = '$jenis'");
$data = $result->fetch_assoc();
$rata2 = round($data['rata2'], 2);
$jumlah = (int)$data['jumlah'];

if ($jenis === 'katalog') {
    $conn->query("UPDATE katalog SET rating = $rata2, jumlah_rating = $jumlah WHERE id = $id");
} else {
    $conn->query("UPDATE penyewa SET rating = $rata2, jumlah_rating = $jumlah WHERE id = $id");
}

echo "Terima kasih atas rating Anda!";
