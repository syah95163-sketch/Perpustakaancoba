<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['peran']) || $_SESSION['peran'] != "admin") {
    header("location:index.php");
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pengguna WHERE id_pengguna='$id' AND peran='pengunjung'"));

if (!$data) {
    die("Data anggota tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Anggota - <?php echo $data['nama']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #eee; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .kartu {
            width: 380px;
            background: linear-gradient(135deg, #007BFF, #0056b3);
            color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .kartu-header { border-bottom: 1px solid rgba(255,255,255,0.3); padding-bottom: 10px; margin-bottom: 15px; }
        .kartu-header h2 { margin: 0; font-size: 18px; letter-spacing: 1px; }
        .kartu-header p { margin: 2px 0 0; font-size: 12px; opacity: 0.85; }
        .info-row { margin-bottom: 10px; }
        .info-label { font-size: 11px; text-transform: uppercase; opacity: 0.8; }
        .info-val { font-size: 16px; font-weight: bold; }
        .btn-print { margin-top: 20px; padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: bold; }
        
        /* Menyembunyikan tombol cetak saat kertas diprint */
        @media print {
            body { background: white; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="kartu">
        <div class="kartu-header">
            <h2>KARTU ANGGOTA PERPUSTAKAAN</h2>
            <p>Perpustakaan Kita - Kartu Identitas Peminjaman</p>
        </div>
        <div class="info-row">
            <div class="info-label">Nomor ID Anggota</div>
            <div class="info-val">ANG-<?php echo str_pad($data['id_pengguna'], 4, '0', STR_PAD_LEFT); ?> (ID Sistem: <?php echo $data['id_pengguna']; ?>)</div>
        </div>
        <div class="info-row">
            <div class="info-label">Nama Lengkap</div>
            <div class="info-val"><?php echo $data['nama']; ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Nomor Kontak</div>
            <div class="info-val"><?php echo $data['kontak']; ?></div>
        </div>
    </div>

    <button class="btn-print" onclick="window.print()">Cetak Kartu / Simpan PDF</button>

</body>
</html>