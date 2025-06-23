<?php
// filepath: d:\spk_hotel\public\edit.php
require_once '../includes/config.php';

if (!isset($_GET['id'])) {
    echo "ID tidak ditemukan.";
    exit;
}

$id = intval($_GET['id']);
$result = pg_query_params($conn, "SELECT * FROM hotel WHERE id = $1", [$id]);
$data = pg_fetch_assoc($result);

if (!$data) {
    echo "Data hotel tidak ditemukan.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $fp = $_POST['fasilitas_publik'];
    $fk = $_POST['fasilitas_kamar'];
    $lokasi = $_POST['lokasi'];
    $pelayanan = $_POST['pelayanan'];
    $kelas = $_POST['kelas'];
    $kebersihan = $_POST['kebersihan'];

    $sql = "UPDATE hotel SET 
            nama=$1, harga=$2, fasilitas_publik=$3, 
            fasilitas_kamar=$4, lokasi=$5, pelayanan=$6, 
            kelas=$7, kebersihan=$8 WHERE id = $9";
    $params = [$nama, $harga, $fp, $fk, $lokasi, $pelayanan, $kelas, $kebersihan, $id];
    pg_query_params($conn, $sql, $params);
    header("Location: index.php");
    exit;
}
?>

<h2>Edit Data Hotel</h2>
<form method="POST">
    <label>Nama Hotel:</label>
    <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required><br>
    <label>Harga:</label>
    <input type="number" name="harga" value="<?= htmlspecialchars($data['harga']) ?>" step="0.01" required><br>
    <label>Fasilitas Publik:</label>
    <input type="number" name="fasilitas_publik" value="<?= htmlspecialchars($data['fasilitas_publik']) ?>" step="0.01" required><br>
    <label>Fasilitas Kamar:</label>
    <input type="number" name="fasilitas_kamar" value="<?= htmlspecialchars($data['fasilitas_kamar']) ?>" step="0.01" required><br>
    <label>Lokasi:</label>
    <input type="number" name="lokasi" value="<?= htmlspecialchars($data['lokasi']) ?>" step="0.01" required><br>
    <label>Pelayanan:</label>
    <input type="number" name="pelayanan" value="<?= htmlspecialchars($data['pelayanan']) ?>" step="0.01" required><br>
    <label>Kelas:</label>
    <input type="number" name="kelas" value="<?= htmlspecialchars($data['kelas']) ?>" step="0.01" required><br>
    <label>Kebersihan:</label>
    <input type="number" name="kebersihan" value="<?= htmlspecialchars($data['kebersihan']) ?>" step="0.01" required><br>
    <button type="submit">Update</button>
</form>

<a href="index.php">⬅️ Kembali</a>