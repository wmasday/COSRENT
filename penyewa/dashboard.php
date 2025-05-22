<?php

include '../includes/config.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyewa') {
    header("Location: ../auth/login.php");
    exit;
}

$penyewa_id = $_SESSION['user_id'];

// Ambil total kostum
$qKostum = $conn->query("SELECT COUNT(*) AS total FROM kostum WHERE penyewa_id = $penyewa_id");
$totalKostum = $qKostum->fetch_assoc()['total'];

// Ambil total series unik (misalnya berdasarkan nama anime yang disebut di nama kostum, asumsi ada penamaan seperti itu)
$qSeries = $conn->query("SELECT COUNT(DISTINCT nama) AS total_series FROM kostum WHERE penyewa_id = $penyewa_id");
$totalSeries = $qSeries->fetch_assoc()['total_series'];

// Ambil daftar kostum
$qList = $conn->query("SELECT * FROM kostum WHERE penyewa_id = $penyewa_id");
?>

<h2>Dashboard Penyewa</h2>

<div class="dashboard-stat">
    <p><strong>Total Kostum:</strong> <?= $totalKostum ?></p>
    <p><strong>Total Series:</strong> <?= $totalSeries ?></p>
</div>

<h3>Kostum yang Disewakan</h3>
<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>Foto</th>
            <th>Nama Kostum</th>
            <th>Deskripsi</th>
            <th>Harga</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($kostum = $qList->fetch_assoc()) : ?>
            <tr>
                <td><img src="../<?= $kostum['foto_path'] ?>" width="80" alt="<?= $kostum['nama'] ?>"></td>
                <td><?= htmlspecialchars($kostum['nama']) ?></td>
                <td><?= htmlspecialchars($kostum['deskripsi']) ?></td>
                <td>Rp<?= number_format($kostum['harga'], 0, ',', '.') ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>