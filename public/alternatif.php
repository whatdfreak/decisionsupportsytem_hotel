<?php
require_once '../includes/config.php';

// Ambil daftar kriteria
$kriteria = [];
$qk = pg_query($conn, "SELECT * FROM kriteria ORDER BY id");
while ($row = pg_fetch_assoc($qk)) {
    $kriteria[] = $row;
}

// Tambah alternatif/hotel + nilai kriteria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    // Insert hotel hanya kolom nama!
    $res = pg_query_params($conn, "INSERT INTO hotel (nama) VALUES ($1) RETURNING id", [$nama]);
    $hotel = pg_fetch_assoc($res);
    $id_hotel = $hotel['id'];
    // Simpan nilai kriteria
    foreach ($kriteria as $k) {
        $nilai = isset($_POST['nilai_'.$k['id']]) ? floatval($_POST['nilai_'.$k['id']]) : 0;
        pg_query_params($conn, "INSERT INTO nilai (id_alternatif, id_kriteria, nilai) VALUES ($1, $2, $3)", [$id_hotel, $k['id'], $nilai]);
    }
    header("Location: alternatif.php");
    exit;
}

// Edit alternatif/hotel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $nama = $_POST['nama'];
    pg_query_params($conn, "UPDATE hotel SET nama=$1 WHERE id=$2", [$nama, $id]);
    // Update nilai kriteria
    foreach ($kriteria as $k) {
        $nilai = isset($_POST['nilai_'.$k['id']]) ? floatval($_POST['nilai_'.$k['id']]) : 0;
        // Cek apakah sudah ada
        $cek = pg_query_params($conn, "SELECT * FROM nilai WHERE id_alternatif=$1 AND id_kriteria=$2", [$id, $k['id']]);
        if (pg_num_rows($cek) > 0) {
            pg_query_params($conn, "UPDATE nilai SET nilai=$1 WHERE id_alternatif=$2 AND id_kriteria=$3", [$nilai, $id, $k['id']]);
        } else {
            pg_query_params($conn, "INSERT INTO nilai (id_alternatif, id_kriteria, nilai) VALUES ($1, $2, $3)", [$id, $k['id'], $nilai]);
        }
    }
    header("Location: alternatif.php");
    exit;
}

// Hapus alternatif/hotel
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    pg_query_params($conn, "DELETE FROM nilai WHERE id_alternatif = $1", [$id]);
    pg_query_params($conn, "DELETE FROM hotel WHERE id = $1", [$id]);
    header("Location: alternatif.php");
    exit;
}

// Ambil data hotel/alternatif
$result = pg_query($conn, "SELECT * FROM hotel ORDER BY id");

// Untuk inline edit
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
?>
<?php include 'menu.php'; ?>
<div class="container">
    <h2>Data Alternatif (Hotel)</h2>
    <form method="POST" style="margin-bottom:1em;">
        <input type="text" name="nama" placeholder="Nama Hotel" required>
        <?php foreach ($kriteria as $k): ?>
            <input type="number" step="any" name="nilai_<?= $k['id'] ?>" placeholder="<?= htmlspecialchars($k['nama']) ?>" required style="width:110px;" min="0">
        <?php endforeach; ?>
        <button type="submit" name="tambah">Tambah</button>
    </form>
    <table>
        <tr>
            <th>No</th>
            <th>Nama Hotel</th>
            <?php foreach ($kriteria as $k): ?>
                <th><?= htmlspecialchars($k['nama']) ?></th>
            <?php endforeach; ?>
            <th>Aksi</th>
        </tr>
        <?php $no=1; while ($row = pg_fetch_assoc($result)): ?>
        <tr>
            <?php if ($edit_id == $row['id']): ?>
            <form method="POST" style="display:contents;">
                <td><?= $no++ ?></td>
                <td>
                    <input type="text" name="nama" value="<?= htmlspecialchars($row['nama']) ?>" required style="width:120px;">
                </td>
                <?php
                // Ambil nilai kriteria hotel ini
                $nilai = [];
                $qv = pg_query_params($conn, "SELECT * FROM nilai WHERE id_alternatif=$1", [$row['id']]);
                while ($nv = pg_fetch_assoc($qv)) {
                    $nilai[$nv['id_kriteria']] = $nv['nilai'];
                }
                ?>
                <?php foreach ($kriteria as $k): ?>
                    <td>
                        <input type="number" step="any" name="nilai_<?= $k['id'] ?>" value="<?= isset($nilai[$k['id']]) ? $nilai[$k['id']] : '' ?>" required style="width:80px;" min="0">
                    </td>
                <?php endforeach; ?>
                <td>
                    <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn-save">Simpan</button>
                    <a href="alternatif.php" class="btn-cancel">Batal</a>
                </td>
            </form>
            <?php else: ?>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <?php
            // Ambil nilai kriteria hotel ini
            $nilai = [];
            $qv = pg_query_params($conn, "SELECT * FROM nilai WHERE id_alternatif=$1", [$row['id']]);
            while ($nv = pg_fetch_assoc($qv)) {
                $nilai[$nv['id_kriteria']] = $nv['nilai'];
            }
            ?>
            <?php foreach ($kriteria as $k): ?>
                <td><?= isset($nilai[$k['id']]) ? $nilai[$k['id']] : '-' ?></td>
            <?php endforeach; ?>
            <td>
                <a href="alternatif.php?edit=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                <a href="alternatif.php?hapus=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Hapus hotel ini?')">Hapus</a>
            </td>
            <?php endif; ?>
        </tr>
        <?php endwhile; ?>
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
        input[type="text"], input[type="number"] {padding:2px 4px;}
    </style>
</div>