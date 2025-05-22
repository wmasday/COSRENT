<?php

include '../includes/config.php';

// Cek apakah admin login (opsional)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

// Jika admin klik verifikasi
if (isset($_GET['verifikasi_id'])) {
    $id = intval($_GET['verifikasi_id']);
    $conn->query("UPDATE users SET verifikasi_ktp = 1 WHERE id = $id");
    header("Location: verifikasi-kyc.php");
    exit;
}

// Jika admin klik tolak
if (isset($_GET['tolak_id'])) {
    $id = intval($_GET['tolak_id']);
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: verifikasi-kyc.php");
    exit;
}

// Ambil daftar user yang belum verifikasi KTP
$result = $conn->query("SELECT * FROM users WHERE verifikasi_ktp = 0");

?>

<h2>Verifikasi KTP (KYC)</h2>
<table border="1" cellpadding="8">
    <tr>
        <th>Nama</th>
        <th>Email</th>
        <th>Role</th>
        <th>KTP</th>
        <th>Bio</th>
        <th>Aksi</th>
    </tr>

    <?php while ($user = $result->fetch_assoc()) : ?>
        <tr>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td><a href="../<?= $user['ktp_path'] ?>" target="_blank">Lihat KTP</a></td>
            <td><?= nl2br(htmlspecialchars($user['bio'])) ?></td>
            <td>
                <a href="?verifikasi_id=<?= $user['id'] ?>" onclick="return confirm('Verifikasi KTP?')">Verifikasi</a> |
                <a href="?tolak_id=<?= $user['id'] ?>" onclick="return confirm('Tolak dan hapus pengguna ini?')">Tolak</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>