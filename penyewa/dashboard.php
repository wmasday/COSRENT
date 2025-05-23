<?php
include '../includes/config.php';
include '../includes/header.php';
include '../auth/auth.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT id FROM penyewa WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $penyewa_id = $row['id'];
} else {
    die("Data penyewa tidak ditemukan untuk user ini.");
}

$query = "SELECT * FROM katalog WHERE penyewa_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $penyewa_id);
$stmt->execute();
$result = $stmt->get_result();

$totalKostum = $conn->query("SELECT COUNT(*) as total FROM katalog WHERE penyewa_id = $penyewa_id")->fetch_assoc()['total'];
$totalSeries = $conn->query("SELECT COUNT(DISTINCT series) as total FROM katalog WHERE penyewa_id = $penyewa_id")->fetch_assoc()['total'];
$tersewa = $conn->query("SELECT COUNT(*) as total FROM katalog WHERE penyewa_id = $penyewa_id AND status = 'disewa'")->fetch_assoc()['total'];
$tersedia = $conn->query("SELECT COUNT(*) as total FROM katalog WHERE penyewa_id = $penyewa_id AND status = 'tersedia'")->fetch_assoc()['total'];
$maintenance = $conn->query("SELECT COUNT(*) as total FROM katalog WHERE penyewa_id = $penyewa_id AND status = 'maintenance'")->fetch_assoc()['total'];
?>

<div class="container" style="margin-top:100px;">
    <h4 class="text-pinkv2 pt-4 mb-4">Dashboard Penyewa</h4>
    <div class="dashboard-stat row g-3">
        <div class="col-sm-3 d-flex align-items-center bg-chartjs rounded-3 p-4">
            <canvas id="dashboardChart" style="max-width:100%; height:120px;"></canvas>
        </div>
        <div class="col-sm-9 row">
            <div class="col-4">
                <div class="total-card bg-gradient-katalog p-3">
                    <span class="d-block text-secondary">Katalog</span>
                    <div class="count display-6 fw-bold"><?= $totalKostum ?></div>
                </div>
                <div class="total-card bg-gradient-series p-3">
                    <span class="d-block text-secondary">Series</span>
                    <div class="count display-6 fw-bold"><?= $totalSeries ?></div>
                </div>
            </div>
            <div class="col-4">
                <div class="total-card bg-gradient-tersedia p-3">
                    <span class="d-block text-secondary">Tersedia</span>
                    <div class="count display-6 fw-bold"><?= $tersedia ?></div>
                </div>
                <div class="total-card bg-gradient-disewa p-3">
                    <span class="d-block text-secondary">Disewa</span>
                    <div class="count display-6 fw-bold"><?= $tersewa ?></div>
                </div>
            </div>

            <div class="col-4">
                <div class="total-card bg-gradient-disewa p-3">
                    <span class="d-block text-secondary">Maintenance</span>
                    <div class="count display-6 fw-bold"><?= $maintenance ?></div>
                </div>
            </div>

            <div class="col-sm-4">
                <a href="./tambah-kostum.php" class="btn btn-catalog px-3 py-3 d-block mt-3">
                    <i class="bi bi-rocket-takeoff me-3"></i>
                    <span>Tambahkan Kostum</span>
                </a>
            </div>
        </div>
    </div>


    <div class="row mt-5 mb-3">
        <div class="col-sm-22 mb-4">
            <h4 class="text-pinkv2 ">Kostum yang Disewakan</h4>
        </div>

        <?php while ($row = $result->fetch_assoc()) :
            $foto = $row['foto_kostum'] ? $row['foto_kostum'] : 'https://picsum.photos/400/300';

            $harga_formatted = "IDR " . number_format($row['harga_sewa'], 0, ',', '.');
        ?>
            <div class="col-sm-3 px-3 mb-3">
                <div class="card w-100">
                    <img src="<?= htmlspecialchars($foto) ?>" class="card-img-top" alt="Foto Kostum">
                    <div class="card-body">
                        <a href="#" class="badge bg-character float-start py-2">
                            <i class="bi bi-person-rolodex me-1"></i>
                            <?= htmlspecialchars($row['karakter']) ?>
                        </a>
                        <a href="#" class="badge bg-series float-end py-2">
                            <i class="bi bi-tags-fill me-1"></i>
                            <?= htmlspecialchars($row['series']) ?>
                        </a>
                        <div class="price mt-5 mb-3">
                            <i class="bi bi-wallet2 me-2"></i>
                            <?= $harga_formatted ?><span> / Hari</span>
                        </div>

                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>Size</td>
                                    <td><?= $row['ukuran']; ?></td>
                                </tr>
                                <tr>
                                    <td>Gender</td>
                                    <td><?= $row['gender']; ?></td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td class="text-capitalize"><?= $row['status']; ?></td>
                                </tr>
                                <tr>
                                    <td>Visible</td>
                                    <td><?= $row['visible'] == 1 ? 'Publish' : 'Private'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-3">
                                <a href="delete-kostum.php?id=<?= $row['id'] ?>" class="btn btn-delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                            <div class="col-9">
                                <a href="edit-kostum.php?id=<?= $row['id'] ?>" class="btn btn-detail">
                                    Edit <i class="bi bi-chevron-compact-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('dashboardChart').getContext('2d');
    const dashboardChart = new Chart(ctx, {
        type: 'polarArea',
        data: {
            labels: ['Total Kostum', 'Total Series', 'Tersewa', 'Tersedia', 'Maintenance'],
            datasets: [{
                label: '',
                data: [
                    <?= $totalKostum ?>,
                    <?= $totalSeries ?>,
                    <?= $tersewa ?>,
                    <?= $tersedia ?>,
                    <?= $maintenance ?>
                ],
                backgroundColor: [
                    'rgba(255, 75, 161, 0.6)',
                    'rgba(255, 166, 211, 0.6)',
                    'rgba(255, 134, 179, 0.6)',
                    'rgba(204, 102, 255, 0.6)',
                    'rgba(102, 204, 255, 0.6)'
                ],
                borderColor: '#ffffff20',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom', // <-- ubah di sini
                    labels: {
                        color: '#fff',
                        font: {
                            family: 'monospace',
                            size: 13
                        }
                    }
                },
                title: {
                    display: false
                }
            },
            scales: {
                r: {
                    ticks: {
                        color: '#ccc',
                        backdropColor: 'transparent'
                    },
                    grid: {
                        color: '#444'
                    },
                    angleLines: {
                        color: '#555'
                    }
                }
            }
        }
    });
</script>

<?php include '../includes/footer.php';
