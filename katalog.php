<?php
session_start();
include 'koneksi.php';

$is_anggota = (isset($_SESSION['peran']) && $_SESSION['peran'] == 'pengunjung');

// Menangkap parameter pencarian dan filter dari URL (GET)
$cari = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';
$kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';
$ketersediaan = isset($_GET['ketersediaan']) ? mysqli_real_escape_string($koneksi, $_GET['ketersediaan']) : '';

// Menyusun query SQL secara dinamis berdasarkan filter yang dipilih
$kondisi = ["status_buku = 'Aktif'"];

if ($cari != '') {
    $kondisi[] = "(judul LIKE '%$cari%' OR penulis LIKE '%$cari%')";
}
if ($kategori != '') {
    $kondisi[] = "kategori = '$kategori'";
}
if ($ketersediaan == 'tersedia') {
    $kondisi[] = "stok > 0";
} elseif ($ketersediaan == 'dipinjam') {
    $kondisi[] = "stok <= 0";
}

$where_sql = implode(" AND ", $kondisi);
$query_buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE $where_sql ORDER BY id_buku DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Buku - Perpustakaan Kita</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; color: #333; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #007BFF; color: #fff; padding: 14px 30px; }
        .navbar a { color: #fff; text-decoration: none; margin-right: 18px; font-weight: bold; font-size: 14px; }
        .navbar a.active { text-decoration: underline; }
        .btn-auth { background: #fff; color: #007BFF !important; padding: 6px 14px; border-radius: 4px; margin-right: 0 !important; }
        .btn-logout { background: #e74c3c; color: #fff !important; padding: 6px 14px; border-radius: 4px; margin-right: 0 !important; }

        .container { max-width: 1100px; margin: 25px auto; padding: 0 20px; }
        .filter-box { background: #fff; padding: 18px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
        .filter-box input, .filter-box select { padding: 9px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .filter-box input { flex: 2; min-width: 200px; }
        .filter-box select { flex: 1; min-width: 160px; }
        .btn-filter { padding: 9px 18px; background: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-reset { padding: 9px 14px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; }

        .grid-katalog { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-top: 25px; }
        .kartu-buku { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); display: flex; flex-direction: column; justify-content: space-between; }
        .kartu-buku img { width: 100%; height: 185px; object-fit: cover; border-radius: 4px; }
        .no-img { height: 185px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #888; font-size: 13px; }
        .kartu-buku h4 { margin: 12px 0 4px; color: #222; }
        .kartu-buku p { margin: 0 0 12px; color: #666; font-size: 13px; }
        .badge-tersedia { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block; }
        .badge-habis { background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block; }
        .btn-detail { display: block; text-align: center; margin-top: 12px; padding: 8px; background: #007BFF; color: white; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="navbar">
        <div>
            <a href="beranda.php">Beranda</a>
            <a href="katalog.php" class="active">Katalog Buku</a>
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
        <h2 style="margin-top: 0;">Katalog Koleksi Buku</h2>

        <!-- Bilah Pencarian & Filter -->
        <form method="GET" class="filter-box">
            <input type="text" name="cari" value="<?php echo htmlspecialchars($cari); ?>" placeholder="Cari judul buku atau penulis...">
            
            <select name="kategori">
                <option value="">-- Semua Kategori --</option>
                <option value="Fiksi" <?php if ($kategori == 'Fiksi') echo 'selected'; ?>>Fiksi</option>
                <option value="Pelajaran" <?php if ($kategori == 'Pelajaran') echo 'selected'; ?>>Pelajaran</option>
                <option value="Sejarah" <?php if ($kategori == 'Sejarah') echo 'selected'; ?>>Sejarah</option>
            </select>

            <select name="ketersediaan">
                <option value="">-- Semua Status --</option>
                <option value="tersedia" <?php if ($ketersediaan == 'tersedia') echo 'selected'; ?>>Tersedia</option>
                <option value="dipinjam" <?php if ($ketersediaan == 'dipinjam') echo 'selected'; ?>>Sedang Dipinjam</option>
            </select>

            <button type="submit" class="btn-filter">Terapkan Filter</button>
            <a href="katalog.php" class="btn-reset">Reset</a>
        </form>

        <!-- Daftar Grid Buku -->
        <div class="grid-katalog">
            <?php if (mysqli_num_rows($query_buku) > 0): ?>
                <?php while ($buku = mysqli_fetch_assoc($query_buku)): ?>
                <div class="kartu-buku">
                    <div>
                        <?php if (!empty($buku['gambar']) && file_exists('uploads/' . $buku['gambar'])): ?>
                            <img src="uploads/<?php echo $buku['gambar']; ?>" alt="Cover Buku">
                        <?php else: ?>
                            <div class="no-img">Tidak Ada Cover</div>
                        <?php endif; ?>
                        <h4><?php echo htmlspecialchars($buku['judul']); ?></h4>
                        <p><?php echo htmlspecialchars($buku['penulis']); ?> &bull; <?php echo htmlspecialchars($buku['kategori']); ?></p>
                    </div>
                    <div>
                        <?php if ($buku['stok'] > 0): ?>
                            <span class="badge-tersedia">Tersedia (Stok: <?php echo $buku['stok']; ?>)</span>
                        <?php else: ?>
                            <span class="badge-habis">Sedang Dipinjam</span>
                        <?php endif; ?>
                        <a href="detail_buku.php?id=<?php echo $buku['id_buku']; ?>" class="btn-detail">Lihat Detail</a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; background: #fff; padding: 30px; border-radius: 8px; color: #666;">
                    Buku yang Anda cari tidak ditemukan. Silakan ubah kata kunci atau filter.
                </p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>