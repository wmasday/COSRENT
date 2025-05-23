<?php

include '../includes/config.php';
include '../includes/header.php';
include '../auth/auth.php';

$json = file_get_contents("../includes/province.json");
$data = json_decode($json, true);

$user_id = $_SESSION['user_id'];

// Ambil id penyewa
$stmtPenyewa = $conn->prepare("SELECT id FROM penyewa WHERE user_id = ?");
$stmtPenyewa->bind_param("i", $user_id);
$stmtPenyewa->execute();
$res = $stmtPenyewa->get_result();
$penyewa = $res->fetch_assoc();
$penyewa_id = $penyewa['id'] ?? null;

if (!$penyewa_id) {
    die("Penyewa tidak ditemukan.");
}

function handleFileUpload($fieldName, $uploadDir, $prefix = 'file_', $oldFile = null)
{
    if (!empty($_FILES[$fieldName]['name'])) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $fileName = $_FILES[$fieldName]['name'];
        $tmpName = $_FILES[$fieldName]['tmp_name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions)) {
            $_SESSION['error'] = 'Format file tidak didukung.';
            exit();
        }

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $newFileName = uniqid($prefix) . '.' . $ext;
        $newFilePath = $uploadDir . $newFileName;

        if ($oldFile && file_exists($oldFile)) {
            unlink($oldFile);  // Uncomment this to delete old file
        }

        move_uploaded_file($tmpName, $newFilePath);
        return $newFilePath;
    }
    return null;
}

$kostum_id = $_GET['id'] ?? null;

if (!$kostum_id) {
    die("ID kostum tidak ditemukan.");
}

// Ambil data lama
$stmtOld = $conn->prepare("SELECT * FROM katalog WHERE id = ? AND penyewa_id = ?");
$stmtOld->bind_param("ii", $kostum_id, $penyewa_id);
$stmtOld->execute();
$resultOld = $stmtOld->get_result();
$katalog = $resultOld->fetch_assoc();

if (!$katalog) {
    die("Katalog tidak ditemukan atau Anda tidak memiliki akses.");
}

