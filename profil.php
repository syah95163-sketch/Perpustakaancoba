<?php
session_start();
include 'koneksi.php';

// Proteksi halaman khusus anggota yang sudah login
if (!isset($_SESSION['peran']) || $_SESSION['peran'] != "pengunjung") {
    header("location:index.php");
    exit;
}

$id_pengguna = (int) $_SESSION['id_pengguna'];

// Mengambil data profil anggota
$anggota = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pengguna WHERE id_pengguna='$id_pengguna'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dasbor Profil Anggota - Perpustakaan Kita</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; color: #333; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #007BFF; color: #fff; padding: 14px 30px; }
        .navbar a { color: #fff; text-decoration: none; margin-right: 18px; font-weight: bold; font-size: 14px; }
        .navbar a.active { text-decoration: underline; }
        .btn-logout { background: #e74c3c; color: #fff !important; padding: 6px 14px; border-radius: 4px; margin-right: 0 !important; }

        .container { max-width: 1000px; margin: 25px auto; padding: 0 20px; }
        
        /* Kartu Nomor Anggota */
        .profile-card { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; padding: 25px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; box-shadow: 0 3px 8px rgba(0,0,0,0.12); }
        .member-badge { background: #28a745; padding: 8px 16px; border-radius: 6px; font-size: 16px; font-weight: bold; letter-spacing: 1px; }

        .section-box { background: #fff; padding: 20px; border-radius: 8px; margin-top: 25px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        th { background-color: #007BFF; color: white; }
        .th-history { background-color: #6c757d; }
    </style>
</head>
<body>

    <div class="navbar">
        <div>
            <a href="beranda.php">Beranda</a>
            <a href="katalog.php">Katalog Buku</a>
            <a href="profil.php" class="active">Dasbor Profil</a>
        </div>
        <div>
            <span style="margin-right: 12px; font-size: 14px;">Halo, <?php echo htmlspecialchars($anggota['nama']); ?></span>
            <a href="logout.php" class="btn-logout">Keluar</a>
        </div>
    </div>

    <div class="container">
        <!-- Informasi Nomor Anggota -->
        <div class="profile-card">
            <div>
                <h2 style="margin: 0 0 6px;"><?php echo htmlspecialchars($anggota['nama']); ?></h2>
                <p style="margin: 0; opacity: 0.85; font-size: 14px;">Kontak Terdaftar: <?php echo htmlspecialchars($anggota['kontak']); ?></p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 12px; opacity: 0.8; margin-bottom: 4px;">NOMOR ANGGOTA PERPUSTAKAAN</div>
                <div class="member-badge">
                    ANG-<?php echo str_pad($anggota['id_pengguna'], 4, '0', STR_PAD_LEFT); ?> (ID: <?php echo $anggota['id_pengguna']; ?>)
                </div>
            </div>
        </div>

        <!-- Daftar Buku yang Sedang Dipinjam -->
        <div class="section-box">
            <h3 style="margin-top: 0; color: #007BFF;">Buku yang Sedang Anda Pinjam</h3>
            <table>
                <tr>
                    <th>ID Transaksi</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tenggat Pengembalian</th>
                    <th>Status Waktu & Estimasi Denda</th>
                </tr>
                <?php
                $sedang_dipinjam = mysqli_query($koneksi, "
                    SELECT p.*, b.judul, b.penulis
                    FROM peminjaman p
                    JOIN buku b ON p.id_buku = b.id_buku
                    WHERE p.id_pengguna = '$id_pengguna' AND p.status = 'Dipinjam'
                    ORDER BY p.tenggat_kembali ASC
                ");

                if (mysqli_num_rows($sedang_dipinjam) > 0) {
                    while ($row = mysqli_fetch_assoc($sedang_dipinjam)) {
                        $tenggat = strtotime($row['tenggat_kembali']);
                        $hari_ini = strtotime(date('Y-m-d'));

                        if ($hari_ini > $tenggat) {
                            $telat = floor(($hari_ini - $tenggat) / (60 * 60 * 24));
                            $denda = $telat * 1000;
                            $info_waktu = "<span style='color:#e74c3c; font-weight:bold;'>Terlambat $telat hari (Denda berjalan: Rp " . number_format($denda, 0, ',', '.') . ")</span>";
                        } else {
                            $sisa = floor(($tenggat - $hari_ini) / (60 * 60 * 24));
                            $info_waktu = "<span style='color:#28a745; font-weight:bold;'>Sisa waktu $sisa hari lagi</span>";
                        }
                ?>
                <tr>
                    <td>#<?php echo $row['id_transaksi']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['judul']); ?></strong><br>
                        <small style="color:#666;"><?php echo htmlspecialchars($row['penulis']); ?></small>
                    </td>
                    <td><?php echo $row['tanggal_pinjam']; ?></td>
                    <td><strong><?php echo $row['tenggat_kembali']; ?></strong></td>
                    <td><?php echo $info_waktu; ?></td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center; color:#666;'>Anda tidak memiliki pinjaman buku aktif saat ini.</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- Riwayat Peminjaman Sebelumnya -->
        <div class="section-box">
            <h3 style="margin-top: 0; color: #444;">Riwayat Peminjaman Sebelumnya</h3>
            <table>
                <tr>
                    <th class="th-history">ID Transaksi</th>
                    <th class="th-history">Judul Buku</th>
                    <th class="th-history">Tanggal Pinjam</th>
                    <th class="th-history">Tenggat Kembali</th>
                    <th class="th-history">Status</th>
                    <th class="th-history">Denda</th>
                </tr>
                <?php
                $riwayat = mysqli_query($koneksi, "
                    SELECT p.*, b.judul
                    FROM peminjaman p
                    JOIN buku b ON p.id_buku = b.id_buku
                    WHERE p.id_pengguna = '$id_pengguna' AND p.status = 'Selesai'
                    ORDER BY p.id_transaksi DESC
                ");

                if (mysqli_num_rows($riwayat) > 0) {
                    while ($rw = mysqli_fetch_assoc($riwayat)) {
                ?>
                <tr>
                    <td>#<?php echo $rw['id_transaksi']; ?></td>
                    <td><?php echo htmlspecialchars($rw['judul']); ?></td>
                    <td><?php echo $rw['tanggal_pinjam']; ?></td>
                    <td><?php echo $rw['tenggat_kembali']; ?></td>
                    <td><span style="color:#28a745; font-weight:bold;">Selesai Dikembalikan</span></td>
                    <td>Rp <?php echo number_format($rw['denda'], 0, ',', '.'); ?></td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; color:#666;'>Belum ada riwayat peminjaman buku.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>

</body>
</html>