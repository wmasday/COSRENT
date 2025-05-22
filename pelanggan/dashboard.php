<?php

include '../includes/config.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../auth/login.php");
    exit;
}

$pelanggan_id = $_SESSION['user_id'];

// Ambil total sewa
$qTotal = $conn->query("SELECT COUNT(*) AS total FROM sewa WHERE pelanggan_id = $pelanggan_id");
$totalSewa = $qTotal->fetch_assoc()['total'];

// Ambil daftar sewa dengan detail kostum
$qList = $conn->query("
    SELECT s.*, k.nama AS nama_kostum, k.foto_path, k.harga
    FROM sewa s
    JOIN kostum k ON s.kostum_id = k.id
    WHERE s.pelanggan_id = $pelanggan_id
    ORDER BY s.tanggal_sewa DESC
");
?>

<h2>Dashboard Pelanggan</h2>

<div class="dashboard-stat">
    <p><strong>Total Penyewaan:</strong> <?= $totalSewa ?></p>
</div>

<h3>Riwayat Penyewaan</h3>
<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>Foto Kostum</th>
            <th>Nama Kostum</th>
            <th>Tanggal Sewa</th>
            <th>Tanggal Kembali</th>
            <th>Status</th>
            <th>Harga</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $qList->fetch_assoc()) : ?>
            <tr>
                <td><img src="../<?= $row['foto_path'] ?>" width="80" alt="<?= $row['nama_kostum'] ?>"></td>
                <td><?= htmlspecialchars($row['nama_kostum']) ?></td>
                <td><?= $row['tanggal_sewa'] ?></td>
                <td><?= $row['tanggal_kembali'] ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>