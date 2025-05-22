<?php

include '../includes/config.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id   = $_SESSION['user_id'];
$kostum_id = $_GET['kostum_id'] ?? 0;

$result = $conn->query("SELECT * FROM kostum WHERE id = $kostum_id");
$kostum = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal_sewa = $_POST['tanggal_sewa'];
    $lama_sewa    = $_POST['lama_sewa'];
    $total_harga  = $kostum['harga_per_hari'] * $lama_sewa;

    $bukti = $_FILES['bukti']['name'];
    $tmp   = $_FILES['bukti']['tmp_name'];
    $path  = '../uploads/bukti/' . time() . '_' . basename($bukti);

    if (move_uploaded_file($tmp, $path)) {
        $stmt = $conn->prepare("INSERT INTO rentals (user_id, kostum_id, tanggal_sewa, lama_sewa, total_harga, bukti_transfer) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisiss", $user_id, $kostum_id, $tanggal_sewa, $lama_sewa, $total_harga, $path);
        $stmt->execute();
        echo "Pembayaran berhasil dikirim. Tunggu verifikasi admin.";
    } else {
        echo "Upload bukti transfer gagal.";
    }
    exit;
}
?>

<h2>Checkout Kostum</h2>
<p><strong><?= $kostum['judul'] ?></strong></p>
<p>Harga per hari: Rp<?= number_format($kostum['harga_per_hari']) ?></p>

<form method="POST" enctype="multipart/form-data">
    <label>Tanggal Sewa:</label>
    <input type="date" name="tanggal_sewa" required><br>

    <label>Lama Sewa (hari):</label>
    <input type="number" name="lama_sewa" min="1" required><br>

    <label>Upload Bukti Transfer:</label>
    <input type="file" name="bukti" required><br><br>

    <button type="submit">Kirim Pembayaran</button>
</form>

<p>Transfer ke: BCA 123456789 a.n. Cosrent Lampung</p>

<?php include '../includes/footer.php'; ?>