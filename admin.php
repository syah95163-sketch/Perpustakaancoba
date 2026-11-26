<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['peran']) || $_SESSION['peran'] != "admin") {
    header("location:index.php");
    exit;
}

// Proses Ubah Status Buku (Aktif / Nonaktif untuk buku hilang/rusak)
if (isset($_GET['toggle_id']) && isset($_GET['status'])) {
    $id_buku = mysqli_real_escape_string($koneksi, $_GET['toggle_id']);
    $status_baru = ($_GET['status'] == 'Aktif') ? 'Nonaktif' : 'Aktif';
    mysqli_query($koneksi, "UPDATE buku SET status_buku='$status_baru' WHERE id_buku='$id_buku'");
    header("location:admin.php");
    exit;
}

// Mengambil Data Metrik Dashboard
$jml_buku = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku"))['total'];
$jml_anggota = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengguna WHERE peran='pengunjung'"))['total'];
$jml_dipinjam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status='Dipinjam'"))['total'];
$jml_terlambat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status='Dipinjam' AND tenggat_kembali < CURDATE()"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Perpustakaan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f9; color: #333; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #2c3e50; color: #fff; padding: 12px 20px; border-radius: 8px; }
        .nav-links a { color: #ecf0f1; text-decoration: none; margin-right: 15px; font-weight: bold; padding: 6px 10px; border-radius: 4px; }
        .nav-links a:hover, .nav-links a.active { background: #34495e; }
        .btn-logout { background: #e74c3c; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        
        .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); text-align: center; border-top: 4px solid #007BFF; }
        .card.warning { border-top-color: #e74c3c; }
        .card h3 { margin: 0; font-size: 14px; color: #666; }
        .card h2 { margin: 10px 0 0; font-size: 28px; color: #2c3e50; }
        
        .section-box { background: #fff; padding: 20px; border-radius: 8px; margin-top: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        th { background-color: #007BFF; color: white; }
        .th-danger { background-color: #e74c3c; }
        
        .btn { display: inline-block; background: #28a745; color: white; padding: 8px 14px; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .badge-aktif { background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-nonaktif { background: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="nav-links">
            <a href="admin.php" class="active">Dashboard & Buku</a>
            <a href="anggota.php">Manajemen Anggota</a>
            <a href="sirkulasi.php">Sirkulasi (Kasir)</a>
        </div>
        <div>
            <span style="margin-right: 15px;">Admin: <?php echo $_SESSION['nama']; ?></span>
            <a href="logout.php" class="btn-logout">Keluar</a>
        </div>
    </div>

    <!-- Ringkasan Metrik -->
    <div class="metrics">
        <div class="card">
            <h3>Total Judul Buku</h3>
            <h2><?php echo $jml_buku; ?></h2>
        </div>
        <div class="card">
            <h3>Total Anggota Aktif</h3>
            <h2><?php echo $jml_anggota; ?></h2>
        </div>
        <div class="card">
            <h3>Sedang Dipinjam Hari Ini</h3>
            <h2><?php echo $jml_dipinjam; ?></h2>
        </div>
        <div class="card warning">
            <h3>Pengembalian Terlambat</h3>
            <h2 style="color: #e74c3c;"><?php echo $jml_terlambat; ?></h2>
        </div>
    </div>

    <!-- Daftar Peringatan Pengembalian Terlambat -->
    <div class="section-box">
        <h3 style="margin-top: 0; color: #e74c3c;">Peringatan Pengembalian Terlambat</h3>
        <table>
            <tr>
                <th class="th-danger">ID Transaksi</th>
                <th class="th-danger">Nama Anggota</th>
                <th class="th-danger">Kontak</th>
                <th class="th-danger">Judul Buku</th>
                <th class="th-danger">Tenggat Kembali</th>
                <th class="th-danger">Keterlambatan</th>
                <th class="th-danger">Estimasi Denda</th>
            </tr>
            <?php
            $query_telat = mysqli_query($koneksi, "
                SELECT p.id_transaksi, u.nama, u.kontak, b.judul, p.tenggat_kembali,
                       DATEDIFF(CURDATE(), p.tenggat_kembali) AS telat_hari
                FROM peminjaman p
                JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                JOIN buku b ON p.id_buku = b.id_buku
                WHERE p.status = 'Dipinjam' AND p.tenggat_kembali < CURDATE()
                ORDER BY p.tenggat_kembali ASC
            ");

            if (mysqli_num_rows($query_telat) > 0) {
                while ($telat = mysqli_fetch_assoc($query_telat)) {
                    $estimasi_denda = $telat['telat_hari'] * 1000; // Tarif Rp 1.000/hari
            ?>
            <tr>
                <td>#<?php echo $telat['id_transaksi']; ?></td>
                <td><?php echo $telat['nama']; ?></td>
                <td><?php echo $telat['kontak']; ?></td>
                <td><?php echo $telat['judul']; ?></td>
                <td><?php echo $telat['tenggat_kembali']; ?></td>
                <td style="color: #e74c3c; font-weight: bold;"><?php echo $telat['telat_hari']; ?> Hari</td>
                <td>Rp <?php echo number_format($estimasi_denda, 0, ',', '.'); ?></td>
            </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='7' style='text-align:center; color:#666;'>Tidak ada peminjaman yang melewati tenggat hari ini.</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Manajemen Katalog Buku -->
    <div class="section-box">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">Manajemen Katalog Buku</h3>
            <a href="tambah_buku.php" class="btn">+ Tambah Buku Baru</a>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Informasi Buku</th>
                <th>Kategori & Rak</th>
                <th>Stok</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php
            $data_buku = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY id_buku DESC");
            while ($buku = mysqli_fetch_assoc($data_buku)) {
                $status = $buku['status_buku'];
            ?>
            <tr>
                <td><?php echo $buku['id_buku']; ?></td>
                <td>
                    <strong><?php echo $buku['judul']; ?></strong><br>
                    <small>Penulis: <?php echo $buku['penulis']; ?> | Penerbit: <?php echo $buku['penerbit']; ?></small>
                </td>
                <td><?php echo $buku['kategori']; ?> (Rak: <?php echo $buku['lokasi_rak']; ?>)</td>
                <td><?php echo $buku['stok']; ?></td>
                <td>
                    <span class="<?php echo ($status == 'Aktif') ? 'badge-aktif' : 'badge-nonaktif'; ?>">
                        <?php echo $status; ?>
                    </span>
                </td>
                <td>
                    <a href="edit_buku.php?id=<?php echo $buku['id_buku']; ?>" style="color: #007BFF;">Edit</a> | 
                    <a href="admin.php?toggle_id=<?php echo $buku['id_buku']; ?>&status=<?php echo $status; ?>" style="color: #e67e22;">
                        <?php echo ($status == 'Aktif') ? 'Nonaktifkan' : 'Aktifkan'; ?>
                    </a> | 
                    <a href="hapus_buku.php?id=<?php echo $buku['id_buku']; ?>" style="color: #e74c3c;" onclick="return confirm('Hapus buku ini secara permanen?');">Hapus</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

</body>
</html>