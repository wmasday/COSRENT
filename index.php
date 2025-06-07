<?php
include 'includes/config.php';
include 'includes/header.php';

$user_id = $_SESSION['user_id'] ?? null;
$wishlist_ids = [];

if (isset($_SESSION['role']) && $_SESSION['role'] === 'pelanggan' && $user_id) {
    $stmt2 = $conn->prepare("SELECT katalog_id FROM wishlist WHERE user_id = ?");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($w = $res2->fetch_assoc()) $wishlist_ids[] = $w['katalog_id'];
}
?>

<main class="container" style="margin-top: 200px;height: 320px;">
    <div class="row">
        <div class="col-sm-6">
            <span class="open-sub-head d-block">Yaa~ konnichiwa~!</span>
            <h1 class="text-pink-head"><?php echo $APP_NAME; ?></h1>
            <span class="sub-head">Lampung</span>
            <span class="text-capitalize d-block pt-3 detail">Sewa kostum cosplay, wig, sepatu, dan properti <b class="text-pink">anime favoritmu!</b></span>

            <a href="./katalog/" class="btn btn-catalog mt-5 px-3 py-2">
                <i class="bi bi-rocket-takeoff me-3"></i>
                <span>Lihat Katalog</span>
            </a>
        </div>

        <div class="col-sm-3 offset-sm-2">
            <img src="./assets/img/hero.png" class="hero-landing" alt="HERO" />
            <div class="verfied-landing bg-white rounded-3 shadow-sm py-3 text-center">
                <i class="bi bi-check-circle me-3"></i> Trustsed Cosplay Rental
            </div>
        </div>
    </div>
</main>

<section class="mt-5 section-modern">
    <div class="blob-container">
        <div class="blob"></div>
        <div class="blob"></div>
        <div class="blob"></div>
    </div>
    <div class="container pt-1 pb-5">
        <div class="row mb-3">
            <div class="col-6 mb-4">
                <h2 class="text-gradient-pink">Katalog Terbaru</h2>
            </div>

            <div class="col-6 mb-4 text-end">
                <a href="./katalog/" class="text-decoration-none text-pink mt-3" style="cursor:pointer;">
                    View All <i class="bi bi-chevron-compact-right"></i>
                </a>
            </div>

            <?php
            $query = "SELECT * FROM katalog ORDER BY created_at DESC LIMIT 4";
            $result = $conn->query($query);

            while ($row = $result->fetch_assoc()) :
                $foto = $row['foto_kostum'] ? $row['foto_kostum'] : 'https://picsum.photos/400/300';

                $harga_formatted = "IDR " . number_format($row['harga_sewa'], 0, ',', '.');
            ?>
                <div class="col-sm-3 px-3 mb-3">
                    <div class="card w-100">
                        <img src="<?= htmlspecialchars($foto) ?>" class="card-img-top" alt="Foto Kostum" style="max-height: 200px;">
                        <div class="card-body">
                            <a href="./katalog/index.php?keyword=<?= htmlspecialchars($row['karakter']); ?>" class="badge bg-character float-start py-2">
                                <i class="bi bi-person-rolodex me-1"></i>
                                <?= htmlspecialchars($row['karakter']) ?>
                            </a>
                            <a href="./katalog/index.php?keyword=<?= htmlspecialchars($row['series']); ?>" class="badge bg-series float-end py-2">
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
                                        <a href="wishlist.php?katalog_id=<?= $row['id'] ?>" class="btn wishlist-btn <?= $active ?>">
                                            <i class="bi bi-bag-heart-fill"></i>
                                        </a>
                                    </div>
                                    <div class="col-9">
                                        <a href="./katalog/detail.php?id=<?= $row['id'] ?>" class="btn btn-detail">
                                            View Detail <i class="bi bi-chevron-compact-right"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php else : ?>
                                <a href="./katalog/detail.php?id=<?= $row['id'] ?>" class="btn btn-detail">
                                    View Detail <i class="bi bi-chevron-compact-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

        </div>
    </div>
</section>




<?php include 'includes/footer.php'; ?>