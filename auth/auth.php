<?php
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silahkan login terlebih dahulu.";
    header("Location: ../auth/login.php");
    exit();
}
