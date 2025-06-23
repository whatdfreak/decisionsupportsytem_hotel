<?php
require_once '../includes/config.php';

$pesan_error = '';
$pesan_sukses = '';

// Proses update inline (tanpa edit bobot)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $nama = $_POST['nama'];
    $sifat = $_POST['sifat'];
    pg_query_params($conn, "UPDATE kriteria SET nama=$1, sifat=$2 WHERE id=$3", [$nama, $sifat, $id]);
    header("Location: kriteria.php");
    exit;
}

// Tambah kriteria (tanpa input bobot)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $sifat = $_POST['sifat'];
    // Bobot default 0, nanti diupdate otomatis
    pg_query_params($conn, "INSERT INTO kriteria (nama, bobot, sifat) VALUES ($1, 0, $2)", [$nama, $sifat]);
    header("Location: kriteria.php");
    exit;
}

// Hapus kriteria (dengan pengecekan error)
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    // Hapus dulu data di tabel nilai yang terkait
    pg_query_params($conn, "DELETE FROM nilai WHERE id_kriteria = $1", [$id]);
    $res = pg_query_params($conn, "DELETE FROM kriteria WHERE id = $1", [$id]);
    if (!$res) {
        $pesan_error = "Data tidak bisa dihapus karena masih digunakan di tabel lain.";
    } else {
        header("Location: kriteria.php");
        exit;
    }
}

// Update bobot otomatis dari kuisioner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bobot'])) {
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
    $total = array_sum($data);

    // Ambil semua kriteria urut sesuai field kuisioner
    $kriteria_q = pg_query($conn, "SELECT * FROM kriteria ORDER BY id");
    $fields = [
        'total_harga',
        'total_kelas',
        'total_fasilitas_kamar',
        'total_fasilitas_publik',
        'total_lokasi',
        'total_pelayanan',
        'total_kebersihan'
    ];
    $i = 0;
    while ($row = pg_fetch_assoc($kriteria_q)) {
        $bobot_baru = $total > 0 ? $data[$fields[$i]] / $total : 0;
        pg_query_params($conn, "UPDATE kriteria SET bobot=$1 WHERE id=$2", [$bobot_baru, $row['id']]);
        $i++;
    }
    $pesan_sukses = "Bobot berhasil diupdate otomatis dari kuisioner.";
}

// Ambil data kriteria
$result = pg_query($conn, "SELECT * FROM kriteria ORDER BY id");

// Untuk inline edit
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
?>
<?php include 'menu.php'; ?>
<div class="container">
    <h2>Data Kriteria</h2>
    <?php if ($pesan_error): ?>
        <div style="background:#ffeaea;color:#c0392b;padding:10px 16px;border-radius:6px;margin-bottom:12px;">
            <?= htmlspecialchars($pesan_error) ?>
        </div>
    <?php endif; ?>
    <?php if ($pesan_sukses): ?>
        <div style="background:#eafbe7;color:#2a5d9f;padding:10px 16px;border-radius:6px;margin-bottom:12px;">
            <?= htmlspecialchars($pesan_sukses) ?>
        </div>
    <?php endif; ?>
    <form method="POST" style="margin-bottom:1em;display:inline-block;">
        <input type="text" name="nama" placeholder="Nama Kriteria" required>
        <select name="sifat" required>
            <option value="benefit">Benefit</option>
            <option value="cost">Cost</option>
        </select>
        <button type="submit" name="tambah">Tambah</button>
    </form>
    <form method="POST" style="margin-bottom:1em;display:inline-block;">
        <button type="submit" name="update_bobot" style="background:#2a5d9f;color:#fff;">Update Bobot Otomatis</button>
    </form>
    <div style="margin-bottom:10px;color:#888;font-size:14px;">
        <b>Catatan:</b> Bobot akan diupdate otomatis dari hasil input kuisioner. Tidak perlu input bobot manual.
    </div>
    <table>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Bobot</th>
            <th>Sifat</th>
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
                <td>
                    <span><?= $row['bobot'] ?></span>
                </td>
                <td>
                    <select name="sifat" required>
                        <option value="benefit" <?= $row['sifat']=='benefit'?'selected':'' ?>>Benefit</option>
                        <option value="cost" <?= $row['sifat']=='cost'?'selected':'' ?>>Cost</option>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn-save">Simpan</button>
                    <a href="kriteria.php" class="btn-cancel">Batal</a>
                </td>
            </form>
            <?php else: ?>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= $row['bobot'] ?></td>
            <td><?= $row['sifat'] ?></td>
            <td>
                <a href="kriteria.php?edit=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                <a href="kriteria.php?hapus=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Hapus kriteria ini?')">Hapus</a>
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
        input[type="text"], select {padding:2px 4px;}
    </style>
</div>