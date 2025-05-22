<?php

include '../includes/config.php';
include '../includes/header.php';

// Pastikan penyewa login dan KYC telah diverifikasi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyewa') {
    header("Location: ../auth/login.php");
    exit;
}

// Cek status verifikasi KTP
$userId = $_SESSION['user_id'];
$check = $conn->query("SELECT verifikasi_ktp FROM users WHERE id = $userId");
$verifikasi = $check->fetch_assoc();

if ($verifikasi['verifikasi_ktp'] != 1) {
    echo "<p>Fitur chat hanya tersedia jika KYC Anda telah diverifikasi.</p>";
    include '../includes/footer.php';
    exit;
}

// Ambil daftar percakapan (grouped by pelanggan)
$chats = $conn->query("
    SELECT DISTINCT u.id AS pelanggan_id, u.username AS pelanggan_nama
    FROM chat c
    JOIN users u ON c.pelanggan_id = u.id
    WHERE c.penyewa_id = $userId
    ORDER BY c.created_at DESC
");
?>

<div class="container">
    <h2>Chat dengan Pelanggan</h2>

    <?php if ($chats->num_rows > 0) : ?>
        <ul>
            <?php while ($row = $chats->fetch_assoc()) : ?>
                <li>
                    <a href="../chat/view.php?pelanggan_id=<?= $row['pelanggan_id'] ?>">
                        <?= htmlspecialchars($row['pelanggan_nama']) ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p>Belum ada percakapan.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>