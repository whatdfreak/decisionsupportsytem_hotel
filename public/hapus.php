<?php
// filepath: d:\spk_hotel\public\hapus.php
require_once '../includes/config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "DELETE FROM hotel WHERE id = $1";
    $result = pg_query_params($conn, $query, [$id]);
    header("Location: index.php");
    exit;
} else {
    echo "ID tidak ditemukan.";
}