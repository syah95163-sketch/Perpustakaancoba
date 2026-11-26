<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['peran']) || $_SESSION['peran'] != "admin") {
    header("location:index.php");
    exit;
}

// 1. PROSES PINJAM BUKU
if (isset($_POST['proses_pinjam'])) {
    $id_pengguna = (int) $_POST['id_pengguna'];
    $id_buku = (int) $_POST['id_buku'];
    $lama_pinjam = (int) $_POST['lama_pinjam'];

    $cek_anggota = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE id_pengguna='$id_pengguna' AND peran='pengunjung'");
    $cek_buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku='$id_buku' AND status_buku='Aktif'");
    $data_buku = mysqli_fetch_assoc($cek_buku);

    if (mysqli_num_rows($cek_anggota) == 0) {
        echo "<script>alert('Gagal: ID Anggota tidak ditemukan!');</script>";
    } elseif (!$data_buku || $data_buku['stok'] <= 0) {
        echo "<script>alert('Gagal: Buku tidak ditemukan, nonaktif, atau stok habis!');</script>";
    } else {
        // Catat transaksi & kurangi stok buku
        mysqli_query($koneksi, "INSERT INTO peminjaman (id_pengguna, id_buku, tanggal_pinjam, tenggat_kembali, status, denda) 
                                VALUES ('$id_pengguna', '$id_buku', CURDATE(), DATE_ADD(CURDATE(), INTERVAL $lama_pinjam DAY), 'Dipinjam', 0)");
        mysqli_query($koneksi, "UPDATE buku SET stok = stok - 1 WHERE id_buku='$id_buku'");
        echo "<script>alert('Peminjaman berhasil dicatat!'); window.location='sirkulasi.php';</script>";
    }
}

// 2. PROSES KEMBALIKAN BUKU
if (isset($_POST['proses_kembali'])) {
    $id_transaksi = (int) $_POST['id_transaksi'];

    $cek_trx = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_transaksi='$id_transaksi' AND status='Dipinjam'");
    $trx = mysqli_fetch_assoc($cek_trx);

    if (!$trx) {
        echo "<script>alert('Gagal: ID Transaksi tidak valid atau buku sudah dikembalikan!');</script>";
    } else {
        // Hitung denda otomatis jika melewati tenggat_kembali (Rp 1.000 per hari)
        $tenggat = strtotime($trx['tenggat_kembali']);
        $hari_ini = strtotime(date('Y-m-d'));
        $denda = 0;

        if ($hari_ini > $tenggat) {
            $selisih_hari = floor(($hari_ini - $tenggat) / (60 * 60 * 24));
            $denda = $selisih_hari * 1000;
        }

        $id_buku = $trx['id_buku'];
        mysqli_query($koneksi, "UPDATE peminjaman SET status='Selesai', denda='$denda' WHERE id_transaksi='$id_transaksi'");
        mysqli_query($koneksi, "UPDATE buku SET stok = stok + 1 WHERE id_buku='$id_buku'");

        $info_denda = ($denda > 0) ? "Denda keterlambatan: Rp " . number_format($denda, 0, ',', '.') : "Tidak ada denda (Tepat waktu).";
        echo "<script>alert('Buku berhasil dikembalikan! $info_denda'); window.location='sirkulasi.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sirkulasi Perpustakaan - Kasir</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f9; color: #333; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #2c3e50; color: #fff; padding: 12px 20px; border-radius: 8px; }
        .nav-links a { color: #ecf0f1; text-decoration: none; margin-right: 15px; font-weight: bold; padding: 6px 10px; border-radius: 4px; }
        .nav-links a:hover, .nav-links a.active { background: #34495e; }
        .btn-logout { background: #e74c3c; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; }

        .kasir-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .kasir-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); }
        input { width: 100%; padding: 9px; margin: 6px 0 14px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-pinjam { width: 100%; padding: 10px; background: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-kembali { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        
        .section-box { background: #fff; padding: 20px; border-radius: 8px; margin-top: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        th { background-color: #2c3e50; color: white; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="nav-links">
            <a href="admin.php">Dashboard & Buku</a>
            <a href="anggota.php">Manajemen Anggota</a>
            <a href="sirkulasi.php" class="active">Sirkulasi (Kasir)</a>
        </div>
        <div>
            <span style="margin-right: 15px;">Admin: <?php echo $_SESSION['nama']; ?></span>
            <a href="logout.php" class="btn-logout">Keluar</a>
        </div>
    </div>

    <!-- Formulir Cepat Kasir -->
    <div class="kasir-grid">
        <!-- Form Pinjam Buku -->
        <div class="kasir-card" style="border-top: 4px solid #007BFF;">
            <h3 style="margin-top: 0; color: #007BFF;">Formulir Pinjam Buku</h3>
            <form method="POST">
                <label>ID Anggota</label>
                <input type="number" name="id_pengguna" placeholder="Masukkan ID Anggota" required>

                <label>ID Buku</label>
                <input type="number" name="id_buku" placeholder="Masukkan ID Buku" required>

                <label>Durasi Peminjaman (Hari)</label>
                <input type="number" name="lama_pinjam" value="7" min="1" required>

                <button type="submit" name="proses_pinjam" class="btn-pinjam">Proses Peminjaman</button>
            </form>
        </div>

        <!-- Form Kembalikan Buku -->
        <div class="kasir-card" style="border-top: 4px solid #28a745;">
            <h3 style="margin-top: 0; color: #28a745;">Formulir Kembalikan Buku</h3>
            <form method="POST">
                <label>ID Transaksi Peminjaman</label>
                <input type="number" name="id_transaksi" placeholder="Masukkan ID Transaksi (lihat tabel bawah)" required>
                <p style="font-size: 12px; color: #666; margin-top: -8px;">
                    *Stok buku akan otomatis bertambah dan denda dihitung otomatis (Rp 1.000/hari jika lewat tenggat).
                </p>
                <button type="submit" name="proses_kembali" class="btn-kembali">Proses Pengembalian</button>
            </form>
        </div>
    </div>

    <!-- Daftar Transaksi Sedang Berjalan -->
    <div class="section-box">
        <h3 style="margin-top: 0;">Daftar Buku yang Sedang Dipinjam</h3>
        <table>
            <tr>
                <th>ID Transaksi</th>
                <th>Peminjam</th>
                <th>Buku Dipinjam</th>
                <th>Tanggal Pinjam</th>
                <th>Tenggat Kembali</th>
                <th>Status Denda Saat Ini</th>
            </tr>
            <?php
            $aktif = mysqli_query($koneksi, "
                SELECT p.*, u.nama, b.judul 
                FROM peminjaman p
                JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                JOIN buku b ON p.id_buku = b.id_buku
                WHERE p.status = 'Dipinjam'
                ORDER BY p.id_transaksi DESC
            ");
            if (mysqli_num_rows($aktif) > 0) {
                while ($row = mysqli_fetch_assoc($aktif)) {
                    $tenggat = strtotime($row['tenggat_kembali']);
                    $hari_ini = strtotime(date('Y-m-d'));
                    if ($hari_ini > $tenggat) {
                        $hari_telat = floor(($hari_ini - $tenggat) / (60 * 60 * 24));
                        $status_denda = "<span style='color:#e74c3c; font-weight:bold;'>Terlambat $hari_telat hari (Rp " . number_format($hari_telat * 1000, 0, ',', '.') . ")</span>";
                    } else {
                        $status_denda = "<span style='color:#28a745;'>Aman (Belum Jatuh Tempo)</span>";
                    }
            ?>
            <tr>
                <td><strong><?php echo $row['id_transaksi']; ?></strong></td>
                <td><?php echo $row['nama']; ?> (ID: <?php echo $row['id_pengguna']; ?>)</td>
                <td><?php echo $row['judul']; ?> (ID: <?php echo $row['id_buku']; ?>)</td>
                <td><?php echo $row['tanggal_pinjam']; ?></td>
                <td><?php echo $row['tenggat_kembali']; ?></td>
                <td><?php echo $status_denda; ?></td>
            </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center; color:#666;'>Belum ada buku yang sedang dipinjam.</td></tr>";
            }
            ?>
        </table>
    </div>

</body>
</html>