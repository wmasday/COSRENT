<?php
include '../includes/config.php';
include '../includes/header.php';
include '../includes/mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $token = bin2hex(random_bytes(32));
        $conn->query("UPDATE users SET email_verification_token='$token' WHERE email='$email'");

        $resetLink = $BASE_URL . "/auth/reset-password.php?token=" . $token;
        $subject = "Reset Password - " . $APP_NAME;
        $message = "Klik link berikut untuk reset password Anda:\n\n$resetLink";
        sendSMTPMail($email, "", $subject, $message);

        $_SESSION['success'] = "Token reset password berhasil di kirimkan ke " . $email;
        header("Location: ./forgot-password.php");
        exit;
    } else {
        $_SESSION['error'] = "Email " . $email . " tidak dapat ditemukan.";
        header("Location: ./forgot-password.php");
        exit;
    }
}
?>

<div class="container" style="margin-top: 150px;margin-bottom: 100px;">
    <form class="row justify-content-center auth" method="POST" action="">
        <div class="col-sm-4 bg-white shadow-sm rounded-3 p-5">
            <span class="open-sub-head d-block text-pinkv2">Lupa Password | <?php echo $APP_NAME; ?></span>

            <div class="mt-5">
                <label>Email</label>
                <input type="email" class="form-control" name="email" />
            </div>

            <button type="submit" class="btn btn-catalog mt-5 px-3 py-2 w-100" name="login">
                <i class="bi bi-rocket-takeoff me-3"></i>
                <span>Reset Password</span>
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>