<?php
include '../includes/config.php';
include '../includes/header.php';
include '../auth/auth.php';

$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role IN ('pelanggan', 'penyewa')"))['total'];
$totalPenyewa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'penyewa'"))['total'];
$totalPenyewaVerif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'penyewa' AND verifikasi_ktp = 1"))['total'];
$totalPelanggan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'pelanggan'"))['total'];
$totalPelangganVerif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'pelanggan' AND verifikasi_ktp = 1"))['total'];
$totalKatalog = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM katalog"))['total'];
$totalKTPPending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE verifikasi_ktp = 0 AND role IN ('penyewa', 'pelanggan')"))['total'];
$totalSelfiePending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE verifikasi_selfie_ktp = 0 AND role IN ('penyewa', 'pelanggan')"))['total'];
$totalBayarPending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pesanan WHERE status_pembayaran = 'pending'"))['total'];

$stmt = $conn->prepare("SELECT * FROM users WHERE verifikasi_ktp = 0 OR verifikasi_selfie_ktp = 0 AND role IN ('penyewa', 'pelanggan')");
$stmt->execute();
$result = $stmt->get_result();
$unverifiedUsers = $result->fetch_all(MYSQLI_ASSOC);

$pendingPayments = [];
$query = "SELECT o.id, u.fullname, u.email, o.bukti_pembayaran 
          FROM pesanan o
          JOIN pelanggan p ON o.pelanggan_id = p.id
          JOIN users u ON p.user_id = u.id
          WHERE o.status_pembayaran = 'pending'";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pendingPayments[] = $row;
    }
}


if (isset($_GET['verify_user_ktp'])) {
    $verifyUserId = intval($_GET['verify_user_ktp']);
    $stmt = $conn->prepare("UPDATE users SET verifikasi_ktp = 1 WHERE id = ?");
    $stmt->bind_param("i", $verifyUserId);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Verifikasi KTP User Berhasil!";
    header("Location: ./dashboard.php");
    exit();
}

if (isset($_GET['verify_user_selfie'])) {
    $verifyUserId = intval($_GET['verify_user_selfie']);
    $stmt = $conn->prepare("UPDATE users SET verifikasi_selfie_ktp = 1 WHERE id = ?");
    $stmt->bind_param("i", $verifyUserId);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Verifikasi Selfie User Berhasil!";
    header("Location: ./dashboard.php");
    exit();
}

