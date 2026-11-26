<?php
session_start();
include 'koneksi.php';

if ($_SESSION['peran'] != "admin") { header("location:index.php"); exit; }

$id = $_GET['id'];

// Cari nama gambar sebelum dihapus agar bisa dihapus dari folder
$query = mysqli_query($koneksi, "SELECT gambar FROM buku WHERE id_buku='$id'");
$data = mysqli_fetch_assoc($query);

// Hapus file fisik dari folder uploads jika ada
if ($data['gambar'] != "" && file_exists("uploads/" . $data['gambar'])) {
    unlink("uploads/" . $data['gambar']);
}

// Hapus data dari database
mysqli_query($koneksi, "DELETE FROM buku WHERE id_buku='$id'");

echo "<script>alert('Buku berhasil dihapus!'); window.location='admin.php';</script>";
?>