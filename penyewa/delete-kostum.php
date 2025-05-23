<?php
include '../includes/config.php';
include '../auth/auth.php';

$user_id = $_SESSION['user_id'] ?? null;

$kostum_id = $_GET['id'] ?? null;
if (!$kostum_id) {
    die("ID katalog tidak ditemukan.");
}

$stmtPenyewa = $conn->prepare("SELECT id FROM penyewa WHERE user_id = ?");
$stmtPenyewa->bind_param("i", $user_id);
$stmtPenyewa->execute();
$res = $stmtPenyewa->get_result();
$penyewa = $res->fetch_assoc();
$penyewa_id = $penyewa['id'] ?? null;

if (!$penyewa_id) {
    die("Penyewa tidak ditemukan.");
}

$stmt = $conn->prepare("SELECT foto_kostum FROM katalog WHERE id = ? AND penyewa_id = ?");
$stmt->bind_param("ii", $kostum_id, $penyewa_id);
$stmt->execute();
$result = $stmt->get_result();
$katalog = $result->fetch_assoc();

if (!$katalog) {
    die("Katalog tidak ditemukan atau Anda tidak memiliki akses.");
}

if ($katalog['foto_kostum'] && file_exists($katalog['foto_kostum'])) {
    unlink($katalog['foto_kostum']);
}

$stmtDel = $conn->prepare("DELETE FROM katalog WHERE id = ? AND penyewa_id = ?");
$stmtDel->bind_param("ii", $kostum_id, $penyewa_id);

if ($stmtDel->execute()) {
    $_SESSION['success'] = "Katalog berhasil dihapus.";
    header("Location: ../penyewa/dashboard.php");
    exit();
} else {
    $_SESSION['error'] = "Gagal menghapus katalog.";
    header("Location: ../penyewa/dashboard.php");
    exit();
}
