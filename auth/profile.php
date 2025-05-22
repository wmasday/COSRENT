<?php
include '../includes/config.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

// Ambil data user dari DB
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$json = file_get_contents("../includes/province.json");
$data = json_decode($json, true);

if (isset($_POST['update'])) {
    $fullname = !empty($_POST['fullname']) ? $_POST['fullname'] : $$user['fullname'];
    $email = !empty($_POST['email']) ? $_POST['email'] : $user['email'];
    $bio = !empty($_POST['bio']) ? $_POST['bio'] : $user['bio'];
    $provinsi = !empty($_POST['provinsi']) ? $_POST['provinsi'] : $user['provinsi'];
    $kota = !empty($_POST['kota']) ? $_POST['kota'] : $$user['kota'];


    $uploadDir = "../uploads/ktp/";
    $newKTP = $user['ktp_path'];

    if (!empty($_FILES['ktp']['name'])) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $fileName = $_FILES['ktp']['name'];
        $tmpName = $_FILES['ktp']['tmp_name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions)) {
            $_SESSION['error'] = 'Format file tidak didukung.';
            header("Location: ./profile.php");
            exit();
        }

        $newKTP = uniqid('ktp_') . '.jpg';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        move_uploaded_file($tmpName, $uploadDir . $newKTP);
    }

    $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, bio=?, provinsi=?, kota=?, ktp_path=? WHERE id=?");
    $stmt->bind_param("ssssssi", $fullname, $email, $bio, $provinsi, $kota, $newKTP, $user_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Profil berhasil diperbarui.";
        header("Location: ./profile.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal memperbarui profil.";
    }
}
?>

<div class="container mt-5" style="padding-top: 80px;">
    <h3>Profil Saya</h3>
    <?php if (isset($_SESSION['error'])) : ?>
        <div class="alert alert-danger"><?= $_SESSION['error'];
                                        unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['info'])) : ?>
        <div class="alert alert-success"><?= $_SESSION['info'];
                                            unset($_SESSION['info']); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-sm-6 mt-3">
                <label>Nama Lengkap</label>
                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>">
            </div>
            <div class="col-sm-6 mt-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <div class="col-sm-6 mt-3">
                <label>Provinsi</label>
                <select class="form-select" name="provinsi" id="provinsiSelect">
                    <option disabled>Pilih Provinsi</option>
                    <?php foreach ($data as $item) : ?>
                        <option value="<?= $item['provinsi'] ?>" <?= $user['provinsi'] === $item['provinsi'] ? 'selected' : '' ?>>
                            <?= $item['provinsi'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 mt-3">
                <label>Kota</label>
                <select class="form-select" name="kota" id="kotaSelect">
                    <option>Pilih Kota</option>
                </select>
            </div>
            <div class="col-sm-10 mt-3">
                <label>Bio</label>
                <textarea name="bio" class="form-control" rows="6"><?= htmlspecialchars($user['bio']) ?></textarea>
            </div>
            <div class="col-sm-2 mt-3">
                <label>KTP Saat Ini</label><br>
                <img src="<?= $user['ktp_path'] ?>" alt="KTP" width="130">
            </div>
            <?php if ($user['verifikasi_ktp'] == 0) : ?>
                <div class="col-sm-12 mt-3">
                    <label>Upload KTP Baru (Opsional)</label>
                    <input type="file" name="ktp" class="form-control">
                </div>
            <?php endif; ?>
        </div>
        <div class="row justify-content-end mt-3 mb-3">
            <div class="col-sm-2">
                <button type="submit" name="update" class="btn btn-catalog px-3 py-2 w-100 d-block mt-3">
                    <i class="bi bi-rocket-takeoff me-3"></i>
                    <span>Update</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    const data = <?= json_encode($data) ?>;
    const provinsiSelect = document.getElementById('provinsiSelect');
    const kotaSelect = document.getElementById('kotaSelect');

    function populateKota(selectedProvinsi, selectedKota = '') {
        const provinsi = data.find(p => p.provinsi === selectedProvinsi);
        kotaSelect.innerHTML = '';
        if (provinsi) {
            provinsi.kota.forEach(function(kota) {
                const option = document.createElement('option');
                option.value = kota;
                option.textContent = kota;
                if (kota === selectedKota) option.selected = true;
                kotaSelect.appendChild(option);
            });
        }
    }

    provinsiSelect.addEventListener('change', function() {
        populateKota(this.value);
    });

    // Init load
    window.addEventListener('DOMContentLoaded', () => {
        populateKota("<?= $user['provinsi'] ?>", "<?= $user['kota'] ?>");
    });
</script>

<?php include '../includes/footer.php'; ?>