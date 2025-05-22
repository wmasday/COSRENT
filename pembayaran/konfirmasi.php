<?php

include '../includes/config.php';
include '../includes/header.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak!";
    exit;
}

// Ambil semua pembayaran yang belum diverifikasi
$query = $conn->query("
    SELECT p.*, u.username AS pelanggan_nama, k.judul AS kostum_judul
    FROM pembayaran p
    JOIN users u ON p.pelanggan_id = u.id
    JOIN kostum k ON p.kostum_id = k.id
    WHERE p.status = 'Menunggu Verifikasi'
");

?>

<div class="container">
    <h2>Konfirmasi Pembayaran</h2>

    <?php while ($data = $query->fetch_assoc()) : ?>
        <div style="border:1px solid #ccc; padding:15px; margin:10px 0;">
            <p><strong>Pelanggan:</strong> <?= htmlspecialchars($data['pelanggan_nama']) ?></p>
            <p><strong>Kostum:</strong> <?= htmlspecialchars($data['kostum_judul']) ?></p>
            <p><strong>Total:</strong> Rp<?= number_format($data['total']) ?></p>
            <p><strong>Tanggal:</strong> <?= htmlspecialchars($data['tanggal']) ?></p>
            <p><strong>Bukti Transfer:</strong><br>
                <img src="../uploads/bukti/<?= htmlspecialchars($data['bukti_transfer']) ?>" width="300">
            </p>
            <form method="POST" action="verifikasi_pembayaran.php">
                <input type="hidden" name="pembayaran_id" value="<?= $data['id'] ?>">
                <button type="submit" name="aksi" value="terima">✅ Terima</button>
                <button type="submit" name="aksi" value="tolak">❌ Tolak</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>

<?php include '../includes/footer.php'; ?>