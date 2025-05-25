<?php
include '../includes/config.php';
include '../includes/header.php';

if (!isset($_GET['id'])) {
    echo "Kostum tidak ditemukan.";
    exit;
}

$kostum_id = (int) $_GET['id'];

// Ambil data kostum + penyewa + user penyewa
$query = $conn->query("
    SELECT 
        k.*,
        p.rating AS rating_pelanggan,
        p.jumlah_rating AS jumlah_rating_pelanggan,
        u.fullname,
        u.no_telepon,
        u.email,
        u.provinsi AS provinsi_penyewa,
        u.kota AS kota_penyewa,
        u.profil_path,
        u.verifikasi_ktp
    FROM katalog k
    LEFT JOIN penyewa p ON k.penyewa_id = p.id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE k.id = $kostum_id AND k.visible = 1
");

if ($query->num_rows !== 1) {
    echo "Kostum tidak ditemukan atau tidak tersedia.";
    exit;
}

$kostum = $query->fetch_assoc();

// Format harga
$harga_format = "Rp" . number_format($kostum['harga_sewa'], 0, ',', '.');

// Data rating katalog (kostum)
$rating_katalog = $kostum['rating'] ?? 0;
$jumlah_rating_katalog = $kostum['jumlah_rating'] ?? 0;

// Data rating pelanggan (penyewa)
$rating_pelanggan = $kostum['rating_pelanggan'] ?? 0;
$jumlah_rating_pelanggan = $kostum['jumlah_rating_pelanggan'] ?? 0;

?>
<style>
    .detail {
        background: rgba(255, 255, 255, 0.25);
        box-shadow: rgba(17, 17, 26, 0.1) 0px 0px 16px;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    .table,
    .table tr,
    .table td,
    .table th {
        background: transparent !important;
    }

    .badge {
        font-weight: 500;
    }

    .table .bg-success {
        background-color: #2ed573 !important;
    }

    .table .bg-primary {
        background-color: #70a1ff !important;
    }

    .table .bg-warning {
        background-color: #ff7f50 !important;
    }

    th {
        font-weight: 500 !important;
    }
</style>

<div class="container mt-5 pt-5">
    <div class="row px-2 py-3 mt-3 detail">
        <div class="col-sm-3">
            <img src="../uploads/<?= htmlspecialchars($kostum['foto_kostum']) ?>" width="300" class="rounded-3 shadow-sm" alt="Foto Kostum">
        </div>
        <div class="col-sm-9">
            <h4 class="pt-3 pb-2 text-pinkv2"><?= htmlspecialchars($kostum['nama_kostum']) ?> - <?= htmlspecialchars($kostum['karakter']) ?></h4>

            <div class="mb-3">
                <div id="rating-kostum" class="rating d-inline-block" data-jenis="katalog" data-id="<?= $kostum_id ?>" data-rating="<?= $rating_katalog ?>"></div>
                <small class="text-muted ms-2">(<?= $jumlah_rating_katalog ?> ulasan)</small>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th width="200">Series Anime</th>
                                <td><?= htmlspecialchars($kostum['series']) ?></td>
                            </tr>
                            <tr>
                                <th>Ukuran</th>
                                <td><?= htmlspecialchars($kostum['ukuran']) ?></td>
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td><?= htmlspecialchars($kostum['gender']) ?></td>
                            </tr>
                            <tr>
                                <th>Harga Sewa / Hari</th>
                                <td><?= $harga_format ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-sm-6">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th>Kategori</th>
                                <td><?= htmlspecialchars($kostum['kategori']) ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php
                                    echo $kostum['status'] == "tersedia" ? '<span class="badge bg-success">Tersedia</span>' : "";
                                    echo $kostum['status'] == "disewa" ? '<span class="badge bg-primary">Disewa</span>' : "";
                                    echo $kostum['status'] == "maintenance" ? '<span class="badge bg-warning">Maintenance</span>' : "";
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Provinsi / Kota</th>
                                <td><?= htmlspecialchars($kostum['provinsi']) ?> / <?= htmlspecialchars($kostum['kota']) ?></td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td><?= nl2br(htmlspecialchars($kostum['deskripsi'])) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="row px-2 py-3 mt-4 detail">
                <div class="col-sm-2 pt-3 ps-4">
                    <img src="../uploads/<?= htmlspecialchars($kostum['profil_path']) ?>" width="100" style="border-radius: 100%; width:80px; height:80px;" alt="Foto Profil Penyewa">
                </div>
                <div class="col-sm-10">
                    <h4 class="pt-3 pb-2 text-pinkv2"><?= htmlspecialchars($kostum['fullname']) ?></h4>

                    <a href="mailto:<?= htmlspecialchars($kostum['email']) ?>" class="text-dark text-decoration-none me-3">
                        <i class="bi bi-envelope-fill text-pinkv2 me-2"></i>
                        <?= htmlspecialchars($kostum['email']) ?>
                    </a>

                    <a href="https://wa.me/<?= htmlspecialchars($kostum['no_telepon']) ?>" class="text-dark text-decoration-none me-3">
                        <i class="bi bi-whatsapp text-pinkv2 me-2"></i>
                        <?= htmlspecialchars($kostum['no_telepon']) ?>
                    </a>

                    <div class="mb-3 mt-3">
                        <div id="rating-penyewa" class="rating d-inline-block" data-jenis="penyewa" data-id="<?= $kostum['penyewa_id'] ?>" data-rating="<?= $rating_pelanggan ?>"></div>
                        <small class="text-muted ms-2">(<?= $jumlah_rating_pelanggan ?> ulasan)</small>
                    </div>

                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th width="200">Provinsi</th>
                                <td><?= htmlspecialchars($kostum['provinsi_penyewa']) ?></td>
                            </tr>
                            <tr>
                                <th>Kota</th>
                                <td><?= htmlspecialchars($kostum['kota_penyewa']) ?></td>
                            </tr>
                            <tr>
                                <th>Verified</th>
                                <td>
                                    <?php
                                    echo $kostum['verifikasi_ktp'] ?
                                        '<span class="badge bg-primary">DONE</span>' :
                                        '<span class="badge bg-danger">WAITING</span>';
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container fixed-bottom">
    <div class="row mb-4">
        <div class="col-sm-2 offset-sm-10">
            <a href="../pelanggan/order.php?katalog_id=<?= $kostum_id; ?>" class="btn btn-catalog mt-5 w-100">
                <i class="bi bi-rocket-takeoff me-3"></i>
                <span>Order</span>
            </a>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.rating').forEach(container => {
        const id = container.dataset.id;
        const jenis = container.dataset.jenis;

        // Get current rating from dataset if set
        const currentRating = parseFloat(container.dataset.rating || 0);

        for (let i = 1; i <= 5; i++) {
            const star = document.createElement('i');
            star.className = 'bi bi-star-fill';
            star.dataset.value = i;

            // Apply initial "selected" class for current rating
            if (i <= Math.round(currentRating)) {
                star.classList.add('selected');
            }

            // Hover effects
            star.addEventListener('mouseover', () => {
                [...container.children].forEach(s => s.classList.remove('hovered'));
                for (let j = 0; j < i; j++) {
                    container.children[j].classList.add('hovered');
                }
            });

            star.addEventListener('mouseout', () => {
                [...container.children].forEach(s => s.classList.remove('hovered'));
            });

            // Click handler to send new rating
            star.addEventListener('click', () => {
                if (!id || !jenis) {
                    alert("Error: id atau jenis rating belum terdefinisi!");
                    return;
                }

                fetch('./rating.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${encodeURIComponent(id)}&jenis=${encodeURIComponent(jenis)}&nilai=${i}`
                    })
                    .then(res => res.text())
                    .then(text => {
                        [...container.children].forEach(s => s.classList.remove('selected'));
                        for (let j = 0; j < i; j++) {
                            container.children[j].classList.add('selected');
                        }
                    });
            });

            container.appendChild(star);
        }
    });
</script>


<?php include '../includes/footer.php'; ?>