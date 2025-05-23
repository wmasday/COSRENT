<?php
include './includes/config.php';
include './auth/auth.php';

if (isset($_GET['katalog_id'])) {
    $user_id = $_SESSION['user_id'];
    $katalog_id = intval($_GET['katalog_id']);
    $sql = "SELECT id FROM wishlist WHERE katalog_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $katalog_id, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $sql = "DELETE FROM wishlist WHERE katalog_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $katalog_id, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Berhasil menghapus dari wishlist.";
        } else {
            $_SESSION['error'] = "Gagal menghapus dari wishlist.";
        }
    } else {
        $sql = "INSERT INTO wishlist (katalog_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $katalog_id, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Berhasil menambahkan ke wishlist.";
        } else {
            $_SESSION['error'] = "Gagal menambahkan ke wishlist.";
        }
    }
    $stmt->close();
    header("Location: ./index.php");
    exit;
}
