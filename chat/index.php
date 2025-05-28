<?php
include "../includes/config.php";
include "../includes/header.php";
include "../auth/auth.php";

$user_id = $_SESSION['user_id'];

// Kirim pesan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan']) && isset($_POST['receiver_id'])) {
    $pesan = trim($_POST['pesan']);
    $receiver_id = intval($_POST['receiver_id']);

    if ($pesan !== '' && $receiver_id > 0) {
        $stmt = $conn->prepare("INSERT INTO chat (sender_id, receiver_id, pesan, status_baca) VALUES (?, ?, ?, 'belum_dibaca')");
        $stmt->bind_param("iis", $user_id, $receiver_id, $pesan);
        $stmt->execute();
        exit;
    }
}

// Ambil daftar kontak
$stmt = $conn->prepare("
    SELECT u.id, u.fullname, u.profil_path
    FROM users u
    JOIN (
        SELECT sender_id AS uid FROM chat WHERE receiver_id = ?
        UNION
        SELECT receiver_id AS uid FROM chat WHERE sender_id = ?
    ) recent ON u.id = recent.uid
    WHERE u.id != ?
    GROUP BY u.id
");
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$receiver_id = isset($_GET['to']) ? intval($_GET['to']) : null;
$receiver = null;

if ($receiver_id) {
    // Update status pesan jadi dibaca
    $stmt = $conn->prepare("UPDATE chat SET status_baca = 'dibaca' WHERE sender_id = ? AND receiver_id = ? AND status_baca = 'belum_dibaca'");
    $stmt->bind_param("ii", $receiver_id, $user_id);
    $stmt->execute();

    // Ambil profil receiver
    $stmt = $conn->prepare("SELECT fullname, profil_path FROM users WHERE id = ?");
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();
    $receiver = $stmt->get_result()->fetch_assoc();
}

$messages = [];
if ($receiver_id) {
    $stmt = $conn->prepare("
        SELECT * FROM chat 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
        ORDER BY waktu_kirim ASC
    ");
    $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<style>
    body {
        background: #e9ecef;
    }

    .sidebar {
        background: white;
        height: 100%;
        overflow-y: auto;
    }

    .chat-box {
        background: #f8f9fa;
        height: 500px;
        overflow-y: scroll;
        padding: 10px;
    }

    .message {
        margin-bottom: 10px;
        font-size: 13px !important;
    }

    .message.me {
        text-align: right;
    }

    .message .bubble {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 10px;
        max-width: 75%;
    }

    .message.me .bubble {
        background: #45aaf2;
        color: white;
    }

    .message.them .bubble {
        background: #dee2e6;
    }

    .profile-img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
    }

    .user-item:hover {
        background: #f1f1f1;
        cursor: pointer;
    }

    .chat-container {
        margin-top: 100px;
    }

    small {
        font-size: 10px !important;
        padding-top: 5px;
    }

    .text-primary2 {
        color: #3867d6;
    }

    @media (max-width: 768px) {
        .desktop-sidebar {
            display: none;
        }
    }
</style>
</head>

<body>
    <div class="container chat-container">
        <div class="row rounded bg-white">
            <!-- Sidebar Desktop -->
            <div class="col-md-3 sidebar p-3 desktop-sidebar">
                <h5 class="mb-3">Kontak</h5>
                <?php foreach ($users as $u) : ?>
                    <div class="d-flex align-items-center mb-2 user-item p-2 rounded" onclick="location.href='?to=<?= $u['id'] ?>'">
                        <img src="<?= $u['profil_path'] ?>" class="profile-img me-3">
                        <span style="font-size: 15px; font-weight: 500;"><?= $u['fullname'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Chat Area -->
            <div class="col-md-9 d-flex flex-column p-0">
                <?php if ($receiver) : ?>
                    <!-- Header -->
                    <div class="d-flex align-items-center p-3 border-bottom bg-light">
                        <button class="btn btn-outline-secondary d-md-none me-2" data-bs-toggle="offcanvas" data-bs-target="#contactSidebar">☰</button>
                        <img src="<?= $receiver['profil_path'] ?>" class="profile-img me-3">
                        <h6 class="m-0"><?= $receiver['fullname'] ?></h6>
                    </div>

                    <!-- Chat Box -->
                    <div id="chat-box" class="chat-box flex-grow-1">
                        <?php foreach ($messages as $msg) : ?>
                            <div class="message <?= $msg['sender_id'] == $user_id ? 'me' : 'them' ?>">
                                <div class="bubble">
                                    <?= htmlspecialchars($msg['pesan']) ?>
                                    <?php if ($msg['sender_id'] == $user_id) : ?>
                                        <?php if ($msg['status_baca'] == 'dibaca') : ?>
                                            <i class="bi bi-check2-all text-primary2 ms-2" title="Dibaca"></i>
                                        <?php else : ?>
                                            <i class="bi bi-check2 text-muted ms-2" title="Terkirim, belum dibaca"></i>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted d-block"><?= $msg['waktu_kirim'] ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>


                    <!-- Input -->
                    <form id="chat-form" class="p-3 border-top bg-white">
                        <div class="input-group">
                            <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
                            <input type="text" name="pesan" class="form-control me-3" placeholder="Ketik pesan..." required>
                            <button class="btn bg-primaryv2 rounded-3 px-5">
                                <i class="bi bi-send-fill text-white"></i>
                            </button>
                        </div>
                    </form>
                <?php else : ?>
                    <div class="p-4 text-center text-secondary">Pilih kontak untuk mulai chat</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar Mobile Offcanvas -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="contactSidebar">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Kontak</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <?php foreach ($users as $u) : ?>
                <div class="d-flex align-items-center mb-2 user-item p-2 rounded" onclick="location.href='?to=<?= $u['id'] ?>'">
                    <img src="<?= $u['profil_path'] ?>" class="profile-img me-2">
                    <strong><?= $u['fullname'] ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($receiver) : ?>
        <script>
            function refreshMessages() {
                $.post("<?= $_SERVER['PHP_SELF'] ?>?to=<?= $receiver_id ?>", {}, function(data) {
                    const box = $(data).find('#chat-box').html();
                    $('#chat-box').html(box).scrollTop($('#chat-box')[0].scrollHeight);
                });
            }

            $(document).ready(function() {
                $('#chat-form').on('submit', function(e) {
                    e.preventDefault();
                    $.post('', $(this).serialize(), function() {
                        $('input[name=pesan]').val('');
                        refreshMessages();
                    });
                });

                setInterval(refreshMessages, 3000);
            });
        </script>
    <?php
    endif;
    include "../includes/footer.php";
    ?>