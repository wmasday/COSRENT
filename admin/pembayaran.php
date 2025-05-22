<?php

include '../includes/config.php';
include '../includes/header.php';

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

// Verifikasi jika ada tindakan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rental_id = $_POST['rental_id'];
    $aksi = $_POST['aksi']; // 'setujui' atau 'tolak'

    $status = ($aksi === 'setujui') ? 'Diproses Penyewa' : 'Ditolak';
    $conn->query("UPDATE rentals SET status='$status' WHERE id=$rental_id");
}

$rentals = $conn->query("
    SELECT r.*, u.username, k.judul
    FROM rentals r
    JOIN users u ON r.user_id = u.id
    JOIN kostum k ON r.kostum_id = k.id
    WHERE r.status = 'Menunggu Pembayaran'
");
?>

<h2>Verifikasi Pembayaran</h2>

<?php while ($r = $rentals->fetch_assoc()) : ?>
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <p><strong><?= htmlspecialchars($r['judul']) ?></strong> (<?= $r['lama_sewa'] ?> hari)</p>
        <p>Oleh: <?= htmlspecialchars($r['username']) ?></p>
        <p>Tanggal Sewa: <?= $r['tanggal_sewa'] ?></p>
        <p>Total: Rp<?= number_format($r['total_harga']) ?></p>
        <p>Bukti: <a href="<?= $r['bukti_transfer'] ?>" target="_blank">Lihat</a></p>

        <form method="POST">
            <input type="hidden" name="rental_id" value="<?= $r['id'] ?>">
            <button name="aksi" value="setujui">Setujui</button>
            <button name="aksi" value="tolak">Tolak</button>
        </form>
    </div>
<?php endwhile; ?>

<?php include '../includes/footer.php'; ?>