<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
include 'menu.php';

$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $responden = trim($_POST['responden']);

    // Ambil data mentah dari form
    $harga = $_POST['harga_sewa'];
    $kelas = $_POST['kelas_hotel'];
    $kecocokan_fasilitas = $_POST['kecocokan_fasilitas'];
    $kecocokan_lokasi = $_POST['kecocokan_lokasi'];
    $kecocokan_pelayanan = $_POST['kecocokan_pelayanan'];
    $kebersihan = $_POST['kebersihan'];

    // Validasi sederhana
    if (
        $harga === '' ||
        $kelas === '' ||
        $kecocokan_fasilitas === '' ||
        $kecocokan_lokasi === '' ||
        $kecocokan_pelayanan === '' ||
        $kebersihan === ''
    ) {
        $errors[] = "Semua field wajib diisi.";
    }

    if (!$errors) {
        // Konversi ke nilai 1-5
        $nilai_harga = skorHarga($harga);
        $nilai_kelas = skorKelas($kelas);
        $nilai_fasilitas = skorKecocokan($kecocokan_fasilitas);
        $nilai_lokasi = skorKecocokan($kecocokan_lokasi);
        $nilai_pelayanan = skorKecocokan($kecocokan_pelayanan);
        $nilai_kebersihan = skorKebersihan($kebersihan);

        // Simpan ke tabel kuisioner
        $result = pg_query_params(
            $conn,
            "INSERT INTO kuisioner (nilai_harga, nilai_kelas, nilai_fasilitas, nilai_lokasi, nilai_pelayanan, nilai_kebersihan, responden) VALUES ($1,$2,$3,$4,$5,$6,$7)",
            [
                $nilai_harga,
                $nilai_kelas,
                $nilai_fasilitas,
                $nilai_lokasi,
                $nilai_pelayanan,
                $nilai_kebersihan,
                $responden
            ]
        );
        if ($result) {
            $success = true;
        } else {
            $errors[] = "Gagal simpan data.";
        }
    }
}
?>
<div class="container">
    <h2>Input Data Kuisioner</h2>
    <?php if ($success): ?>
        <div style="color:green;">Data berhasil disimpan!</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div style="color:red;"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>
    <form method="POST" style="margin-bottom:2em;">
        <label>Responden (opsional):</label>
        <input type="text" name="responden" placeholder="Nama Responden">
        <label>Harga Sewa Kamar:</label>
        <input type="number" name="harga_sewa" required>
        <label>Kelas Hotel (1-5):</label>
        <input type="number" name="kelas_hotel" min="1" max="5" required>
        <label>Tingkat Kecocokan Fasilitas (0-1):</label>
        <input type="number" step="0.01" name="kecocokan_fasilitas" min="0" max="1" required>
        <label>Tingkat Kecocokan Lokasi (0-1):</label>
        <input type="number" step="0.01" name="kecocokan_lokasi" min="0" max="1" required>
        <label>Tingkat Kecocokan Pelayanan (0-1):</label>
        <input type="number" step="0.01" name="kecocokan_pelayanan" min="0" max="1" required>
        <label>Kebersihan Hotel (1-10):</label>
        <input type="number" name="kebersihan" min="1" max="10" required>
        <button type="submit">Simpan</button>
    </form>
</div>