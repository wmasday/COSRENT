<?php

include '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../auth/login.php");
    exit;
}

$pelanggan_id = $_SESSION['user_id'];
$kostum_id = $_GET['kostum_id'];

$stmt = $conn->prepare("INSERT IGNORE INTO wishlist (pelanggan_id, kostum_id) VALUES (?, ?)");
$stmt->bind_param("ii", $pelanggan_id, $kostum_id);
$stmt->execute();
$stmt->close();

header("Location: ../katalog/detail.php?id=$kostum_id");
