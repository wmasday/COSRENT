<?php
include "../includes/config.php";
include "../includes/header.php";
include "../auth/auth.php";

if (isset($_SESSION['role']) && $_SESSION['role'] !== 'pelanggan') {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$kostum_id = $_GET['katalog_id'] ?? null;

if (!$user_id || !$kostum_id) {
    die("Data user atau katalog tidak lengkap.");
}

// Ambil ID pelanggan
$stmt = $conn->prepare("SELECT id FROM pelanggan WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$pelanggan_id = $row['id'] ?? null;
$stmt->close();

if (!$pelanggan_id) {
    die("Data pelanggan tidak ditemukan.");
}

$query = $conn->query("
    SELECT 
        k.*,
        p.rating AS rating_pelanggan,
        p.jumlah_rating AS jumlah_rating_pelanggan,
        u.fullname,
        u.no_telepon,
        u.email,
        u.provinsi AS provinsi_penyewa,
        u.kota AS kota_penyewa,
        u.profil_path,
        u.verifikasi_ktp
    FROM katalog k
    LEFT JOIN penyewa p ON k.penyewa_id = p.id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE k.id = $kostum_id AND k.visible = 1
");

if ($query->num_rows !== 1) {
    echo "Kostum tidak ditemukan atau tidak tersedia.";
    exit;
}

$kostum = $query->fetch_assoc();
$harga_format = "Rp" . number_format($kostum['harga_sewa'], 0, ',', '.');
$rating_katalog = $kostum['rating'] ?? 0;
$jumlah_rating_katalog = $kostum['jumlah_rating'] ?? 0;

$rating_pelanggan = $kostum['rating_pelanggan'] ?? 0;
$jumlah_rating_pelanggan = $kostum['jumlah_rating_pelanggan'] ?? 0;
function generateStars($rating)
{
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $starsHtml = '';

    for ($i = 0; $i < $fullStars; $i++) {
        $starsHtml .= '<i class="bi bi-star-fill text-warning"></i>';
    }

    if ($halfStar) {
        $starsHtml .= '<i class="bi bi-star-half text-warning"></i>';
    }

    for ($i = $fullStars + $halfStar; $i < 5; $i++) {
        $starsHtml .= '<i class="bi bi-star text-warning"></i>';
    }

    return $starsHtml;
}

if (isset($_POST['order'])) {
    $tanggal_sewa = $_POST['tanggal_sewa'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $catatan = $_POST['catatan'] ?? '';

    $start = new DateTime($tanggal_sewa);
    $end = new DateTime($tanggal_kembali);
    $interval = $start->diff($end)->days + 1;

    $stmt = $conn->prepare("SELECT harga_sewa FROM katalog WHERE id = ?");
    $stmt->bind_param("i", $kostum_id);
    $stmt->execute();
    $harga_sewa = $stmt->get_result()->fetch_assoc()['harga_sewa'] ?? 0;
    $stmt->close();

    $total_harga = $harga_sewa * $interval;

    $bukti_path = null;
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = "../uploads/bukti/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileTmp = $_FILES['bukti_pembayaran']['tmp_name'];
        $fileName = time() . "_" . basename($_FILES['bukti_pembayaran']['name']);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmp, $filePath)) {
            $bukti_path = $filePath;
        } else {
            $_SESSION['error'] = "Gagal upload bukti pembayaran.";
            header("Location: .");
            exit();
        }
    }

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO pesanan (
        pelanggan_id, katalog_id, tanggal_sewa, tanggal_kembali,
        total_harga, status_pembayaran, status_peminjaman,
        bukti_pembayaran, catatan
    ) VALUES (?, ?, ?, ?, ?, 'pending', 'belum_diambil', ?, ?)");

    $stmt->bind_param(
        "iissdss",
        $pelanggan_id,
        $kostum_id,
        $tanggal_sewa,
        $tanggal_kembali,
        $total_harga,
        $bukti_path,
        $catatan
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Pesanan berhasil dibuat!";
        $stmt->close();
        header("Location: ./dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal membuat pesanan!";
        $stmt->close();
        header("Location: .");
        exit();
    }
}

?>

<style>
    .detail {
        background: rgba(255, 255, 255, 0.25);
        box-shadow: rgba(17, 17, 26, 0.1) 0px 0px 16px;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
</style>

<div class="container mt-5 pt-5">
    <h4 class="text-pinkv2 pt-4 pb-3">Order Katalog</h4>
    <div class="row">
        <div class="col-sm-3 px-3 mb-3">
            <div class="card w-100">
                <img src="<?= htmlspecialchars($kostum['foto_kostum']) ?>" class="card-img-top" alt="Foto Kostum">
                <div class="card-body">
                    <a href="./katalog/index.php?keyword=<?= htmlspecialchars($kostum['karakter']); ?>" class="badge bg-character float-start py-2">
                        <i class="bi bi-person-rolodex me-1"></i>
                        <?= htmlspecialchars($kostum['karakter']) ?>
                    </a>
                    <a href="./katalog/index.php?keyword=<?= htmlspecialchars($kostum['series']); ?>" class="badge bg-series float-end py-2">
                        <i class="bi bi-tags-fill me-1"></i>
                        <?= htmlspecialchars($kostum['series']) ?>
                    </a>
                    <div class="price mt-5 mb-3">
                        <i class="bi bi-wallet2 me-2"></i>
                        <?= $harga_format ?><span> / Hari</span>
                    </div>

                    <span class="text-secondary" style="font-size: 13px;font-weight: 500;">
                        <?= htmlspecialchars($kostum['deskripsi']); ?>
                    </span>
                </div>
            </div>
        </div>

        <form class="col-sm-9" method="POST" action="" enctype="multipart/form-data">
            <div class="row detail p-3">
                <div class="col-sm-4 mt-3">
                    <label>Tanggal Sewa</label>
                    <input type="date" name="tanggal_sewa" class="form-control mt-2">
                </div>
                <div class="col-sm-4 mt-3">
                    <label>Tanggal Kembali</label>
                    <input type="date" name="tanggal_kembali" class="form-control mt-2">
                </div>
                <div class="col-sm-4 mt-3">
                    <label>Bukti Pembayaran</label>
                    <input type="file" name="bukti_pembayaran" class="form-control form-control-sm mt-3">
                </div>
                <div class="col-sm-12 mt-3">
                    <label>Catatan</label>
                    <textarea class="form-control bg-light mt-2" placeholder="Catatan" id="floatingTextarea" style="height: 330px;" name="catatan"></textarea>
                </div>
                <div class="col-sm-4 mt-3">
                    <label>Total Harga</label>
                    <input type="text" name="total_harga" class="form-control mt-2" value="Rp. 0" disabled>
                </div>

                <div class="col-sm-3 offset-sm-5 mt-4">
                    <button type="submit" name="order" class="btn btn-catalog w-100">
                        <i class="bi bi-rocket-takeoff me-3"></i>
                        <span>Order</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const tanggalSewa = document.querySelector('input[name="tanggal_sewa"]');
        const tanggalKembali = document.querySelector('input[name="tanggal_kembali"]');
        const totalHargaInput = document.querySelector('input[name="total_harga"]');

        const hargaSewaPerHari = <?= $kostum['harga_sewa']; ?>;

        function hitungTotalHarga() {
            const start = new Date(tanggalSewa.value);
            const end = new Date(tanggalKembali.value);

            if (!isNaN(start) && !isNaN(end) && end >= start) {
                const selisihWaktu = end - start;
                const jumlahHari = Math.ceil(selisihWaktu / (1000 * 60 * 60 * 24)) + 1; // +1 termasuk hari sewa dan kembali
                const total = hargaSewaPerHari * jumlahHari;

                totalHargaInput.value = "Rp. " + total.toLocaleString("id-ID");
            } else {
                totalHargaInput.value = "Rp. 0";
            }
        }

        tanggalSewa.addEventListener("change", hitungTotalHarga);
        tanggalKembali.addEventListener("change", hitungTotalHarga);
    });
</script>

<?php include '../includes/footer.php'; ?>