<?php
require_once '../includes/config.php';
include 'menu.php';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $harga = (int)$_POST['harga'];
    $kelas = (int)$_POST['kelas'];
    $fasilitas_kamar = (int)$_POST['fasilitas_kamar'];
    $fasilitas_publik = (int)$_POST['fasilitas_publik'];
    $lokasi = (int)$_POST['lokasi'];
    $pelayanan = (int)$_POST['pelayanan'];
    $kebersihan = (int)$_POST['kebersihan'];

    $sql = "INSERT INTO kuisioner (harga, kelas, fasilitas_kamar, fasilitas_publik, lokasi, pelayanan, kebersihan)
            VALUES ($1, $2, $3, $4, $5, $6, $7)";
    $result = pg_query_params($conn, $sql, [
        $harga, $kelas, $fasilitas_kamar, $fasilitas_publik, $lokasi, $pelayanan, $kebersihan
    ]);
    if ($result) $success = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Input Kuisioner Bobot Kriteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f6f8fa;">
<div class="container">
    <div class="card" style="max-width:440px;margin:32px auto 0 auto;">
        <div class="card-header" style="background:#2a3559;color:#fff;border-radius:8px 8px 0 0;">
            <h2 style="margin:0;font-size:1.2em;">Input Kuisioner Bobot Kriteria</h2>
        </div>
        <div class="card-body" style="padding:28px 24px 18px 24px;">
            <?php if ($success): ?>
                <div class="alert-success" style="margin-bottom:1em;background:#eafbe7;color:#2a5d9f;padding:8px 12px;border-radius:4px;">Data berhasil disimpan!</div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Harga Sewa Kamar (1-5)</label>
                    <input type="number" name="harga" min="1" max="5" required>
                </div>
                <div class="form-group">
                    <label>Kelas Hotel (1-5)</label>
                    <input type="number" name="kelas" min="1" max="5" required>
                </div>
                <div class="form-group">
                    <label>Fasilitas Kamar (1-5)</label>
                    <input type="number" name="fasilitas_kamar" min="1" max="5" required>
                </div>
                <div class="form-group">
                    <label>Fasilitas Publik (1-5)</label>
                    <input type="number" name="fasilitas_publik" min="1" max="5" required>
                </div>
                <div class="form-group">
                    <label>Lokasi (1-5)</label>
                    <input type="number" name="lokasi" min="1" max="5" required>
                </div>
                <div class="form-group">
                    <label>Pelayanan (1-5)</label>
                    <input type="number" name="pelayanan" min="1" max="5" required>
                </div>
                <div class="form-group">
                    <label>Kebersihan (1-5)</label>
                    <input type="number" name="kebersihan" min="1" max="5" required>
                </div>
                <button type="submit" class="btn-primary" style="margin-top:14px;width:100%;">Simpan</button>
            </form>
        </div>
    </div>
    <style>
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(44,62,80,0.07);
            margin-bottom: 32px;
        }
        .form-group {
            margin-bottom: 13px;
        }
        .form-group label {
            display:block;
            margin-bottom:4px;
            font-weight:500;
            color:#2a3559;
        }
        .form-group input[type="number"] {
            width:100%;
            padding:7px 10px;
            border:1px solid #d0d7de;
            border-radius:4px;
            font-size:15px;
            background:#f6f8fa;
        }
        .btn-primary {
            background:#2a3559;
            color:#fff;
            border:none;
            padding:9px 20px;
            border-radius:4px;
            font-size:16px;
            cursor:pointer;
            font-weight:500;
        }
        .btn-primary:hover {opacity:0.95;}
        .alert-success {
            border-left: 4px solid #2a5d9f;
        }
        .card-header {
            padding: 16px 24px;
        }
    </style>
</div>
</body>
</html>