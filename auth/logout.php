<?php
include "../includes/config.php";
session_unset();
session_destroy();
$_SESSION['success'] = 'Logout berhasil.';
header("Location: ../index.php");
exit;
