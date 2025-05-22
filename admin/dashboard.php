<?php

include '../includes/config.php';
include '../includes/header.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil statistik sederhana
$jumlahPengguna = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$belumVerifikasiKTP = $conn->query("SELECT COUNT(*) as total FROM users WHERE verifikasi_ktp = 0")->fetch_assoc()['total'];
$jumlahKostum = $conn->query("SELECT COUNT(*) as total FROM kostum")->fetch_assoc()['total'];

?>

<div class="container">
    <h2>Dashboard Admin</h2>

    <div class="statistik">
        <div>Total Pengguna: <?= $jumlahPengguna ?></div>
        <div>Belum Verifikasi KTP: <?= $belumVerifikasiKTP ?></div>
        <div>Total Kostum: <?= $jumlahKostum ?></div>
    </div>

    <h3>Pengguna Menunggu Verifikasi KTP</h3>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>KTP</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM users WHERE verifikasi_ktp = 0");
            while ($row = $result->fetch_assoc()) :
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><a href="../<?= $row['ktp_path'] ?>" target="_blank">Lihat KTP</a></td>
                    <td>
                        <a href="verifikasi-kyc.php?aksi=terima&id=<?= $row['id'] ?>">Terima</a> |
                        <a href="verifikasi-kyc.php?aksi=tolak&id=<?= $row['id'] ?>">Tolak</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
header("Location: ../auth/login.php");
exit;
}