<?php
include '../includes/config.php';

if (!isset($_GET['token'])) {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: ./register.php");
    exit();
}

$token = $_GET['token'];
$sql = "SELECT id FROM users WHERE email_verification_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $update = $conn->prepare("UPDATE users SET email_verified = 1, email_verification_token = NULL WHERE id = ?");
    $update->bind_param("i", $user['id']);
    $update->execute();

    $_SESSION['success'] = 'Verifikasi email berhasil, silahkan login dengan akun anda.';
    header("Location: ./login.php");
    exit();
} else {
    $_SESSION['error'] = 'Token tidak valid atau sudah digunakan.';
    header("Location: ./register.php");
    exit();
}
