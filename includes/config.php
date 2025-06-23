<?php
$db_url = "postgresql://neondb_owner:npg_9ut7iNvJTZMo@ep-odd-forest-a1d4ban2-pooler.ap-southeast-1.aws.neon.tech/neondb?sslmode=require";
$url = parse_url($db_url);

$conn = pg_connect(
    "host={$url['host']} port=5432 dbname=" . ltrim($url['path'], '/') .
    " user={$url['user']} password={$url['pass']} sslmode=require"
);

if (!$conn) {
    die("Koneksi database gagal.");
}
?>
