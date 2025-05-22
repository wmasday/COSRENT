<?php

include '../includes/config.php';
include '../includes/header.php';

if (!isset($_GET['id'])) {
    echo "Kostum tidak ditemukan.";
    exit;
}

$kostum_id = (int) $_GET['id'];

// Ambil data kostum
$query = $conn->query("
    SELECT k.*, u.username AS penyewa_nama, u.id AS penyewa_id
    FROM kostum k
    JOIN users u ON k.penyewa_id = u.id
    WHERE k.id = $kostum_id AND k.draft = 1
");

if ($query->num_rows !== 1) {
    echo "Kostum tidak ditemukan atau tidak tersedia.";
    exit;
}

$kostum = $query->fetch_assoc();
?>

<div class="container">
    <h2><?= htmlspecialchars($kostum['judul']) ?> - <?= htmlspecialchars($kostum['karakter']) ?></h2>
    <img src="../uploads/<?= htmlspecialchars($kostum['foto']) ?>" width="300">

    <p><strong>Series Anime:</strong> <?= htmlspecialchars($kostum['series']) ?></p>
    <p><strong>Ukuran:</strong> <?= htmlspecialchars($kostum['ukuran']) ?></p>
    <p><strong>Gender:</strong> <?= htmlspecialchars($kostum['gender']) ?></p>
    <p><strong>Harga Sewa / Hari:</strong> Rp<?= number_format($kostum['harga']) ?></p>
    <p><strong>Kategori:</strong> <?= htmlspecialchars($kostum['kategori']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($kostum['status']) ?></p>
    <p><strong>Provinsi / Kota:</strong> <?= htmlspecialchars($kostum['provinsi']) ?> / <?= htmlspecialchars($kostum['kota']) ?></p>
    <p><strong>Deskripsi:</strong> <?= nl2br(htmlspecialchars($kostum['deskripsi'])) ?></p>
    <p><strong>Penyewa:</strong> <a href="../penyewa/profile.php?id=<?= $kostum['penyewa_id'] ?>"><?= htmlspecialchars($kostum['penyewa_nama']) ?></a></p>

    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'pelanggan') : ?>
        <form method="POST" action="../pelanggan/tambah_wishlist.php">
            <input type="hidden" name="kostum_id" value="<?= $kostum['id'] ?>">
            <button type="submit">❤️ Tambah ke Wishlist</button>
        </form>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>