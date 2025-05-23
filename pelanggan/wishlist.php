<?php

include '../includes/config.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silahkan login terlebih dahulu.";
    header("Location: ./login.php");
    exit();
}

if ($_SESSION['role'] !== "pelanggan") {
    $_SESSION['info'] = "Hanya pelanggan yang dapat membuat wishlist";
    header("Location: ../../index.php");
    exit();
}

$sql = "INSERT INTO wishlist (penyewa_id, pelanggan_id, katalog_id)
        VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $penyewa_id, $pelanggan_id, $katalog_id);

if ($stmt->execute()) {
    $verificationLink = $BASE_URL . "/auth/verify-email.php?token=" . $token;
    $subject = "Verifikasi Email Anda - " . $APP_NAME;
    $message = "Halo $fullname,\n\nSilakan klik link berikut untuk verifikasi email Anda:\n\n$verificationLink\n\nTerima kasih.";
    sendSMTPMail($email, $fullname, $subject, $message);
    $_SESSION['info'] = "Registrasi berhasil, silahkan lakukan verifikasi email yang kami kirimkan.";
    header("Location: ./login.php");
    exit;
} else {
    $_SESSION['error'] = "Kesalahan terjadi saat melakukan registrasi.";
    header("Location: ./register.php");
    exit;
}

$stmt->close();
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