<?php
// UBAH hanya jika username/password MySQL Anda berbeda.
$host = "localhost";
$user = "root";
$pass = "";
$db   = "umkm_uniq qwe";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>