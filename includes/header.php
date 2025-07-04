<?php
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $queryChatUnread = "SELECT COUNT(*) as jumlah FROM chat WHERE receiver_id = ? AND status_baca = 'belum_dibaca'";
    $stmt = $conn->prepare($queryChatUnread);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fetchChatUnread = $result->fetch_assoc();
    $stmt->close();
    $countChatUnread = $fetchChatUnread['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rental Kostum Anime</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="/assets/js/jquery-3.7.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.21.2/dist/sweetalert2.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <div class="landing" id="particles-js"></div>
    <header class="container mt-4 fixed-top">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <a href="<?php echo $BASE_URL; ?>" class="navbar-brand">🎭 CosplayRental</a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                    <div class="d-flex gap-2" style="z-index: 999999;">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == "penyewa") { ?>
                            <a href="/penyewa/dashboard.php" class="btn btn-custom-primary font-silkscreen">Dashboard</a>
                        <?php } ?>

                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == "pelanggan") { ?>
                            <a href="/pelanggan/dashboard.php" class="btn btn-custom-primary font-silkscreen">Dashboard</a>
                        <?php } ?>

                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == "admin") { ?>
                            <a href="/admin/dashboard.php" class="btn btn-custom-primary font-silkscreen">Dashboard</a>
                        <?php } ?>

                        <?php if (isset($_SESSION['role']) && isset($_SESSION['user_id'])) { ?>
                            <a href="/chat/" class="btn btn-custom-primary font-silkscreen mx-3">
                                Chat
                                <?php if ($countChatUnread > 0) : ?>
                                    <div class="badge bg-danger">
                                        <i class="bi bi-chat-dots-fill"></i> <?= $countChatUnread ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php } ?>
                        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) : ?>
                            <div class="dropdown">
                                <div class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <strong class="text-white me-2 mt-1" style="font-weight: 400;"><?= htmlspecialchars($_SESSION['fullname']) ?></strong>
                                    <?php if ($_SESSION['role'] == "admin") : ?>
                                        <img src="<?php echo $_SESSION['profil_path']; ?>" alt="avatar" width="32" height="32" class="rounded-circle me-2 float-end">
                                    <?php else : ?>
                                        <img src="<?php echo $_SESSION['profil_path']; ?>" alt="avatar" width="32" height="32" class="rounded-circle me-2 float-end">
                                    <?php endif; ?>
                                </div>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="/auth/profile.php">Edit Profile</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item text-danger" href="/auth/logout.php">Logout</a></li>
                                </ul>
                            </div>
                        <?php else : ?>
                            <a href="/auth/login.php" class="btn btn-custom-primary font-silkscreen">Login</a>
                            <a href="/auth/register.php" class="btn btn-custom-primary font-silkscreen">Register</a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </nav>
    </header>