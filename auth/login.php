<?php
include '../includes/config.php';
include '../includes/header.php';

if (isset($_SESSION['role']) && isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        header("Location: ../admin/dashboard.php");
        exit;
    } elseif ($role === 'penyewa') {
        header("Location: ../dashboard/penyewa.php");
        exit;
    } elseif ($role === 'pelanggan') {
        header("Location: ../dashboard/pelanggan.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Password yang di masukkan tidak sesuai.";
            header("Location: ./login.php");
            exit;
        } else if ($user['email_verified'] != 1) {
            $_SESSION['info'] = "Email Anda belum diverifikasi, silakan cek email.";
            header("Location: ./login.php");
            exit;
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['provinsi'] = $user['provinsi'];
            $_SESSION['kota'] = $user['kota'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['profil_path'] = $user['profil_path'] ?? null;

            $_SESSION['success'] = "Login berhasil.";
            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
                exit;
            } elseif ($user['role'] === 'penyewa') {
                header("Location: ../penyewa/dashboard.php");
                exit;
            } else {
                header("Location: ../pelanggan/dashboard.php");
                exit;
            }

            exit;
        }
    } else {
        $_SESSION['info'] = "Email tidak dapat di temukan.";
        header("Location: ./login.php");
        exit;
    }

    $stmt->close();
}
?>

<div class="container" style="margin-top: 150px;">
    <form class="row justify-content-center auth" method="POST" action="">
        <div class="col-sm-4 bg-white shadow-sm rounded-3 p-5">
            <span class="open-sub-head d-block text-pinkv2">Login | <?php echo $APP_NAME; ?></span>

            <div class="mt-5">
                <label>Email</label>
                <input type="email" class="form-control" name="email" />
            </div>
            <div class="mt-3">
                <label>Password</label>
                <input type="password" class="form-control" name="password" />
            </div>
            <button type="submit" class="btn btn-catalog mt-5 px-3 py-2 w-100" name="login">
                <i class="bi bi-rocket-takeoff me-3"></i>
                <span>Login</span>
            </button>

            <div class="text-center mt-4">
                <a href="./register.php" class="text-decoration-none text-pink d-block">
                    Tidak Memiliki Akun? Daftar Disini.
                </a>
                <a href="./forgot-password.php" class="text-decoration-none text-secondary">
                    Atau Lupa Password
                </a>
            </div>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>