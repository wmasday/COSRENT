<?php
include '../includes/config.php';
include '../includes/header.php';

$json = file_get_contents("../includes/province.json");
$data = json_decode($json, true);

$user_id = $_SESSION['user_id'] ?? null;
$wishlist_ids = [];

if (isset($_SESSION['role']) && $_SESSION['role'] === 'pelanggan' && $user_id) {
    $stmt2 = $conn->prepare("SELECT katalog_id FROM wishlist WHERE user_id = ?");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($w = $res2->fetch_assoc()) $wishlist_ids[] = $w['katalog_id'];
}

// Filter GET
$keyword   = $_GET['keyword'] ?? '';
$kategori  = $_GET['kategori'] ?? '';
$provinsi  = $_GET['provinsi'] ?? '';
$kota      = $_GET['kota'] ?? '';

$sql = "SELECT * FROM katalog WHERE visible = 1";

if (!empty($keyword)) {
    $sql .= " AND (nama_kostum LIKE '%$keyword%' OR series LIKE '%$keyword%' OR karakter LIKE '%$keyword%')";
}
if (!empty($kategori)) $sql .= " AND kategori = '$kategori'";
if (!empty($provinsi)) $sql .= " AND provinsi = '$provinsi'";
if (!empty($kota)) $sql .= " AND kota = '$kota'";

$result = $conn->query($sql);
?>

<main class="container mt-5 pt-5 mb-5">
    <form method="GET" class="row g-2 mt-3 mb-5 bg-white shadow-sm p-3 rounded-3">
        <div class="col-sm-5">
            <input type="text" name="keyword" class="form-control" placeholder="Cari Nama kostum, karakter, atau anime" value="<?= htmlspecialchars($keyword) ?>">
        </div>
        <div class="col-sm-2">
            <select name="kategori" class="form-select pb-2">
                <option value="">Semua Kategori</option>
                <option value="Kostum" <?= $kategori == 'Kostum' ? 'selected' : '' ?>>Kostum</option>
                <option value="Wig Only" <?= $kategori == 'Wig Only' ? 'selected' : '' ?>>Wig Only</option>
                <option value="Sepatu" <?= $kategori == 'Sepatu' ? 'selected' : '' ?>>Sepatu</option>
                <option value="Weapon" <?= $kategori == 'Weapon' ? 'selected' : '' ?>>Weapon</option>
            </select>
        </div>
        <div class="col-sm-2">
            <select class="form-select pb-2" id="provinsiSelect" name="provinsi">
                <option selected disabled>Pilih Provinsi</option>
                <?php foreach ($data as $item) : ?>
                    <option value="<?= htmlspecialchars($item['provinsi']) ?>"><?= htmlspecialchars($item['provinsi']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-2">
            <select class="form-select pb-2" id="kotaSelect" name="kota">
                <option selected disabled>Pilih Kota</option>
            </select>
        </div>
        <div class="col-sm-1">
            <button type="submit" class="btn btn-detail">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </form>

    <h4 class="text-pinkv2 mt-3 mb-3">
        <?php if (!empty($keyword)) : ?>
            Hasil Pencarian : <?php echo $keyword; ?>
        <?php else : ?>
            Semua Katalog
        <?php endif; ?>
    </h4>
    <?php if ($result && $result->num_rows > 0) : ?>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()) : ?>
                <?php
                $foto = $row['foto_kostum'] ?: 'https://picsum.photos/400/300';
                $harga_formatted = "IDR " . number_format($row['harga_sewa'], 0, ',', '.');
                $active = in_array($row['id'], $wishlist_ids) ? 'active' : '';
                ?>
                <div class="col-sm-3 px-3 mb-3">
                    <div class="card w-100">
                        <img src="<?= htmlspecialchars($foto) ?>" class="card-img-top" alt="Foto Kostum">
                        <div class="card-body">
                            <a href="./index.php?keyword=<?= htmlspecialchars($row['karakter']); ?>" class="badge bg-character float-start py-2">
                                <i class="bi bi-person-rolodex me-1"></i>
                                <?= htmlspecialchars($row['karakter']) ?>
                            </a>
                            <a href="./index.php?keyword=<?= htmlspecialchars($row['series']); ?>" class="badge bg-series float-end py-2">
                                <i class="bi bi-tags-fill me-1"></i>
                                <?= htmlspecialchars($row['series']) ?>
                            </a>
                            <div class="price mt-5 mb-3">
                                <i class="bi bi-wallet2 me-2"></i>
                                <?= $harga_formatted ?><span> / Hari</span>
                            </div>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'pelanggan') : ?>
                                <?php
                                $active = in_array($row['id'], $wishlist_ids) ? 'active' : '';
                                ?>
                                <div class="row">
                                    <div class="col-3">
                                        <a href="../wishlist.php?katalog_id=<?= $row['id'] ?>" class="btn wishlist-btn <?= $active ?>">
                                            <i class="bi bi-bag-heart-fill"></i>
                                        </a>
                                    </div>
                                    <div class="col-9">
                                        <a href="./detail.php?id=<?= $row['id'] ?>" class="btn btn-detail">
                                            View Detail <i class="bi bi-chevron-compact-right"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php else : ?>
                                <a href="./detail.php?id=<?= $row['id'] ?>" class="btn btn-detail">
                                    View Detail <i class="bi bi-chevron-compact-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <p class="text-center text-muted">Tidak ada kostum ditemukan.</p>
    <?php endif; ?>
</main>

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