<?php
include '../includes/config.php';
include '../includes/header.php';

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'penyewa'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $penyewa = $result->fetch_assoc();
} else {
    echo "Penyewa tidak ditemukan.";
    exit;
}
?>

<h2>Profil Penyewa: <?= htmlspecialchars($penyewa['username']) ?></h2>
<p>Email: <?= htmlspecialchars($penyewa['email']) ?></p>
<p>Provinsi: <?= htmlspecialchars($penyewa['provinsi']) ?></p>
<p>Kota: <?= htmlspecialchars($penyewa['kota']) ?></p>
<p>Bio: <?= nl2br(htmlspecialchars($penyewa['bio'])) ?></p>

<?php include '../includes/footer.php'; ?>