<?php
session_start();
include 'koneksi.php';

$is_anggota = (isset($_SESSION['peran']) && $_SESSION['peran'] == 'pengunjung');
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$query = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku='$id' AND status_buku='Aktif'");
$buku = mysqli_fetch_assoc($query);

if (!$buku) {
    echo "<script>alert('Buku tidak ditemukan!'); window.location='katalog.php';</script>";
    exit;
}

// Cek estimasi tanggal kembali terdekat jika stok habis
$estimasi_kembali = null;
if ($buku['stok'] <= 0) {
    $q_pinjam = mysqli_query($koneksi, "SELECT MIN(tenggat_kembali) AS terdekat FROM peminjaman WHERE id_buku='$id' AND status='Dipinjam'");
    $d_pinjam = mysqli_fetch_assoc($q_pinjam);
    $estimasi_kembali = $d_pinjam['terdekat'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Buku - <?php echo htmlspecialchars($buku['judul']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; color: #333; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #007BFF; color: #fff; padding: 14px 30px; }
        .navbar a { color: #fff; text-decoration: none; margin-right: 18px; font-weight: bold; font-size: 14px; }
        .btn-auth { background: #fff; color: #007BFF !important; padding: 6px 14px; border-radius: 4px; margin-right: 0 !important; }
        .btn-logout { background: #e74c3c; color: #fff !important; padding: 6px 14px; border-radius: 4px; margin-right: 0 !important; }

        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .detail-card { background: #fff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; gap: 30px; flex-wrap: wrap; }
        .cover-col { flex: 1; min-width: 240px; max-width: 280px; }
        .cover-col img { width: 100%; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
        .no-img { height: 340px; background: #e9ecef; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #888; }
        
        .info-col { flex: 2; min-width: 280px; }
        .info-col h1 { margin: 0 0 10px; font-size: 26px; color: #222; }
        .meta-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .meta-table td { padding: 8px 0; border-bottom: 1px solid #eee; font-size: 14px; }
        .meta-table td:first-child { width: 150px; color: #666; font-weight: bold; }

        .status-box { padding: 12px 15px; border-radius: 6px; margin: 15px 0; font-size: 14px; }
        .status-ready { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .status-out { background: #f8d7da; color: #721c24; border-left: 4px solid #e74c3c; }

        .sinopsis-box { margin-top: 20px; line-height: 1.6; font-size: 14px; color: #444; }
        .btn-back { display: inline-block; margin-top: 20px; padding: 10px 18px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="navbar">
        <div>
            <a href="beranda.php">Beranda</a>
            <a href="katalog.php">Katalog Buku</a>
            <?php if ($is_anggota): ?>
                <a href="profil.php">Dasbor Profil</a>
            <?php endif; ?>
        </div>
        <div>
            <?php if ($is_anggota): ?>
                <span style="margin-right: 12px; font-size: 14px;">Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                <a href="logout.php" class="btn-logout">Keluar</a>
            <?php else: ?>
                <a href="index.php" class="btn-auth">Login Anggota</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="detail-card">
            <div class="cover-col">
                <?php if (!empty($buku['gambar']) && file_exists('uploads/' . $buku['gambar'])): ?>
                    <img src="uploads/<?php echo $buku['gambar']; ?>" alt="Cover Buku">
                <?php else: ?>
                    <div class="no-img">Tidak Ada Cover</div>
                <?php endif; ?>
            </div>

            <div class="info-col">
                <h1><?php echo htmlspecialchars($buku['judul']); ?></h1>

                <!-- Status Ketersediaan Real-Time -->
                <?php if ($buku['stok'] > 0): ?>
                    <div class="status-box status-ready">
                        <strong>Status: Tersedia di Perpustakaan</strong> &mdash; Sisa <?php echo $buku['stok']; ?> eksemplar siap dipinjam.
                    </div>
                <?php else: ?>
                    <div class="status-box status-out">
                        <strong>Status: Sedang Dipinjam Semua</strong>
                        <?php if ($estimasi_kembali): ?>
                            <br>Perkiraan tersedia kembali pada: <strong><?php echo $estimasi_kembali; ?></strong>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <table class="meta-table">
                    <tr>
                        <td>Penulis</td>
                        <td><?php echo htmlspecialchars($buku['penulis']); ?></td>
                    </tr>
                    <tr>
                        <td>Penerbit</td>
                        <td><?php echo htmlspecialchars($buku['penerbit'] ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <td>Tahun Terbit</td>
                        <td><?php echo htmlspecialchars($buku['tahun'] ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <td>Kategori</td>
                        <td><?php echo htmlspecialchars($buku['kategori']); ?></td>
                    </tr>
                    <tr>
                        <td>Nomor / Lokasi Rak</td>
                        <td><strong>Rak <?php echo htmlspecialchars($buku['lokasi_rak'] ?: '-'); ?></strong></td>
                    </tr>
                </table>

                <div class="sinopsis-box">
                    <h3 style="margin: 0 0 8px; font-size: 16px;">Sinopsis</h3>
                    <p style="margin: 0;">
                        <?php echo !empty($buku['sinopsis']) ? nl2br(htmlspecialchars($buku['sinopsis'])) : 'Sinopsis untuk buku ini belum tersedia.'; ?>
                    </p>
                </div>

                <a href="katalog.php" class="btn-back">&larr; Kembali ke Katalog</a>
            </div>
        </div>
    </div>

</body>
</html>