if (isset($_GET['verify_payment'])) {
    $orderId = intval($_GET['verify_payment']);

    // Update status pembayaran di pesanan
    $stmt = $conn->prepare("UPDATE pesanan SET status_pembayaran = 'dibayar' WHERE id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $stmt->close();

    // Ambil katalog_id dari pesanan yang diverifikasi
    $stmt = $conn->prepare("SELECT katalog_id FROM pesanan WHERE id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $stmt->bind_result($katalogId);
    $stmt->fetch();
    $stmt->close();

    // Update status katalog jadi disewa
    if (!empty($katalogId)) {
        $stmt = $conn->prepare("UPDATE katalog SET status = 'disewa' WHERE id = ?");
        $stmt->bind_param("i", $katalogId);
        $stmt->execute();
        $stmt->close();
    }

    $_SESSION['success'] = "Verifikasi Pembayaran dan Update Status Katalog Berhasil!";
    header("Location: ./dashboard.php");
    exit();
}



?>

<div class="container" style="margin-top:100px;">
    <h4 class="text-pinkv2 pt-4 mb-4">Dashboard Admin</h4>
    <div class="dashboard-stat row g-3">
        <div class="col-md-3">
            <div class="total-card bg-grad-1 p-3">
                <span class="d-block">Total Users</span>
                <div class="count display-6 fw-bold"><?= $totalUsers ?></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="total-card bg-grad-2 p-3">
                <span class="d-block">Total Penyewa</span>
                <div class="count display-6 fw-bold"><?= $totalPenyewa ?></div>
                <small>Terverifikasi: <?= $totalPenyewaVerif ?></small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="total-card bg-grad-3 p-3">
                <span class="d-block">Total Pelanggan</span>
                <div class="count display-6 fw-bold"><?= $totalPelanggan ?></div>
                <small>Terverifikasi: <?= $totalPelangganVerif ?></small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="total-card bg-grad-4 p-3">
                <span class="d-block">Total Katalog</span>
                <div class="count display-6 fw-bold"><?= $totalKatalog ?></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="total-card bg-grad-5 p-3">
                <span class="d-block">Menunggu Verifikasi KTP</span>
                <div class="count display-6 fw-bold"><?= $totalKTPPending ?></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="total-card bg-grad-1 p-3">
                <span class="d-block">Menunggu Verifikasi Selfie</span>
                <div class="count display-6 fw-bold"><?= $totalSelfiePending ?></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="total-card bg-grad-6 p-3">
                <span class="d-block">Pembayaran Pending</span>
                <div class="count display-6 fw-bold"><?= $totalBayarPending ?></div>
            </div>
        </div>
    </div>

    <style type="text/css">
        .table,
        .table tr,
        .table td,
        .table th {
            background: transparent !important;
        }
    </style>
    <h5 class="text-pinkv2 mt-5 mb-3">Menunggu Verifikasi KTP</h5>
    <?php if (count($unverifiedUsers) > 0) : ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>KYC</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unverifiedUsers as $index => $user) : ?>
                        <tr>
                            <td><?= htmlspecialchars($user['fullname']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php if ($user['ktp_path']) : ?>
                                    <button class="btn btn-sm bg-grad-5 view-ktp-btn" data-bs-toggle="modal" data-bs-target="#ktpModal" data-img="<?= htmlspecialchars($user['ktp_path']) ?>">
                                        Lihat KTP
                                    </button>
                                <?php else : ?>
                                    <span class="text-muted">KTP KOSONG</span>
                                <?php endif; ?>

                                <?php if ($user['selfie_path']) : ?>
                                    <button class="btn btn-sm bg-grad-5 view-selfie-btn" data-bs-toggle="modal" data-bs-target="#selfieModal" data-img="<?= htmlspecialchars($user['selfie_path']) ?>">
                                        Lihat Selfie
                                    </button>
                                <?php else : ?>
                                    <span class="text-muted">SELFIE KOSONG</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($user['verifikasi_ktp'] == 0) : ?>
                                    <a href="dashboard.php?verify_user_ktp=<?= $user['id'] ?>" class="btn bg-grad-6 btn-sm text-dark">
                                        <i class="bi bi-check-circle"></i> KTP
                                    </a>
                                <?php endif; ?>

                                <?php if ($user['verifikasi_selfie_ktp'] == 0) : ?>
                                    <a href="dashboard.php?verify_user_selfie=<?= $user['id'] ?>" class="btn bg-grad-6 btn-sm text-dark">
                                        <i class="bi bi-check-circle"></i> SELFIE
                                    </a>
                                <?php endif; ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <p class="mt-4 text-center bg-grad-3 pt-3 pb-3 text-white rounded-3">Semua pengguna sudah terverifikasi KTP.</p>
    <?php endif; ?>

    <h5 class="text-pinkv2 mt-5 mb-3">Menunggu Verifikasi Pembayaran</h5>
    <?php if (count($pendingPayments) > 0) : ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Email</th>
                        <th>Bukti Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingPayments as $payment) : ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['fullname']) ?></td>
                            <td><?= htmlspecialchars($payment['email']) ?></td>
                            <td>
                                <?php if ($payment['bukti_pembayaran']) : ?>
                                    <button class="btn btn-sm bg-grad-5 view-bukti-btn" data-bs-toggle="modal" data-bs-target="#buktiModal" data-img="<?= htmlspecialchars($payment['bukti_pembayaran']) ?>">
                                        Lihat Bukti
                                    </button>
                                <?php else : ?>
                                    <span class="text-muted">Tidak Ada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="dashboard.php?verify_payment=<?= $payment['id'] ?>" class="btn bg-grad-6 btn-sm text-dark">
                                    <i class="bi bi-check-circle"></i> Verifikasi
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <p class="mt-4 text-center bg-grad-3 pt-3 pb-3 text-white rounded-3">Tidak ada pembayaran yang menunggu verifikasi.</p>
    <?php endif; ?>


    <div class="modal fade" id="ktpModal" tabindex="-1" aria-labelledby="ktpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glassmorph shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="ktpModalLabel">Lihat KTP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="ktpImage" src="" alt="KTP" class="img-fluid rounded" style="max-height: 500px;" />
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="selfieModal" tabindex="-1" aria-labelledby="selfieModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glassmorph shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="selfieModalLabel">Lihat Selfie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="selfieImage" src="" alt="SELFIE" class="img-fluid rounded" style="max-height: 500px;" />
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="buktiModal" tabindex="-1" aria-labelledby="buktiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glassmorph shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="buktiModalLabel">Lihat Bukti Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="buktiImage" src="" alt="Bukti Pembayaran" class="img-fluid rounded" style="max-height: 500px;" />
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.view-ktp-btn').forEach(button => {
        button.addEventListener('click', function() {
            const imgSrc = this.getAttribute('data-img');
            document.getElementById('ktpImage').setAttribute('src', imgSrc);
        });
    });

    document.querySelectorAll('.view-selfie-btn').forEach(button => {
        button.addEventListener('click', function() {
            const imgSrc = this.getAttribute('data-img');
            document.getElementById('selfieImage').setAttribute('src', imgSrc);
        });
    });

    document.querySelectorAll('.view-bukti-btn').forEach(button => {
        button.addEventListener('click', function() {
            const imgSrc = this.getAttribute('data-img');
            document.getElementById('buktiImage').setAttribute('src', imgSrc);
        });
    });
</script>

<?php include "../includes/footer.php"; ?>