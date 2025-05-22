<?php

include '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$sender_id   = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$message     = trim($_POST['message']);

if ($message !== '') {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
    $stmt->execute();
}

header("Location: index.php?partner=$receiver_id");
exit;
