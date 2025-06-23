<?php
require_once '../includes/config.php';
include 'menu.php';

// Ambil semua data kuisioner
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
<style>
    .card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(44,62,80,0.10);
        margin-bottom: 32px;
        overflow: hidden;
    }
    .card-header {
        background: #2a3559;
        color: #fff;
        padding: 22px 24px 16px 24px;
        border-radius: 12px 12px 0 0;
        text-align: center;
        font-size: 1.25em;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .card-body {
        padding: 28px 24px 18px 24px;
    }
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 10px;
        background: #fff;
        border-radius: 0 0 12px 12px;
        overflow: hidden;
    }
    th, td {
        padding: 12px 8px;
        text-align: center;
        border-bottom: 1px solid #e0e0e0;
    }
    th {
        background: #2a3559;
        color: #fff;
        font-weight: 600;
        border-bottom: 2px solid #d0d7de;
    }
    tr:last-child td {
        border-bottom: none;
    }
    tr:nth-child(even) td {
        background: #f6f8fa;
    }
    tr:hover td {
        background: #eaf1fb;
    }
    @media (max-width: 700px) {
        .card { max-width: 98vw; }
        .card-body { padding: 16px 4vw 12px 4vw; }
        table, th, td { font-size: 15px; }
    }
</style>
<div class="container">
    <div class="card" style="max-width:600px;margin:32px auto 0 auto;">
        <div class="card-header">
            Rekap Bobot Kriteria dari Kuisioner
        </div>
        <div class="card-body">
            <table>
                <tr>
                    <th>Kriteria</th>
                    <th>Jumlah Nilai</th>
                    <th>Bobot</th>
                </tr>
                <?php $i=0; foreach ($bobot as $kriteria => $nilai_bobot): $i++; ?>
                <tr>
                    <td><?= $label[$kriteria] ?? htmlspecialchars($kriteria) ?></td>
                    <td><?= $data[$kriteria] ?></td>
                    <td><?= round($nilai_bobot, 4) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>