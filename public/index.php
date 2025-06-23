<?php
require_once '../includes/config.php';

// Data statistik
$total_hotel = pg_fetch_result(pg_query($conn, "SELECT COUNT(*) FROM hotel"), 0, 0);
$total_kriteria = pg_fetch_result(pg_query($conn, "SELECT COUNT(*) FROM kriteria"), 0, 0);
$total_nilai = pg_fetch_result(pg_query($conn, "SELECT COUNT(*) FROM nilai"), 0, 0);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard SPK Pemilihan Hotel - TOPSIS</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {background:#f6f8fa;}
        .dashboard-cards {
            display: flex;
            gap: 24px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }
        .card {
            background: #fff;
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            padding: 24px 32px;
            flex: 1;
            min-width: 180px;
            text-align: center;
            box-shadow: 0 2px 8px #e3e6f0;
        }
        .card-title {
            font-size: 16px;
            color: #888;
            margin-bottom: 8px;
        }
        .card-value {
            font-size: 32px;
            font-weight: bold;
            color: #2a5d9f;
        }
        .quick-links {
            margin-bottom: 32px;
        }
        .quick-links a {
            display: inline-block;
            margin-right: 18px;
            margin-bottom: 8px;
            padding: 10px 22px;
            background: #2a5d9f;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-size: 16px;
            transition: background 0.2s;
        }
        .quick-links a:hover { background: #17406b; }
        .desc {
            background: #fff;
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            padding: 24px 32px;
            margin-bottom: 32px;
            box-shadow: 0 2px 8px #e3e6f0;
            font-size: 17px;
            color: #444;
        }
        @media (max-width: 900px) {
            .dashboard-cards {flex-direction: column;}
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container">
        <h1 style="margin-bottom:0.5em;">Dashboard SPK Pemilihan Hotel</h1>
        <div class="desc">
            Sistem Pendukung Keputusan (SPK) pemilihan hotel menggunakan metode <b>TOPSIS</b>. 
            Silakan gunakan menu di bawah untuk mengelola data dan melakukan perankingan hotel terbaik.
        </div>
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-title">Total Hotel</div>
                <div class="card-value"><?= $total_hotel ?></div>
            </div>
            <div class="card">
                <div class="card-title">Total Kriteria</div>
                <div class="card-value"><?= $total_kriteria ?></div>
            </div>
            <div class="card">
                <div class="card-title">Total Data Nilai</div>
                <div class="card-value"><?= $total_nilai ?></div>
            </div>
        </div>

        <div class="quick-links">
            <a href="alternatif.php">➕ Kelola Hotel</a>
            <a href="kriteria.php">⚙️ Kelola Kriteria</a>
            <a href="nilai.php">📝 Input/Edit Nilai</a>
            <a href="proses.php">🔍 Proses & Ranking TOPSIS</a>
            <a href="kuisioner_bobot.php">📋 Input Bobot Kuisioner</a>
            <a href="get_bobot.php">📊 Rekap Bobot Kriteria</a>
        </div>
    </div>
</body>
</html>
