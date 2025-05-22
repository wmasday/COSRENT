<link rel="stylesheet" href="../assets/css/style.css"> <!-- Perbaiki path CSS -->

<?php
include '../includes/config.php';
include '../includes/header.php';

$keyword   = $_GET['keyword'] ?? '';
$kategori  = $_GET['kategori'] ?? '';
$provinsi  = $_GET['provinsi'] ?? '';
$kota      = $_GET['kota'] ?? '';

// Query dasar
$sql = "SELECT * FROM kostum WHERE is_draft = 0 AND status = 'Ready'";

// Tambah filter
if (!empty($keyword)) {
    $sql .= " AND (judul LIKE '%$keyword%' OR series_anime LIKE '%$keyword%' OR karakter LIKE '%$keyword%')";
}
if (!empty($kategori)) {
    $sql .= " AND kategori = '$kategori'";
}
if (!empty($provinsi)) {
    $sql .= " AND provinsi = '$provinsi'";
}
if (!empty($kota)) {
    $sql .= " AND kota = '$kota'";
}

$result = $conn->query($sql);
?>

<h2>Pencarian Kostum</h2>
<form method="GET" action="">
    <input type="text" name="keyword" placeholder="Cari judul, karakter, atau anime" value="<?= htmlspecialchars($keyword) ?>">

    <select name="kategori">
        <option value="">Semua Kategori</option>
        <option value="Kostum" <?= $kategori == 'Kostum' ? 'selected' : '' ?>>Kostum</option>
        <option value="Wig Only" <?= $kategori == 'Wig Only' ? 'selected' : '' ?>>Wig Only</option>
        <option value="Sepatu" <?= $kategori == 'Sepatu' ? 'selected' : '' ?>>Sepatu</option>
        <option value="Weapon" <?= $kategori == 'Weapon' ? 'selected' : '' ?>>Weapon</option>
    </select>

    <input type="text" name="provinsi" placeholder="Provinsi" value="<?= htmlspecialchars($provinsi) ?>">
    <input type="text" name="kota" placeholder="Kota" value="<?= htmlspecialchars($kota) ?>">

    <button type="submit">Cari</button>
</form>

<hr>

<?php if ($result && $result->num_rows > 0) : ?>
    <div class="kostum-list">
        <?php while ($row = $result->fetch_assoc()) : ?>
            <div class="kostum-card">
                <img src="../<?= htmlspecialchars($row['foto_path']) ?>" width="150" height="200" alt="Foto Kostum">
                <h3><?= htmlspecialchars($row['judul']) ?></h3>
                <p><?= htmlspecialchars($row['series_anime']) ?> - <?= htmlspecialchars($row['karakter']) ?></p>
                <p><?= htmlspecialchars($row['kota']) ?>, <?= htmlspecialchars($row['provinsi']) ?></p>
                <p>Rp<?= number_format($row['harga']) ?>/hari</p>
                <a href="detail.php?id=<?= $row['id'] ?>">Lihat Detail</a>
            </div>
        <?php endwhile; ?>
    </div>
<?php else : ?>
    <p>Tidak ada kostum ditemukan.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>