if (isset($_POST['submit'])) {
    $nama_kostum = $_POST['nama_kostum'];
    $series = $_POST['series'];
    $karakter = $_POST['karakter'];
    $ukuran = $_POST['ukuran'];
    $gender = $_POST['gender'];
    $kategori = $_POST['kategori'];
    $harga_sewa = $_POST['harga_sewa'];
    $provinsi = $_POST['provinsi'];
    $kota = $_POST['kota'];
    $status = $_POST['status'];
    $visible = $_POST['visible'];
    $deskripsi = $_POST['deskripsi'];

    $foto_kostum = $katalog['foto_kostum'];
    $uploadedKostum = handleFileUpload('foto_kostum', '../uploads/katalog/', 'kostum_', $foto_kostum);
    if ($uploadedKostum) {
        $foto_kostum = $uploadedKostum;
    }

    $stmt = $conn->prepare("UPDATE katalog SET nama_kostum=?, series=?, karakter=?, ukuran=?, gender=?, deskripsi=?, kategori=?, harga_sewa=?, kota=?, provinsi=?, status=?, visible=?, foto_kostum=? WHERE id=? AND penyewa_id=?");
    $stmt->bind_param(
        "sssssssdsssssii",
        $nama_kostum,
        $series,
        $karakter,
        $ukuran,
        $gender,
        $deskripsi,
        $kategori,
        $harga_sewa,
        $kota,
        $provinsi,
        $status,
        $visible,
        $foto_kostum,
        $kostum_id,
        $penyewa_id
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Katalog berhasil diperbarui.";
        header("Location: ../penyewa/dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal memperbarui katalog.";
        header("Location: ./edit-kostum.php?id=$kostum_id");
        exit();
    }
}
?>

<div class="container mt-5 pt-5">
    <h3 class="text-pinkv2 auth pt-3">Edit Katalog</h3>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="katalog_id" value="<?= $katalog['id'] ?>">

        <div class="row">
            <div class="col-sm-3 mt-3">
                <label>Kategori Katalog</label>
                <select class="form-select mt-2" name="kategori" required>
                    <option disabled>Pilih Kategori</option>
                    <?php
                    $kategori_list = ['Kostum', 'Wig Only', 'Sepatu', 'Weapon'];
                    foreach ($kategori_list as $kat) {
                        $selected = ($kat === $katalog['kategori']) ? 'selected' : '';
                        echo "<option value=\"$kat\" $selected>$kat</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-sm-3 mt-3">
                <label>Status Katalog</label>
                <select class="form-select mt-2" name="status" required>
                    <?php
                    $status_list = ['tersedia' => 'Tersedia', 'disewa' => 'Di Sewa', 'maintenance' => 'Maintenance'];
                    foreach ($status_list as $value => $label) {
                        $selected = ($value === $katalog['status']) ? 'selected' : '';
                        echo "<option value=\"$value\" $selected>$label</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-sm-3 mt-3">
                <label>Nama Kostum</label>
                <input type="text" name="nama_kostum" class="form-control mt-2" value="<?= htmlspecialchars($katalog['nama_kostum']) ?>">
            </div>

            <div class="col-sm-3 mt-3">
                <label>Series Kostum</label>
                <input type="text" name="series" class="form-control mt-2" value="<?= htmlspecialchars($katalog['series']) ?>">
            </div>

            <div class="col-sm-3 mt-3">
                <label>Karakter Kostum</label>
                <input type="text" name="karakter" class="form-control mt-2" value="<?= htmlspecialchars($katalog['karakter']) ?>">
            </div>

            <div class="col-sm-3 mt-3">
                <label>Ukuran Kostum</label>
                <input type="text" name="ukuran" class="form-control mt-2" value="<?= htmlspecialchars($katalog['ukuran']) ?>">
            </div>

            <div class="col-sm-3 mt-3">
                <label>Gender</label>
                <select class="form-select mt-2" name="gender" required>
                    <?php
                    $gender_list = ['Pria', 'Wanita', 'Unisex'];
                    foreach ($gender_list as $gender) {
                        $selected = ($gender === $katalog['gender']) ? 'selected' : '';
                        echo "<option value=\"$gender\" $selected>$gender</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-sm-3 mt-3">
                <label>Province</label>
                <select class="form-select mt-2" id="provinsiSelect" name="provinsi" required>
                    <option disabled>Pilih Provinsi</option>
                    <?php foreach ($data as $item) : ?>
                        <?php $selected = ($item['provinsi'] === $katalog['provinsi']) ? 'selected' : ''; ?>
                        <option value="<?= htmlspecialchars($item['provinsi']) ?>" <?= $selected ?>><?= htmlspecialchars($item['provinsi']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-sm-3 mt-3">
                <label>Kota</label>
                <select class="form-select mt-2" id="kotaSelect" name="kota" required>
                    <option disabled>Pilih Kota</option>
                    <!-- Kota akan diisi otomatis oleh JS -->
                </select>
            </div>

            <div class="col-sm-3 mt-3">
                <label>Harga Sewa Kostum</label>
                <input type="number" name="harga_sewa" class="form-control mt-2" value="<?= htmlspecialchars($katalog['harga_sewa']) ?>">
            </div>

            <div class="col-sm-3 mt-3">
                <label>Visible Katalog</label>
                <select class="form-select mt-2" name="visible" required>
                    <option value="1" <?= ($katalog['visible'] == 1) ? 'selected' : '' ?>>Publish</option>
                    <option value="0" <?= ($katalog['visible'] == 0) ? 'selected' : '' ?>>Private</option>
                </select>
            </div>

            <div class="col-sm-12 mt-3">
                <label>Deskripsi Katalog</label>
                <textarea name="deskripsi" class="form-control mt-2" rows="6"><?= htmlspecialchars($katalog['deskripsi']) ?></textarea>
            </div>

            <div class="col-sm-12 mt-3">
                <label>Foto Katalog</label>
                <input type="file" name="foto_kostum" class="form-control form-control-sm mt-2">
                <?php if ($katalog['foto_kostum']) : ?>
                    <img src="<?= htmlspecialchars($katalog['foto_kostum']) ?>" alt="Foto Kostum" style="width:200px; margin-top:10px; border-radius: 8px;">
                <?php endif; ?>
            </div>
        </div>

        <div class="col-sm-3 offset-sm-9">
            <button type="submit" name="submit" id="submitBtn" class="btn btn-catalog px-3 py-2 w-100 d-block mt-3">
                <i class="bi bi-pencil-square me-3"></i>
                <span>Update Katalog</span>
            </button>
        </div>
    </form>
</div>

<script>
    const data = <?= json_encode($data) ?>;
    const katalog = <?= json_encode($katalog) ?>;

    const provinsiSelect = document.getElementById('provinsiSelect');
    const kotaSelect = document.getElementById('kotaSelect');

    function populateKota(provinsi) {
        kotaSelect.innerHTML = '';
        kotaSelect.disabled = false;

        const provinsiData = data.find(p => p.provinsi === provinsi);

        if (provinsiData) {
            provinsiData.kota.forEach(function(kota) {
                const option = document.createElement('option');
                option.value = kota;
                option.textContent = kota;
                if (kota === katalog.kota) option.selected = true;
                kotaSelect.appendChild(option);
            });
        }
    }

    // Set initial kota select options on page load
    if (katalog.provinsi) {
        populateKota(katalog.provinsi);
    }

    provinsiSelect.addEventListener('change', function() {
        populateKota(this.value);
    });
</script>

<?php include "../includes/footer.php"; ?>