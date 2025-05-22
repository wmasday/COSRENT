<?php
session_start();
$BASE_URL = "http://localhost:1337";
$APP_NAME = "COSRENT";
$host = "localhost:8484";
$user = "root";
$pass = "root";
$dbname = "db_cosrent";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
