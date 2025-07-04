<?php
include '../includes/config.php';
include '../includes/header.php';
include './auth.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$json = file_get_contents("../includes/province.json");
$data = json_decode($json, true);

function handleFileUpload($fieldName, $uploadDir, $prefix = 'file_', $oldFile = null)
{
    if (!empty($_FILES[$fieldName]['name'])) {
        $allowedExtensions = ['jpg', 'png'];
        $fileName = $_FILES[$fieldName]['name'];
        $tmpName = $_FILES[$fieldName]['tmp_name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions)) {
            $_SESSION['error'] = 'Format file tidak didukung.';
            header("Location: ./profile.php");
            exit();
        }

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $newFileName = uniqid($prefix) . '.' . $ext;
        $newFilePath = $uploadDir . $newFileName;

        if ($oldFile && file_exists($oldFile)) {
            unlink($oldFile);
        }

        move_uploaded_file($tmpName, $newFilePath);
        return $newFilePath;
    }
    return null;
}

if (isset($_POST['update'])) {
    $fullname = $_POST['fullname'] ?? $user['fullname'];
    $email = $_POST['email'] ?? $user['email'];
    $bio = $_POST['bio'] ?? $user['bio'];
    $provinsi = $_POST['provinsi'] ?? $user['provinsi'];
    $kota = $_POST['kota'] ?? $user['kota'];
    $no_telepon = $_POST['no_telepon'] ?? $user['no_telepon'];
    $alamat = $_POST['alamat'] ?? $user['alamat'];

    $ktpPath = $user['ktp_path'];
    $selfiePath = $user['selfie_path'];
    $profilPath = $user['profil_path'];

    if ($user['verifikasi_selfie_ktp'] == 0) {
        $uploadedSelfie = handleFileUpload('selfie', '../uploads/selfie/', 'selfie_', $selfiePath);
        if ($uploadedSelfie) {
            $selfiePath = $uploadedSelfie;
        }
    }

    if ($user['verifikasi_ktp'] == 0) {
        $uploadedKTP = handleFileUpload('ktp', '../uploads/ktp/', 'ktp_', $ktpPath);
        if ($uploadedKTP) {
            $ktpPath = $uploadedKTP;
        }
    }

    $uploadedProfil = handleFileUpload('profil_path', '../uploads/profile/', 'profile_', $profilPath);
    if ($uploadedProfil) {
        $profilPath = $uploadedProfil;
    }

    $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, bio=?, provinsi=?, kota=?, ktp_path=?, selfie_path=?, profil_path=?, alamat=?, no_telepon=? WHERE id=?");
    $stmt->bind_param("ssssssssssi", $fullname, $email, $bio, $provinsi, $kota, $ktpPath, $selfiePath, $profilPath, $alamat, $no_telepon, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profil berhasil diperbarui.";
        header("Location: ./profile.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal memperbarui profil.";
        header("Location: ./profile.php");
        exit();
    }
}
?>



<div class="container mt-5" style="padding-top: 80px;">
    <h3 class="text-pinkv2">Profil Saya</h3>

    <form method="POST" enctype="multipart/form-data" class="auth">
        <div class="row">
            <div class="col-sm-6 mt-3">
                <label>Nama Lengkap</label>
                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>">
            </div>

            <div class="col-sm-6 mt-3">
                <label>NIK</label>
                <input type="text" name="nik" class="form-control" value="<?= htmlspecialchars($user['nik']) ?>" readonly>
            </div>

            <div class="col-sm-6 mt-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <div class="col-sm-6 mt-3">
                <label>Telp</label>
                <input type="text" name="no_telpon" class="form-control" value="<?= htmlspecialchars($user['no_telepon']) ?>">
            </div>
            <div class="col-sm-6 mt-3">
                <label>Alamat</label>
                <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($user['alamat']) ?>">
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
            <div class="col-sm-8 mt-3">
                <label>Bio</label>
                <textarea name="bio" class="form-control" rows="6"><?= htmlspecialchars($user['bio']) ?></textarea>
            </div>
            <div class="col-sm-2 mt-3">
                <label class="mb-2">Selfie KTP Saat Ini</label><br>
                <img src="<?= $user['selfie_path'] ?>" alt="selfie" width="130">
            </div>
            <div class="col-sm-2 mt-3">
                <label class="mb-2">KTP Saat Ini</label><br>
                <img src="<?= $user['ktp_path'] ?>" alt="KTP" width="130">
            </div>
            <?php if ($user['verifikasi_ktp'] == 0) : ?>
                <div class="col-sm-12 mt-3">
                    <label>Upload KTP Baru (Opsional) <span class="text-danger" style="font-size: 10px;">* jpg/png</span></label>
                    <input type="file" name="ktp" class="form-control">
                </div>
            <?php endif; ?>

            <?php if ($user['verifikasi_selfie_ktp'] == 0) : ?>
                <div class="col-sm-12 mt-3">
                    <label>Upload Selfie KTP Baru (Opsional) <span class="text-danger" style="font-size: 10px;">* jpg/png</span></label>
                    <input type="file" name="selfie" class="form-control">
                </div>
            <?php endif; ?>
            <div class="col-sm-12 mt-3">
                <label>Ganti Profile <span class="text-danger" style="font-size: 10px;">* (jpg/png)</span></label>
                <input type="file" name="profile" class="form-control">
            </div>
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

    window.addEventListener('DOMContentLoaded', () => {
        populateKota("<?= $user['provinsi'] ?>", "<?= $user['kota'] ?>");
    });
</script>

<?php include '../includes/footer.php'; ?>