<?php
include '../includes/config.php';
include '../includes/header.php';
include '../auth/auth.php';

$user_id = $_SESSION['user_id'];

// Ambil pelanggan_id dari user_id
$sql = "SELECT id FROM pelanggan WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $pelanggan_id = $row['id'];
} else {
    die("Data pelanggan tidak ditemukan untuk user ini.");
}

// Hitung jumlah wishlist
$sqlWishlistCount = "SELECT COUNT(*) as total FROM wishlist WHERE pelanggan_id = ?";
$stmt = $conn->prepare($sqlWishlistCount);
$stmt->bind_param("i", $pelanggan_id);
$stmt->execute();
$totalWishlist = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Hitung jumlah pesanan
$sqlOrderCount = "SELECT COUNT(*) as total FROM pesanan WHERE pelanggan_id = ?";
$stmt = $conn->prepare($sqlOrderCount);
$stmt->bind_param("i", $pelanggan_id);
$stmt->execute();
$totalOrders = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Ambil list pesanan lengkap (join produk)
$sqlOrders = "SELECT p.id as pesanan_id, p.status, p.tanggal_pesan, pr.nama_produk, pr.harga, pr.foto_produk 
              FROM pesanan p 
              JOIN produk pr ON p.produk_id = pr.id 
              WHERE p.pelanggan_id = ? ORDER BY p.tanggal_pesan DESC";
$stmt = $conn->prepare($sqlOrders);
$stmt->bind_param("i", $pelanggan_id);
$stmt->execute();
$orders = $stmt->get_result();

// Ambil produk wishlist (join produk)
$sqlWishlist = "SELECT pr.id, pr.nama_produk, pr.harga, pr.foto_produk
                FROM wishlist w
                JOIN produk pr ON w.produk_id = pr.id
                WHERE w.pelanggan_id = ?";
$stmt = $conn->prepare($sqlWishlist);
$stmt->bind_param("i", $pelanggan_id);
$stmt->execute();
$wishlists = $stmt->get_result();

?>

<div class="container" style="margin-top:100px;">
    <h4 class="text-pinkv2 pt-4 mb-4">Dashboard Pelanggan</h4>

    <div class="row g-3 mb-4">
        <div class="col-sm-3">
            <div class="total-card bg-gradient-katalog p-3 text-center">
                <span class="d-block text-secondary">Jumlah Wishlist</span>
                <div class="count display-6 fw-bold"><?= $totalWishlist ?></div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="total-card bg-gradient-series p-3 text-center">
                <span class="d-block text-secondary">Jumlah Pesanan</span>
                <div class="count display-6 fw-bold"><?= $totalOrders ?></div>
            </div>
        </div>
    </div>

    <h5>Daftar Pesanan</h5>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID Pesanan</th>
                <th>Produk</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Tanggal Pesan</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $orders->fetch_assoc()) : ?>
                <tr>
                    <td><?= htmlspecialchars($order['pesanan_id']) ?></td>
                    <td><?= htmlspecialchars($order['nama_produk']) ?></td>
                    <td>IDR <?= number_format($order['harga'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars(ucfirst($order['status'])) ?></td>
                    <td><?= htmlspecialchars($order['tanggal_pesan']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h5 class="mt-5">Wishlist Produk</h5>
    <div class="row">
        <?php while ($wishlist = $wishlists->fetch_assoc()) : ?>
            <div class="col-sm-3 mb-4">
                <div class="card">
                    <img src="<?= htmlspecialchars($wishlist['foto_produk']) ?>" class="card-img-top" alt="<?= htmlspecialchars($wishlist['nama_produk']) ?>">
                    <div class="card-body">
                        <h6 class="card-title"><?= htmlspecialchars($wishlist['nama_produk']) ?></h6>
                        <p class="card-text">IDR <?= number_format($wishlist['harga'], 0, ',', '.') ?></p>
                        <a href="produk-detail.php?id=<?= $wishlist['id'] ?>" class="btn btn-primary btn-sm">Lihat Produk</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</div>

<?php include '../includes/footer.php'; ?>