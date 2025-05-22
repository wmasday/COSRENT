<?php

include '../includes/config.php';

$user_id = $_SESSION['user_id'];
$query = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $query->fetch_assoc();
?>

<h2>Edit Profil</h2>
<form method="POST" action="edit-profile.php" enctype="multipart/form-data">
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

    <label>Password Baru (opsional)</label>
    <input type="password" name="password">

    <input type="text" name="provinsi" value="<?= htmlspecialchars($user['provinsi']) ?>">
    <input type="text" name="kota" value="<?= htmlspecialchars($user['kota']) ?>">
    <textarea name="bio"><?= htmlspecialchars($user['bio']) ?></textarea>

    <?php if (!$user['verifikasi_ktp']) : ?>
        <label>Upload ulang KTP (belum diverifikasi)</label>
        <input type="file" name="ktp" accept="image/*">
    <?php else : ?>
        <p>KTP sudah terverifikasi ✅</p>
    <?php endif; ?>

    <button type="submit" name="submit">Simpan Perubahan</button>
</form>