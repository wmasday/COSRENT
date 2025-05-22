<?php

include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyewa') {
    header("Location: ../auth/login.php");
    exit;
}
?>

<h2>Tambah Kostum</h2>
<form action="proses_tambah_kostum.php" method="POST" enctype="multipart/form-data">
    <input type="file" name="foto" required><br><br>

    <label>Kategori:</label>
    <select name="kategori" required>
        <option value="Kostum">Kostum</option>
        <option value="Wig Only">Wig Only</option>
        <option value="Sepatu">Sepatu</option>
        <option value="Weapon">Weapon</option>
    </select><br><br>

    <label>Status:</label>
    <select name="status" required>
        <option value="Ready">Ready</option>
        <option value="Coming Soon">Coming Soon</option>
    </select><br><br>

    <input type="text" name="judul" placeholder="Judul Kostum" required><br><br>
    <input type="text" name="series_anime" placeholder="Series Anime" required><br><br>
    <input type="text" name="karakter" placeholder="Karakter" required><br><br>
    <input type="text" name="ukuran" placeholder="Ukuran" required><br><br>

    <label>Gender:</label>
    <select name="gender" required>
        <option value="Pria">Pria</option>
        <option value="Wanita">Wanita</option>
        <option value="Unisex">Unisex</option>
    </select><br><br>

    <input type="number" name="harga" placeholder="Harga Sewa per Hari" required><br><br>
    <input type="text" name="provinsi" placeholder="Provinsi" required><br><br>
    <input type="text" name="kota" placeholder="Kota" required><br><br>
    <textarea name="deskripsi" placeholder="Deskripsi..." required></textarea><br><br>

    <label>Draft:</label>
    <select name="is_draft" required>
        <option value="1">Ya (Belum Tayang)</option>
        <option value="0">Tidak (Tayang)</option>
    </select><br><br>

    <button type="submit">Simpan</button>
</form>

<?php include '../includes/footer.php'; ?>