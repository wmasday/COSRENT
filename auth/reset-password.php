<?php
include '../includes/config.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['token'])) {
        $_SESSION['error'] = "Token tidak ditemukan.";
        header("Location: ./forgot-password.php");
        exit;
    }
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email_verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $_SESSION['error'] = "Token tidak ditemukan atau sudah kadaluarsa.";
        header("Location: ./forgot-password.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $token = $_POST['token'];

    $stmt = $conn->prepare("UPDATE users SET password=?, email_verification_token=NULL WHERE email_verification_token=?");
    $stmt->bind_param("ss", $new_password, $token);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Password berhasil diperbarui, silakan login.";
        header("Location: ./login.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal mengubah password..";
        header("Location: ./forgot-password.php");
        exit;
    }
}
?>

<div class="container" style="margin-top: 150px;margin-bottom: 100px;">
    <form class="row justify-content-center auth" method="POST" action="">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
        <div class="col-sm-4 bg-white shadow-sm rounded-3 p-5">
            <span class="open-sub-head d-block text-pinkv2">Reset Password | <?php echo $APP_NAME; ?></span>

            <div class="mt-5">
                <label>Pssword Baru</label>
                <input type="password" class="form-control" name="new_password" />
            </div>

            <button type="submit" class="btn btn-catalog mt-5 px-3 py-2 w-100" name="login">
                <i class="bi bi-rocket-takeoff me-3"></i>
                <span>Reset Password</span>
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>