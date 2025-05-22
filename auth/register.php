<?php
include '../includes/config.php';
include '../includes/header.php';
include '../includes/mail.php';

$json = file_get_contents("../includes/province.json");
$data = json_decode($json, true);

if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $c_password = $_POST['c_password'];
    $role     = $_POST['role'];
    $provinsi = $_POST['provinsi'];
    $kota     = $_POST['kota'];
    $bio      = $_POST['bio'];

    if ($password != $c_password) {
        $_SESSION['error'] = 'Password dan Konfirmasi Password tidak sama!';
        header("Location: ./register.php");
        exit();
    } else {
        $password = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($role === 'penyewa' && !isset($_POST['term'])) {
        $_SESSION['error'] = 'Anda harus menyetujui syarat dan ketentuan sebagai penyewa.';
        header("Location: ./register.php");
        exit();
    }


    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    $ktp_file = $_FILES['ktp']['name'];
    $tmp_name = $_FILES['ktp']['tmp_name'];
    $uploadDir = "../uploads/ktp/";
    $secureIt = uniqid('ktp_') . ".jpg";

    $fileExt = strtolower(pathinfo($ktp_file, PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedExtensions)) {
        $_SESSION['error'] = 'File upload not allowed.';
        header("Location: ./register.php");
        exit();
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!move_uploaded_file($tmp_name, $secureIt)) {
        $_SESSION['error'] = 'Upload KTP gagal.';
        header("Location: ./register.php");
        exit();
    }
    $target = $uploadDir . $secureIt;
    $token = bin2hex(random_bytes(32));

    $sql = "INSERT INTO users (fullname, email, password, role, ktp_path, provinsi, kota, bio, verifikasi_ktp, email_verified, email_verification_token)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $fullname, $email, $password, $role, $target, $provinsi, $kota, $bio, $token);

    if ($stmt->execute()) {
        $verificationLink = $BASE_URL . "/auth/verify-email.php?token=" . $token;
        $subject = "Verifikasi Email Anda - " . $APP_NAME;
        $message = "Halo $fullname,\n\nSilakan klik link berikut untuk verifikasi email Anda:\n\n$verificationLink\n\nTerima kasih.";
        sendSMTPMail($email, $fullname, $subject, $message);
        $_SESSION['info'] = "Registrasi berhasil, silahkan lakukan verifikasi email yang kami kirimkan.";
        header("Location: ./login.php");
        exit;
    } else {
        $_SESSION['error'] = "Kesalahan terjadi saat melakukan registrasi.";
        header("Location: ./register.php");
        exit;
    }

    $stmt->close();
}
?>

<div class="container" style="margin-top: 120px;">
    <form class="row auth bg-white shadow-sm rounded-3 p-5" method="POST" action="" enctype="multipart/form-data">
        <span class="open-sub-head d-block text-pinkv2">Daftar | <?php echo $APP_NAME; ?></span>
        <div class="col-sm-4">
            <div class="mt-3">
                <label>Nama Lengkap</label>
                <input type="text" class="form-control" name="fullname" />
            </div>
            <div class="mt-3">
                <label>Email</label>
                <input type="email" class="form-control" name="email" />
            </div>
            <div class="mt-3">
                <label>Password</label>
                <input type="password" class="form-control" name="password" />

            </div>
            <div class="mt-3">
                <label>Confirm Password</label>
                <input type="password" class="form-control" name="c_password" />
            </div>
        </div>

        <div class="col-sm-4">
            <div class="mt-3">
                <label>Province</label>
                <select class="form-select" id="provinsiSelect" name="provinsi">
                    <option selected disabled>Pilih Provinsi</option>
                    <?php foreach ($data as $item) : ?>
                        <option value="<?= htmlspecialchars($item['provinsi']) ?>"><?= htmlspecialchars($item['provinsi']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mt-3">
                <label>Kota</label>
                <select class="form-select" id="kotaSelect" name="kota">
                    <option selected disabled>Pilih Kota</option>
                </select>
            </div>

            <div class="mt-3">
                <label>Upload KTP <span class="text-danger" style="font-size: 10px;">* Verifikasi Identitas</span></label>
                <input type="file" class="form-control" name="ktp" />
            </div>

            <div class="mt-3">
                <label>Daftar Sebagai</label>
                <select class="form-select" id="roleSelect" name="role">
                    <option selected disabled>Pilih Peran</option>
                    <option value="penyewa">Penyewa</option>
                    <option value="penyewa">Pelanggan</option>
                </select>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="mt-3">
                <label>Bio Profile</label>
                <div class="form-floating">
                    <textarea class="form-control bg-light" placeholder="Bio" id="floatingTextarea" style="height: 250px;" name="bio"></textarea>
                </div>
            </div>
        </div>
        <div class="col-sm-3 offset-sm-9 text-end">
            <div class="form-check mt-3 mb-4">
                <input class="form-check-input" type="checkbox" id="termsCheck" name="term">
                <label class="form-check-label" for="termsCheck">
                    Saya menyetujui <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Syarat & Ketentuan</a>
                </label>
            </div>

            <button type="submit" name="register" id="submitBtn" class="btn btn-catalog px-3 py-2 w-100 d-block mt-3" disabled>
                <i class="bi bi-rocket-takeoff me-3"></i>
                <span>Register</span>
            </button>
        </div>
    </form>
</div>
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Syarat & Ketentuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p>
                    Isi syarat dan ketentuan di sini. Contohnya:
                </p>
                <ul>
                    <li>Kostum harus dikembalikan dalam keadaan bersih.</li>
                    <li>Penyewaan maksimal 3 hari.</li>
                    <li>Denda akan dikenakan jika ada kerusakan.</li>
                </ul>
                <p>Harap baca dan pahami sebelum menyetujui.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<script>
    const data = <?= json_encode($data) ?>;

    const provinsiSelect = document.getElementById('provinsiSelect');
    const kotaSelect = document.getElementById('kotaSelect');

    provinsiSelect.addEventListener('change', function() {
        const selectedProvinsi = this.value;
        const provinsiData = data.find(p => p.provinsi === selectedProvinsi);

        // Kosongkan dan aktifkan kotaSelect
        kotaSelect.innerHTML = '';
        kotaSelect.disabled = false;

        if (provinsiData) {
            provinsiData.kota.forEach(function(kota) {
                const option = document.createElement('option');
                option.value = kota;
                option.textContent = kota;
                kotaSelect.appendChild(option);
            });
        }
    });

    document.getElementById('termsCheck').addEventListener('change', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = !this.checked;
    });
</script>

<?php include '../includes/footer.php'; ?>