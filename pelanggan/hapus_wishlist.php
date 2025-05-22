<?php

include '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../auth/login.php");
    exit;
}

$wishlist_id = $_GET['id'];
$conn->query("DELETE FROM wishlist WHERE id = $wishlist_id");

header("Location: wishlist.php");
