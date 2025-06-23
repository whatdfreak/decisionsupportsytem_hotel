<?php
require_once '../includes/config.php';
include 'menu.php';

// Ambil data alternatif dan kriteria (urut sesuai input/id)
$alternatif = pg_query($conn, "SELECT * FROM hotel ORDER BY id");
$kriteria = pg_query($conn, "SELECT * FROM kriteria ORDER BY id");

// Proses update inline
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id_alt = intval($_POST['edit_id']);
    foreach ($_POST['nilai'] as $id_kriteria => $nilai) {
        if ($nilai === '' || !is_numeric($nilai) || $nilai < 1 || $nilai > 5) continue;
        $cek = pg_query_params($conn, "SELECT id FROM nilai WHERE id_alternatif=$1 AND id_kriteria=$2", [$id_alt, $id_kriteria]);
        if (pg_num_rows($cek) > 0) {
            pg_query_params($conn, "UPDATE nilai SET nilai=$1 WHERE id_alternatif=$2 AND id_kriteria=$3", [$nilai, $id_alt, $id_kriteria]);
        } else {
            pg_query_params($conn, "INSERT INTO nilai (id_alternatif, id_kriteria, nilai) VALUES ($1, $2, $3)", [$id_alt, $id_kriteria, $nilai]);
        }
    }
    header("Location: nilai.php");
    exit;
}

// Hapus semua nilai untuk satu alternatif
if (isset($_GET['hapus'])) {
    $id_alt = intval($_GET['hapus']);
    pg_query_params($conn, "DELETE FROM nilai WHERE id_alternatif=$1", [$id_alt]);
    header("Location: nilai.php");
    exit;
}

// Ambil data nilai untuk tabel read (urut hotel sesuai id/input)
$nilai_data = pg_query($conn, "
    SELECT h.id AS id_alt, h.nama AS hotel, 
        array_agg(k.id ORDER BY k.id) AS kriteria_id,
        array_agg(k.nama ORDER BY k.id) AS kriteria,
        array_agg(n.nilai ORDER BY k.id) AS nilai
    FROM hotel h
    LEFT JOIN nilai n ON n.id_alternatif = h.id
    LEFT JOIN kriteria k ON n.id_kriteria = k.id
    GROUP BY h.id, h.nama
    ORDER BY h.id
");

// Untuk inline edit
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
?>

<div class="container">
    <h2>Data Nilai Alternatif</h2>
    <table>
        <tr>
            <th>No</th>
            <th>Alternatif</th>
            <?php pg_result_seek($kriteria, 0); while ($k = pg_fetch_assoc($kriteria)): ?>
                <th><?= htmlspecialchars($k['nama']) ?></th>
            <?php endwhile; ?>
            <th style="min-width:120px;">Aksi</th>
        </tr>
        <?php $no=1; while ($row = pg_fetch_assoc($nilai_data)): ?>
        <tr>
            <?php if ($edit_id == $row['id_alt']): ?>
            <form method="POST" style="display:contents;">
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['hotel']) ?></td>
                <?php
                $kriteria_id_arr = $row['kriteria_id'] ? explode(',', trim($row['kriteria_id'], '{}')) : [];
                $nilai_arr = $row['nilai'] ? explode(',', trim($row['nilai'], '{}')) : [];
                for ($i=0; $i < pg_num_rows($kriteria); $i++) {
                    $kid = isset($kriteria_id_arr[$i]) ? $kriteria_id_arr[$i] : '';
                    $v = (isset($nilai_arr[$i]) && $nilai_arr[$i] !== '') ? $nilai_arr[$i] : '';
                    echo '<td><input type="number" name="nilai[' . htmlspecialchars($kid) . ']" value="' . htmlspecialchars($v) . '" min="1" max="5" step="1" required style="width:60px;"></td>';
                }
                ?>
                <td>
                    <input type="hidden" name="edit_id" value="<?= $row['id_alt'] ?>">
                    <button type="submit" class="btn-save">Simpan</button>
                    <a href="nilai.php" class="btn-cancel">Batal</a>
                </td>
            </form>
            <?php else: ?>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['hotel']) ?></td>
            <?php
            $nilai_arr = $row['nilai'] ? explode(',', trim($row['nilai'], '{}')) : [];
            for ($i=0; $i < pg_num_rows($kriteria); $i++) {
                echo '<td>' . (isset($nilai_arr[$i]) && $nilai_arr[$i] !== '' ? $nilai_arr[$i] : '-') . '</td>';
            }
            ?>
            <td>
                <a href="nilai.php?edit=<?= $row['id_alt'] ?>" class="btn-edit">Edit</a>
                <a href="nilai.php?hapus=<?= $row['id_alt'] ?>" class="btn-delete" onclick="return confirm('Hapus semua nilai alternatif ini?')">Hapus</a>
            </td>
            <?php endif; ?>
        </tr>
        <?php endwhile; pg_result_seek($alternatif, 0); ?>
    </table>
    <style>
        .btn-edit, .btn-delete, .btn-save, .btn-cancel {
            display:inline-block; padding:4px 12px; border-radius:4px; text-decoration:none; font-size:14px;
        }
        .btn-edit {background:#2a5d9f; color:#fff;}
        .btn-delete {background:#e74c3c; color:#fff;}
        .btn-save {background:#27ae60; color:#fff; border:none;}
        .btn-cancel {background:#bbb; color:#fff;}
        .btn-edit:hover, .btn-delete:hover, .btn-save:hover, .btn-cancel:hover {opacity:0.85;}
        input[type="number"] {padding:2px 4px;}
    </style>
</div>