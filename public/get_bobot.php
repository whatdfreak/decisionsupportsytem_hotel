<?php
require_once '../includes/config.php';
include 'menu.php';

// Ambil total nilai tiap kriteria
$sql = "SELECT 
    SUM(harga) AS total_harga,
    SUM(kelas) AS total_kelas,
    SUM(fasilitas_kamar) AS total_fasilitas_kamar,
    SUM(fasilitas_publik) AS total_fasilitas_publik,
    SUM(lokasi) AS total_lokasi,
    SUM(pelayanan) AS total_pelayanan,
    SUM(kebersihan) AS total_kebersihan
    FROM kuisioner";
$result = pg_query($conn, $sql);
$data = pg_fetch_assoc($result);

// Hitung total semua nilai
$total = array_sum($data);

// Label kriteria agar lebih rapi
$label = [
    'total_harga' => 'Harga',
    'total_kelas' => 'Kelas',
    'total_fasilitas_kamar' => 'Fasilitas Kamar',
    'total_fasilitas_publik' => 'Fasilitas Publik',
    'total_lokasi' => 'Lokasi',
    'total_pelayanan' => 'Pelayanan',
    'total_kebersihan' => 'Kebersihan'
];

// Hitung bobot tiap kriteria
$bobot = [];
foreach ($data as $kriteria => $jumlah) {
    $bobot[$kriteria] = $total > 0 ? $jumlah / $total : 0;
}
?>
<div class="container">
    <div class="card" style="max-width:600px;margin:32px auto 0 auto;">
        <div class="card-header" style="background:#2a3559;color:#fff;border-radius:8px 8px 0 0;">
            <h2 style="margin:0;font-size:1.2em;">Bobot Kriteria dari Kuisioner</h2>
        </div>
        <div class="card-body" style="padding:28px 24px 18px 24px;">
            <table style="width:100%;border-collapse:collapse;">
                <tr style="background:#2a3559;color:#fff;">
                    <th style="padding:10px 8px;">Kriteria</th>
                    <th style="padding:10px 8px;">Bobot</th>
                </tr>
                <?php $i=0; foreach ($bobot as $kriteria => $nilai_bobot): $i++; ?>
                <tr style="background:<?= $i%2==0 ? '#f6f8fa' : '#fff' ?>;">
                    <td style="padding:10px 8px;"><?= $label[$kriteria] ?? htmlspecialchars($kriteria) ?></td>
                    <td><?= round($nilai_bobot, 4) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <style>
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(44,62,80,0.07);
            margin-bottom: 32px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #2a3559;
            color: #fff;
            font-weight: 600;
        }
        tr:hover td {
            background: #eaf1fb;
        }
        .card-header {
            padding: 16px 24px;
            border-radius: 8px 8px 0 0;
        }
        .card-body {
            padding: 28px 24px 18px 24px;
        }
    </style>
</div>