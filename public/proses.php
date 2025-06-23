<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
include 'menu.php';

// Ambil data kriteria
$kriteria = pg_query($conn, "SELECT * FROM kriteria ORDER BY id");
$atribut = [];
$nama_kriteria = [];
$id_kriteria = [];
$bobot = [];
while ($row = pg_fetch_assoc($kriteria)) {
    $atribut[] = $row['sifat'];
    $nama_kriteria[] = $row['nama'];
    $id_kriteria[] = $row['id'];
    $bobot[] = $row['bobot'];
}
pg_result_seek($kriteria, 0);

// Ambil data hotel
$hotels = pg_query($conn, "SELECT * FROM hotel ORDER BY id");

// Matriks keputusan: ambil dari tabel nilai
$data = [];
while ($h = pg_fetch_assoc($hotels)) {
    $row = ['id' => $h['id'], 'nama' => $h['nama'], 'nilai' => []];
    pg_result_seek($kriteria, 0);
    while ($k = pg_fetch_assoc($kriteria)) {
        $res = pg_query_params($conn, "SELECT nilai FROM nilai WHERE id_alternatif=$1 AND id_kriteria=$2", [$h['id'], $k['id']]);
        $r = pg_fetch_assoc($res);
        $row['nilai'][] = $r ? floatval($r['nilai']) : 0;
    }
    $data[] = $row;
}

// Proses TOPSIS
$normal = normalizeMatrix($data);
$terbobot = bobotMatrix($normal, $bobot);
list($A_plus, $A_minus) = idealSolutions($terbobot, $atribut);
$ranking = preferensi($terbobot, $A_plus, $A_minus);

// Hitung jarak ke solusi ideal
$jarak = [];
foreach ($terbobot as $row) {
    $d_plus = 0;
    $d_minus = 0;
    for ($i = 0; $i < count($A_plus); $i++) {
        $d_plus += pow($row['nilai'][$i] - $A_plus[$i], 2);
        $d_minus += pow($row['nilai'][$i] - $A_minus[$i], 2);
    }
    $jarak[] = [
        'nama' => $row['nama'],
        'd_plus' => round(sqrt($d_plus), 4),
        'd_minus' => round(sqrt($d_minus), 4)
    ];
}
?>
<div class="container">
    <div class="card" style="max-width:1100px;margin:32px auto 0 auto;">
        <div class="card-header" style="background:#2a3559;color:#fff;border-radius:8px 8px 0 0;text-align:center;">
            <h2 style="margin:0;font-size:1.2em;">Proses Perhitungan TOPSIS</h2>
        </div>
        <div class="card-body" style="padding:28px 24px 18px 24px;">
            <h3 style="color:#2a3559;">1. Matriks Keputusan</h3>
            <table>
                <tr>
                    <th>Alternatif</th>
                    <?php foreach ($nama_kriteria as $nk): ?>
                        <th><?= htmlspecialchars($nk) ?></th>
                    <?php endforeach; ?>
                </tr>
                <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <?php foreach ($row['nilai'] as $v): ?>
                        <td><?= $v ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </table>

            <h3 style="color:#2a3559;">2. Matriks Normalisasi</h3>
            <table>
                <tr>
                    <th>Alternatif</th>
                    <?php foreach ($nama_kriteria as $nk): ?>
                        <th><?= htmlspecialchars($nk) ?></th>
                    <?php endforeach; ?>
                </tr>
                <?php foreach ($normal as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <?php foreach ($row['nilai'] as $v): ?>
                        <td><?= round($v, 4) ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </table>

            <h3 style="color:#2a3559;">3. Matriks Ternormalisasi Terbobot</h3>
            <table>
                <tr>
                    <th>Alternatif</th>
                    <?php foreach ($nama_kriteria as $nk): ?>
                        <th><?= htmlspecialchars($nk) ?></th>
                    <?php endforeach; ?>
                </tr>
                <?php foreach ($terbobot as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <?php foreach ($row['nilai'] as $v): ?>
                        <td><?= round($v, 4) ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </table>

            <h3 style="color:#2a3559;">4. Solusi Ideal (+) dan Anti Ideal (-)</h3>
            <table>
                <tr>
                    <th></th>
                    <?php foreach ($nama_kriteria as $nk): ?>
                        <th><?= htmlspecialchars($nk) ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><b>A+</b></td>
                    <?php foreach ($A_plus as $v): ?>
                        <td><?= round($v, 4) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td><b>A-</b></td>
                    <?php foreach ($A_minus as $v): ?>
                        <td><?= round($v, 4) ?></td>
                    <?php endforeach; ?>
                </tr>
            </table>

            <h3 style="color:#2a3559;">5. Jarak Alternatif ke Solusi Ideal</h3>
            <table>
                <tr>
                    <th>Alternatif</th>
                    <th>Jarak ke A+</th>
                    <th>Jarak ke A-</th>
                </tr>
                <?php foreach ($jarak as $j): ?>
                <tr>
                    <td><?= htmlspecialchars($j['nama']) ?></td>
                    <td><?= $j['d_plus'] ?></td>
                    <td><?= $j['d_minus'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <h3 style="color:#2a3559;">6. Ranking Akhir</h3>
            <table>
                <tr>
                    <th>Peringkat</th>
                    <th>Nama Hotel</th>
                    <th>Nilai Preferensi</th>
                </tr>
                <?php $no = 1; foreach ($ranking as $r): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($r['nama']) ?></td>
                    <td><?= round($r['nilai'], 4) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <br>
            <a href="index.php" class="btn-primary" style="padding:7px 18px;display:inline-block;">⬅️ Kembali ke Dashboard</a>
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
            margin-bottom: 18px;
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
        .btn-primary {
            background:#2a3559;
            color:#fff;
            border:none;
            border-radius:4px;
            font-size:15px;
            cursor:pointer;
            text-decoration:none;
        }
        .btn-primary:hover {opacity:0.95;}
        h3 {margin-top:28px;}
    </style>
</div>
