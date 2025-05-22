<?php

include 'includes/config.php';
include 'includes/header.php';

$result = $conn->query("SELECT id, username, email, kota, provinsi, bio FROM users WHERE role = 'penyewa'");
?>

<h2>Daftar Penyewa Terdaftar</h2>

<?php if ($result->num_rows > 0) : ?>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Kota</th>
                <th>Provinsi</th>
                <th>Bio</th>
                <th>Profil</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($penyewa = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?= htmlspecialchars($penyewa['username']) ?></td>
                    <td><?= htmlspecialchars($penyewa['email']) ?></td>
                    <td><?= htmlspecialchars($penyewa['kota']) ?></td>
                    <td><?= htmlspecialchars($penyewa['provinsi']) ?></td>
                    <td><?= nl2br(htmlspecialchars($penyewa['bio'])) ?></td>
                    <td><a href="penyewa/profil.php?id=<?= $penyewa['id'] ?>">Lihat Profil</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else : ?>
    <p>Belum ada penyewa yang terdaftar.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>