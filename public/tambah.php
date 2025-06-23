<?php
require_once '../includes/config.php';
include 'menu.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $fp = $_POST['fasilitas_publik'];
    $fk = $_POST['fasilitas_kamar'];
    $lokasi = $_POST['lokasi'];
    $pelayanan = $_POST['pelayanan'];
    $kelas = $_POST['kelas'];
    $kebersihan = $_POST['kebersihan'];

    $sql = "INSERT INTO hotel (nama, harga, fasilitas_publik, fasilitas_kamar, lokasi, pelayanan, kelas, kebersihan)
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";
    $params = [$nama, $harga, $fp, $fk, $lokasi, $pelayanan, $kelas, $kebersihan];
    pg_query_params($conn, $sql, $params);
    header("Location: index.php");
    exit;
}
?>

<div class="container">
    <h2>Tambah Data Hotel</h2>
    <form method="POST">
        <label>Nama Hotel:</label>
        <input type="text" name="nama" required><br>
        <label>Harga:</label>
        <input type="number" name="harga" step="0.01" required><br>
        <label>Fasilitas Publik:</label>
        <input type="number" name="fasilitas_publik" step="0.01" required><br>
        <label>Fasilitas Kamar:</label>
        <input type="number" name="fasilitas_kamar" step="0.01" required><br>
        <label>Lokasi:</label>
        <input type="number" name="lokasi" step="0.01" required><br>
        <label>Pelayanan:</label>
        <input type="number" name="pelayanan" step="0.01" required><br>
        <label>Kelas:</label>
        <input type="number" name="kelas" step="0.01" required><br>
        <label>Kebersihan:</label>
        <input type="number" name="kebersihan" step="0.01" required><br>
        <button type="submit">Simpan</button>
    </form>
    <br>
    <a href="index.php">⬅️ Kembali</a>
</div>
