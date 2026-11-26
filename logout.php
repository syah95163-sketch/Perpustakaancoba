<?php
session_start();
session_destroy(); // Menghapus semua data sesi
header("location:index.php"); // Mengarahkan kembali ke halaman login
exit;
?>