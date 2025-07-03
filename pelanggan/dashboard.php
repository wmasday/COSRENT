<?php
include '../includes/config.php';
include '../includes/header.php';
include '../auth/auth.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("User tidak login.");
}

// Ambil data pelanggan berdasarkan user_id
$sql = "SELECT id FROM pelanggan WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $pelanggan_id = $row['id'];
} else {
    die("Data pelanggan tidak ditemukan.");
}

// Hitung jumlah wishlist
$sqlWishlistCount = "SELECT COUNT(*) AS total FROM wishlist WHERE user_id = ?";
$stmt = $conn->prepare($sqlWishlistCount);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totalWishlist = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Hitung jumlah pesanan
$sqlOrderCount = "SELECT COUNT(*) AS total FROM pesanan WHERE pelanggan_id = ?";
$stmt = $conn->prepare($sqlOrderCount);
$stmt->bind_param("i", $pelanggan_id);
$stmt->execute();
$totalOrders = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Order History
$sqlOrderHistory = "SELECT 
    p.id AS pesanan_id,
    p.tanggal_sewa,
    p.tanggal_kembali,
    p.total_harga,
    p.status_pembayaran,
    p.status_peminjaman,
    k.nama_kostum,
    k.foto_kostum
FROM pesanan p
JOIN katalog k ON p.katalog_id = k.id
WHERE p.pelanggan_id = ?
AND p.status_pembayaran = 'dibayar'
ORDER BY p.tanggal_sewa DESC";

$historyStmt = $conn->prepare($sqlOrderHistory);
$historyStmt->bind_param("i", $pelanggan_id);
$historyStmt->execute();
$riwayatPesanan = $historyStmt->get_result();

// Ambil daftar pesanan lengkap dengan katalog
$sqlOrders = "SELECT 
    p.id AS pesanan_id,
    p.tanggal_sewa,
    p.tanggal_kembali,
    p.total_harga,
    p.status_pembayaran,
    p.status_peminjaman,
    p.bukti_pembayaran,
    p.catatan,
    k.nama_kostum,
    k.harga_sewa,
    k.foto_kostum
FROM pesanan p
JOIN katalog k ON p.katalog_id = k.id
WHERE p.pelanggan_id = ?
ORDER BY p.tanggal_sewa DESC";
$stmt = $conn->prepare($sqlOrders);
$stmt->bind_param("i", $pelanggan_id);
$stmt->execute();
$orders = $stmt->get_result();

// Ambil wishlist dengan detail katalog
$sqlWishlist = "SELECT 
    k.id AS katalog_id,
    k.nama_kostum,
    k.harga_sewa,
    k.foto_kostum,
    k.karakter,
    k.series
FROM wishlist w
JOIN katalog k ON w.katalog_id = k.id
WHERE w.user_id = ?";
$stmt = $conn->prepare($sqlWishlist);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlists = $stmt->get_result();

// Ambil semua katalog_id yang sudah ada di wishlist
$wishlist_ids = [];
$stmt2 = $conn->prepare("SELECT katalog_id FROM wishlist WHERE user_id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($w = $res2->fetch_assoc()) {
    $wishlist_ids[] = $w['katalog_id'];
}
?>

