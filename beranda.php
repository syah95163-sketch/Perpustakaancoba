<?php
session_start();
include 'koneksi.php';

$is_anggota = (isset($_SESSION['peran']) && $_SESSION['peran'] == 'pengunjung');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beranda - Perpustakaan Kita</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; color: #333; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #007BFF; color: #fff; padding: 14px 30px; }
        .navbar a { color: #fff; text-decoration: none; margin-right: 18px; font-weight: bold; font-size: 14px; }
        .navbar a.active { text-decoration: underline; }
        .btn-auth { background: #fff; color: #007BFF !important; padding: 6px 14px; border-radius: 4px; margin-right: 0 !important; }
        .btn-logout { background: #e74c3c; color: #fff !important; padding: 6px 14px; border-radius: 4px; margin-right: 0 !important; }

        /* Hero Pencarian Utama */
        .hero { background: linear-gradient(135deg, #007BFF, #0056b3); color: white; text-align: center; padding: 55px 20px; }
        .hero h1 { margin: 0 0 10px; font-size: 28px; }
        .hero p { margin: 0 0 25px; opacity: 0.9; }
        .search-form { display: flex; justify-content: center; max-width: 600px; margin: auto; }
        .search-form input { flex: 1; padding: 12px 15px; border: none; border-radius: 6px 0 0 6px; font-size: 15px; outline: none; }
        .search-form button { padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 0 6px 6px 0; font-size: 15px; font-weight: bold; cursor: pointer; }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .section-header a { color: #007BFF; text-decoration: none; font-size: 14px; font-weight: bold; }

        .grid-buku { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .kartu { background: #fff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); display: flex; flex-direction: column; justify-content: space-between; }
        .kartu img { width: 100%; height: 180px; object-fit: cover; border-radius: 4px; }
        .no-img { height: 180px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #888; font-size: 13px; }
        .kartu h4 { margin: 12px 0 4px; font-size: 16px; color: #222; }
        .kartu p { margin: 0 0 10px; font-size: 13px; color: #666; }
        .badge-tersedia { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block; }
        .badge-habis { background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block; }
        .btn-detail { display: block; text-align: center; margin-top: 12px; padding: 8px; background: #007BFF; color: white; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="navbar">
        <div>
            <a href="beranda.php" class="active">Beranda</a>
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

    <!-- Kotak Pencarian Utama -->
    <div class="hero">
        <h1>Temukan Buku Favorit Anda</h1>
        <p>Jelajahi ribuan koleksi pengetahuan, fiksi, sejarah, dan referensi pelajaran.</p>
        <form class="search-form" action="katalog.php" method="GET">
            <input type="text" name="cari" placeholder="Ketik judul buku atau nama penulis..." required>
            <button type="submit">Cari Buku</button>
        </form>
    </div>

    <div class="container">
        <!-- Daftar Buku Kedatangan Terbaru -->
        <div class="section-header">
            <h2 style="margin: 0;">Kedatangan Terbaru</h2>
            <a href="katalog.php">Lihat Semua Katalog &rarr;</a>
        </div>
        <div class="grid-buku">
            <?php
            $terbaru = mysqli_query($koneksi, "SELECT * FROM buku WHERE status_buku='Aktif' ORDER BY id_buku DESC LIMIT 4");
            while ($b = mysqli_fetch_assoc($terbaru)) {
            ?>
            <div class="kartu">
                <div>
                    <?php if (!empty($b['gambar']) && file_exists('uploads/' . $b['gambar'])): ?>
                        <img src="uploads/<?php echo $b['gambar']; ?>" alt="Cover">
                    <?php else: ?>
                        <div class="no-img">Tidak Ada Cover</div>
                    <?php endif; ?>
                    <h4><?php echo htmlspecialchars($b['judul']); ?></h4>
                    <p><?php echo htmlspecialchars($b['penulis']); ?> &bull; <?php echo htmlspecialchars($b['kategori']); ?></p>
                </div>
                <div>
                    <?php if ($b['stok'] > 0): ?>
                        <span class="badge-tersedia">Tersedia (Stok: <?php echo $b['stok']; ?>)</span>
                    <?php else: ?>
                        <span class="badge-habis">Sedang Dipinjam</span>
                    <?php endif; ?>
                    <a href="detail_buku.php?id=<?php echo $b['id_buku']; ?>" class="btn-detail">Lihat Detail</a>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- Daftar Buku Paling Populer / Direkomendasikan -->
        <div class="section-header">
            <h2 style="margin: 0;">Paling Populer & Direkomendasikan</h2>
            <a href="katalog.php">Eksplorasi Koleksi &rarr;</a>
        </div>
        <div class="grid-buku">
            <?php
            $populer = mysqli_query($koneksi, "
                SELECT b.*, COUNT(p.id_transaksi) AS total_dipinjam
                FROM buku b
                LEFT JOIN peminjaman p ON b.id_buku = p.id_buku
                WHERE b.status_buku = 'Aktif'
                GROUP BY b.id_buku
                ORDER BY total_dipinjam DESC, b.id_buku ASC
                LIMIT 4
            ");
            while ($b = mysqli_fetch_assoc($populer)) {
            ?>
            <div class="kartu">
                <div>
                    <?php if (!empty($b['gambar']) && file_exists('uploads/' . $b['gambar'])): ?>
                        <img src="uploads/<?php echo $b['gambar']; ?>" alt="Cover">
                    <?php else: ?>
                        <div class="no-img">Tidak Ada Cover</div>
                    <?php endif; ?>
                    <h4><?php echo htmlspecialchars($b['judul']); ?></h4>
                    <p><?php echo htmlspecialchars($b['penulis']); ?> (Dipinjam <?php echo $b['total_dipinjam']; ?>x)</p>
                </div>
                <div>
                    <?php if ($b['stok'] > 0): ?>
                        <span class="badge-tersedia">Tersedia (Stok: <?php echo $b['stok']; ?>)</span>
                    <?php else: ?>
                        <span class="badge-habis">Sedang Dipinjam</span>
                    <?php endif; ?>
                    <a href="detail_buku.php?id=<?php echo $b['id_buku']; ?>" class="btn-detail">Lihat Detail</a>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

</body>
</html>