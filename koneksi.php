<?php
// koneksi.php
$host = "localhost";
$user = "root"; // Default XAMPP
$pass = "";     // Default XAMPP (kosong)
$db   = "perpustakaan";

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek apakah jembatan berhasil dibuat
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>