<div class="container" style="margin-top:100px;">
    <h4 class="text-pinkv2 pt-4 mb-2">Dashboard Pelanggan</h4>

    <div class="row g-3 mb-4">
        <div class="col-sm-2">
            <div class="total-card bg-gradient-katalog p-3 text-start">
                <span class="d-block text-secondary">Wishlist</span>
                <div class="count display-6 fw-bold"><?= $totalWishlist ?></div>
            </div>
        </div>
        <div class="col-sm-2">
            <div class="total-card bg-gradient-series p-3 text-start">
                <span class="d-block text-secondary">Pesanan</span>
                <div class="count display-6 fw-bold"><?= $totalOrders ?></div>
            </div>
        </div>
    </div>

    <h5 class="mt-5">Daftar Pesanan</h5>
    <div class="accordion glass-accordion" id="accordionPesanan">
        <?php $no = 1;
        $orders->data_seek(0);
        while ($order = $orders->fetch_assoc()) :
            $badgeClass = 'pending';
            if ($order['status_pembayaran'] === 'dibayar') $badgeClass = 'dibayar';
            elseif ($order['status_pembayaran'] === 'dibatalkan') $badgeClass = 'dibatalkan';
            elseif ($order['status_pembayaran'] === 'selesai') $badgeClass = 'selesai';
        ?>
            <div class="bg-gradient-tersedia accordion-item border-0 mb-3">
                <h3 class="accordion-header" id="heading<?= $no ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $no ?>" aria-expanded="false" aria-controls="collapse<?= $no ?>">
                        <?= htmlspecialchars($order['nama_kostum']) ?> | <?= htmlspecialchars($order['tanggal_sewa']) ?>
                    </button>
                </h3>
                <div id="collapse<?= $no ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $no ?>" data-bs-parent="#accordionPesanan">
                    <div class="accordion-body text-dark">
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <strong>Tanggal Sewa</strong><br><?= $order['tanggal_sewa'] ?> s.d. <?= $order['tanggal_kembali'] ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Total Harga</strong><br>IDR <?= number_format($order['total_harga'], 0, ',', '.') ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Status Pembayaran</strong><br>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= htmlspecialchars($order['status_pembayaran']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 text-capitalize">
                                <strong>Status Peminjaman</strong><br><?= htmlspecialchars(str_replace('_', ' ', $order['status_peminjaman'])) ?>
                            </div>
                            <div class="col-md-8">
                                <strong>Catatan</strong><br><small><?= nl2br(htmlspecialchars($order['catatan'])) ?></small>
                            </div>
                        </div>
                        <div>
                            <strong>Bukti Pembayaran</strong><br>
                            <?php if ($order['bukti_pembayaran']) : ?>
                                <a href="../uploads/<?= htmlspecialchars($order['bukti_pembayaran']) ?>" target="_blank">
                                    <img src="../uploads/<?= htmlspecialchars($order['bukti_pembayaran']) ?>" class="img-fluid rounded mt-2" style="max-height:100px;">
                                </a>
                            <?php else : ?>
                                <span class="text-muted">Belum Diupload</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php $no++;
        endwhile; ?>
    </div>


    <h5 class="mt-5">Wishlist Kostum</h5>
    <div class="row">
        <?php while ($wishlist = $wishlists->fetch_assoc()) :
            $foto = $wishlist['foto_kostum'] ? $wishlist['foto_kostum'] : 'https://picsum.photos/400/300';
            $harga_formatted = "IDR " . number_format($wishlist['harga_sewa'], 0, ',', '.');
            $active = in_array($wishlist['katalog_id'], $wishlist_ids) ? 'active' : '';
        ?>
            <div class="col-sm-3 px-3 mb-3">
                <div class="card w-100">
                    <img src="<?= htmlspecialchars($foto) ?>" class="card-img-top" alt="Foto Kostum">
                    <div class="card-body">
                        <a href="./katalog/index.php?keyword=<?= htmlspecialchars($wishlist['karakter']); ?>" class="badge bg-character float-start py-2">
                            <i class="bi bi-person-rolodex me-1"></i>
                            <?= htmlspecialchars($wishlist['karakter']) ?>
                        </a>
                        <a href="./katalog/index.php?keyword=<?= htmlspecialchars($wishlist['series']); ?>" class="badge bg-series float-end py-2">
                            <i class="bi bi-tags-fill me-1"></i>
                            <?= htmlspecialchars($wishlist['series']) ?>
                        </a>
                        <div class="price mt-5 mb-3">
                            <i class="bi bi-wallet2 me-2"></i>
                            <?= $harga_formatted ?><span> / Hari</span>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <a href="../wishlist.php?katalog_id=<?= $wishlist['katalog_id'] ?>" class="btn wishlist-btn <?= $active ?>">
                                    <i class="bi bi-bag-heart-fill"></i>
                                </a>
                            </div>
                            <div class="col-9">
                                <a href="../katalog/detail.php?id=<?= $wishlist['katalog_id'] ?>" class="btn btn-detail">
                                    View Detail <i class="bi bi-chevron-compact-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <h5 class="mt-5">Riwayat Pesanan</h5>
    <?php if ($riwayatPesanan->num_rows > 0) : ?>
        <div class="row">
            <?php $no = 1;
            $orders->data_seek(0);
            while ($order = $orders->fetch_assoc()) :
                $badgeClass = match ($order['status_pembayaran']) {
                    'dibayar' => 'success',
                    'selesai' => 'primary',
                    'dibatalkan' => 'danger',
                    default => 'secondary'
                };
            ?>
                <div class="col-sm-4 mt-2">
                    <div class="accordion glass-accordion" id="accordionPesanan">
                        <div class="accordion-item mb-3 bg-gradient-tersedia border-0">
                            <h2 class="accordion-header" id="heading<?= $no ?>">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHistory<?= $no ?>" aria-expanded="false" aria-controls="collapse<?= $no ?>">
                                    <div class="w-100 d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($order['nama_kostum']) ?></strong>
                                            <div class="text-muted small"><?= $order['tanggal_sewa'] ?> s.d. <?= $order['tanggal_kembali'] ?></div>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseHistory<?= $no ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $no ?>" data-bs-parent="#accordionPesanan">
                                <div class="accordion-body text-dark">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <strong>Total Harga</strong><br>
                                            IDR <?= number_format($order['total_harga'], 0, ',', '.') ?>
                                        </div>
                                        <div class="col-sm-4">
                                            <strong>Status</strong><br>
                                            <span class="text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($order['status_peminjaman'])) ?></span>
                                        </div>
                                        <div class="col-sm-4">
                                            <strong>Catatan</strong><br>
                                            <small><?= nl2br(htmlspecialchars($order['catatan'])) ?></small>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-sm-6">
                                            <strong>Bukti Pembayaran</strong><br>
                                            <?php if ($order['bukti_pembayaran']) : ?>
                                                <a href="../uploads/<?= htmlspecialchars($order['bukti_pembayaran']) ?>" target="_blank">
                                                    <img src="../uploads/<?= htmlspecialchars($order['bukti_pembayaran']) ?>" class="img-thumbnail mt-2" style="max-height: 120px;">
                                                </a>
                                            <?php else : ?>
                                                <span class="text-muted">Belum diupload</span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-sm-6">
                                            <strong>Preview</strong><br>
                                            <?php if ($order['foto_kostum']) : ?>
                                                <img src="../uploads/<?= htmlspecialchars($order['foto_kostum']) ?>" class="img-thumbnail mt-2" style="max-height: 120px;">
                                            <?php else : ?>
                                                <span class="text-muted">Belum diupload</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php $no++;
            endwhile; ?>
        </div>

    <?php else : ?>
        <p class="text-muted">Belum ada riwayat pesanan.</p>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>