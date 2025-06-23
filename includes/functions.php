<?php

function getDataHotel($conn) {
    $query = "SELECT * FROM hotel";
    $result = pg_query($conn, $query);
    $hotels = [];

    while ($row = pg_fetch_assoc($result)) {
        $hotels[] = $row;
    }

    return $hotels;
}

function getMatrix($hotels) {
    // Ambil kriteria dari database
    global $conn;
    $kriteria = pg_query($conn, "SELECT * FROM kriteria ORDER BY id");
    $kriteria_ids = [];
    while ($row = pg_fetch_assoc($kriteria)) {
        $kriteria_ids[] = $row['id'];
    }

    $matrix = [];
    foreach ($hotels as $hotel) {
        // Ambil nilai untuk setiap kriteria dari tabel nilai
        $nilai = [];
        foreach ($kriteria_ids as $kid) {
            $res = pg_query_params($conn, "SELECT nilai FROM nilai WHERE id_alternatif=$1 AND id_kriteria=$2", [$hotel['id'], $kid]);
            $row = pg_fetch_assoc($res);
            $nilai[] = $row ? floatval($row['nilai']) : 0; // 0 jika belum ada nilai
        }
        $matrix[] = [
            'id' => $hotel['id'],
            'nama' => $hotel['nama'],
            'nilai' => $nilai
        ];
    }
    return $matrix;
}

function normalizeMatrix($matrix) {
    if (empty($matrix) || empty($matrix[0]['nilai'])) return [];
    $jumlah = count($matrix[0]['nilai']);
    $pembagi = array_fill(0, $jumlah, 0);

    foreach ($matrix as $baris) {
        for ($i = 0; $i < $jumlah; $i++) {
            $pembagi[$i] += pow($baris['nilai'][$i], 2);
        }
    }

    for ($i = 0; $i < $jumlah; $i++) {
        $pembagi[$i] = sqrt($pembagi[$i]);
        if ($pembagi[$i] == 0) $pembagi[$i] = 1; // Hindari pembagian nol
    }

    foreach ($matrix as &$baris) {
        for ($i = 0; $i < $jumlah; $i++) {
            $baris['nilai'][$i] = $baris['nilai'][$i] / $pembagi[$i];
        }
    }

    return $matrix;
}

function bobotMatrix($matrix, $bobot) {
    foreach ($matrix as &$baris) {
        for ($i = 0; $i < count($bobot); $i++) {
            $baris['nilai'][$i] *= $bobot[$i];
        }
    }
    return $matrix;
}

function idealSolutions($matrix, $atribut) {
    if (empty($matrix) || empty($matrix[0]['nilai'])) {
        return [[], []]; // atau tampilkan pesan error
    }
    $jumlah = count($matrix[0]['nilai']);
    $A_plus = array_fill(0, $jumlah, 0);
    $A_minus = array_fill(0, $jumlah, 0);

    for ($i = 0; $i < $jumlah; $i++) {
        $nilai_kolom = array_column(array_column($matrix, 'nilai'), $i);
        $A_plus[$i] = ($atribut[$i] == 'benefit') ? max($nilai_kolom) : min($nilai_kolom);
        $A_minus[$i] = ($atribut[$i] == 'benefit') ? min($nilai_kolom) : max($nilai_kolom);
    }

    return [$A_plus, $A_minus];
}

function preferensi($matrix, $A_plus, $A_minus) {
    $hasil = [];
    foreach ($matrix as $baris) {
        $d_plus = 0;
        $d_minus = 0;
        for ($i = 0; $i < count($A_plus); $i++) {
            $d_plus += pow($baris['nilai'][$i] - $A_plus[$i], 2);
            $d_minus += pow($baris['nilai'][$i] - $A_minus[$i], 2);
        }
        $d_plus = sqrt($d_plus);
        $d_minus = sqrt($d_minus);
        $denom = $d_plus + $d_minus;
        $nilai = ($denom == 0) ? 0 : $d_minus / $denom; // Hindari pembagian nol
        $hasil[] = [
            'id' => $baris['id'],
            'nama' => $baris['nama'],
            'nilai' => round($nilai, 4)
        ];
    }

    usort($hasil, fn($a, $b) => $b['nilai'] <=> $a['nilai']);
    return $hasil;
}

function skorHarga($harga) {
    if ($harga < 285000) return 5;
    if ($harga <= 406000) return 4;
    if ($harga <= 527000) return 3;
    if ($harga <= 648000) return 2;
    return 1;
}

function skorKelas($bintang) {
    // $bintang: angka 1-5
    return intval($bintang);
}

function skorKecocokan($nilai) {
    // $nilai: 0-1 (misal kecocokan fasilitas, lokasi, pelayanan)
    if ($nilai <= 0.2) return 1;
    if ($nilai <= 0.4) return 2;
    if ($nilai <= 0.6) return 3;
    if ($nilai <= 0.8) return 4;
    return 5;
}

function skorKebersihan($nilai) {
    // $nilai: 1-10
    if ($nilai <= 2) return 1;
    if ($nilai <= 4) return 2;
    if ($nilai <= 6) return 3;
    if ($nilai <= 8) return 4;
    return 5;
}

function getBobotKriteria($conn) {
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
    $bobot = [];
    foreach ($data as $kriteria => $jumlah) {
        $bobot[$kriteria] = $total > 0 ? $jumlah / $total : 0;
    }
    return $bobot;
}
