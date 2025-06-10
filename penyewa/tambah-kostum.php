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

function uploadKostumFoto($field, $target = '../uploads/katalog/')
{
    if (!empty($_FILES[$field]['name'])) {
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'png'];
        if (!in_array($ext, $allowed)) return null;

        if (!is_dir($target)) mkdir($target, 0777, true);
        $newName = uniqid('kostum_') . '.' . $ext;
        $dest = $target . $newName;
        move_uploaded_file($_FILES[$field]['tmp_name'], $dest);
        return $dest;
    }
    return null;
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
    $deskripsi = $_POST['deskripsi'];

    $foto_kostum = uploadKostumFoto('foto_kostum');

    $stmt = $conn->prepare("INSERT INTO katalog 
        (penyewa_id, nama_kostum, series, karakter, ukuran, gender, deskripsi, kategori, harga_sewa, kota, provinsi, status, foto_kostum) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "isssssssdssss",
        $penyewa_id,
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
        $foto_kostum
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Katalog berhasil ditambahkan.";
        header("Location: ../penyewa/dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal menambahkan katalog.";
        header("Location: ./tambah-kostum.php");
        exit();
    }
}
?>

<div class="container mt-5 pt-5">
    <h3 class="text-pinkv2 auth pt-3">Tambah Katalog</h3>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-sm-3 mt-3">
                <label>Foto Katalog <span class="text-danger" style="font-size: 10px;">* (jpg/png)</span></label>
                <input type="file" name="foto_kostum" class="form-control form-control-sm mt-2">
            </div>
            <div class="col-sm-3 mt-3">
                <label>Kategori Katalog</label>
                <select class="form-select mt-2" name="kategori" required>
                    <option disabled>Pilih Kategori</option>
                    <option value="Kostum">Kostum</option>
                    <option value="Wig Only">Wig Only</option>
                    <option value="Sepatu">Sepatu</option>
                    <option value="Weapon">Weapon</option>
                </select>
            </div>
            <div class="col-sm-3 mt-3">
                <label>Status Katalog</label>
                <select class="form-select mt-2" name="status" required>
                    <option value="tersedia">Tersedia</option>
                    <option value="disewa">Di Sewa</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>

            <div class="col-sm-3 mt-3">
                <label>Nama Kostum</label>
                <input type="text" name="nama_kostum" class="form-control mt-2">
            </div>

            <div class="col-sm-3 mt-3">
                <label>Series Kostum</label>
                <input type="text" name="series" class="form-control mt-2">
            </div>

            <div class="col-sm-3 mt-3">
                <label>Karakter Kostum</label>
                <input type="text" name="karakter" class="form-control mt-2">
            </div>

            <div class="col-sm-3 mt-3">
                <label>Ukuran Kostum</label>
                <input type="text" name="ukuran" class="form-control mt-2">
            </div>

            <div class="col-sm-3 mt-3">
                <label>Gender</label>
                <select class="form-select mt-2" name="gender" required>
                    <option disabled>Pilih Gender</option>
                    <option value="Pria">Pria</option>
                    <option value="Wanita">Wanita</option>
                    <option value="Unisex">Unisex</option>
                </select>
            </div>

            <div class="col-sm-3 mt-3">
                <label>Province</label>
                <select class="form-select mt-2" id="provinsiSelect" name="provinsi">
                    <option selected disabled>Pilih Provinsi</option>
                    <?php foreach ($data as $item) : ?>
                        <option value="<?= htmlspecialchars($item['provinsi']) ?>"><?= htmlspecialchars($item['provinsi']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-sm-3 mt-3">
                <label>Kota</label>
                <select class="form-select mt-2" id="kotaSelect" name="kota">
                    <option selected disabled>Pilih Kota</option>
                </select>
            </div>

            <div class="col-sm-3 mt-3">
                <label>Harga Sewa Kostum</label>
                <input type="number" name="harga_sewa" class="form-control mt-2">
            </div>

            <div class="col-sm-3 mt-3">
                <label>Visible Katalog</label>
                <select class="form-select mt-2" name="visible" required>
                    <option value="1">Publish</option>
                    <option value="0">Private</option>
                </select>
            </div>

            <div class="col-sm-12 mt-3">
                <label>Deskripsi Katalog</label>
                <textarea name="deskripsi" class="form-control mt-2" rows="6"></textarea>
            </div>
        </div>

        <div class="col-sm-3 offset-sm-9">
            <button type="submit" name="submit" id="submitBtn" class="btn btn-catalog px-3 py-2 w-100 d-block mt-3">
                <i class="bi bi-rocket-takeoff me-3"></i>
                <span>Tambahkan</span>
            </button>
        </div>
    </form>
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
</script>
<?php include '../includes/footer.php'; ?>