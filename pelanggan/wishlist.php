<?php

include '../includes/config.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../auth/login.php");
    exit;
}

$pelanggan_id = $_SESSION['user_id'];

$result = $conn->query("
    SELECT k.*, w.id AS wishlist_id
    FROM wishlist w
    JOIN kostum k ON w.kostum_id = k.id
    WHERE w.pelanggan_id = $pelanggan_id
");

?>

<h2>Wishlist Kostum</h2>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>Foto</th>
            <th>Nama Kostum</th>
            <th>Harga</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <tr>
                <td><img src="../<?= $row['foto_path'] ?>" width="80"></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
                <td>
                    <a href="hapus_wishlist.php?id=<?= $row['wishlist_id'] ?>">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>