<?php

include '../includes/config.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Cek verifikasi KYC
$cek = $conn->query("SELECT verifikasi_ktp FROM users WHERE id = $user_id");
$row = $cek->fetch_assoc();
if ($row['verifikasi_ktp'] != 1) {
    echo "<p>Anda belum terverifikasi KTP. Silakan tunggu verifikasi dari admin.</p>";
    exit;
}

// Ambil daftar pengguna yang sudah pernah mengirim / menerima pesan
$chatList = $conn->query("
    SELECT u.id, u.username FROM users u
    WHERE u.id != $user_id AND (
        u.id IN (SELECT sender_id FROM messages WHERE receiver_id = $user_id)
        OR u.id IN (SELECT receiver_id FROM messages WHERE sender_id = $user_id)
    )
");

// Ambil pesan jika ada partner dipilih
$partner_id = isset($_GET['partner']) ? intval($_GET['partner']) : 0;
$messages = [];
if ($partner_id) {
    $query = "SELECT * FROM messages 
              WHERE (sender_id = $user_id AND receiver_id = $partner_id)
                 OR (sender_id = $partner_id AND receiver_id = $user_id)
              ORDER BY timestamp ASC";
    $messages = $conn->query($query);
}
?>

<h2>Chat</h2>

<div style="display: flex;">
    <!-- Sidebar -->
    <div style="width: 30%; border-right: 1px solid #ccc; padding-right: 10px;">
        <h4>Daftar Percakapan</h4>
        <ul>
            <?php while ($c = $chatList->fetch_assoc()) : ?>
                <li><a href="?partner=<?= $c['id'] ?>"><?= htmlspecialchars($c['username']) ?></a></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <!-- Chat Box -->
    <div style="width: 70%; padding-left: 10px;">
        <?php if ($partner_id) : ?>
            <div style="height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                <?php while ($msg = $messages->fetch_assoc()) : ?>
                    <p><strong><?= $msg['sender_id'] == $user_id ? 'Anda' : 'Mereka' ?>:</strong> <?= htmlspecialchars($msg['message']) ?><br><small><?= $msg['timestamp'] ?></small></p>
                <?php endwhile; ?>
            </div>

            <form method="POST" action="send.php">
                <input type="hidden" name="receiver_id" value="<?= $partner_id ?>">
                <textarea name="message" required placeholder="Ketik pesan..." rows="3" style="width: 100%;"></textarea>
                <button type="submit">Kirim</button>
            </form>
        <?php else : ?>
            <p>Pilih pengguna untuk mulai chat.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>