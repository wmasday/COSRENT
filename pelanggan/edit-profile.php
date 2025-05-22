<?php

include '../includes/config.php';

$user_id  = $_SESSION['user_id'];
$username = $_POST['username'];
$email    = $_POST['email'];
$provinsi = $_POST['provinsi'];
$kota     = $_POST['kota'];
$bio      = $_POST['bio'];

$password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

// Jika upload KTP baru (hanya jika belum verifikasi)
$ktp_path = null;
if (isset($_FILES['ktp']) && $_FILES['ktp']['size'] > 0) {
    $ktp_file = $_FILES['ktp']['name'];
    $tmp_name = $_FILES['ktp']['tmp_name'];
    $ktp_path = "uploads/ktp/" . time() . "_" . basename($ktp_file);

    if (!move_uploaded_file($tmp_name, $ktp_path)) {
        die("Gagal mengunggah KTP.");
    }

    // Jika upload ulang, reset verifikasi_ktp
    $conn->query("UPDATE users SET verifikasi_ktp = 0 WHERE id = $user_id");
}

// Update query dinamis
$update_fields = "username=?, email=?, provinsi=?, kota=?, bio=?";
$params = [$username, $email, $provinsi, $kota, $bio];
$types  = "sssss";

if ($password) {
    $update_fields .= ", password=?";
    $params[] = $password;
    $types   .= "s";
}

if ($ktp_path) {
    $update_fields .= ", ktp_path=?";
    $params[] = $ktp_path;
    $types   .= "s";
}

$params[] = $user_id;
$types   .= "i";

$sql = "UPDATE users SET $update_fields WHERE id=?";
$stmt = $conn->prepare("UPDATE users SET $update_fields WHERE id=?");
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo "Profil berhasil diperbarui.";
    // Redirect jika perlu
} else {
    echo "Gagal memperbarui profil: " . $stmt->error;
}
