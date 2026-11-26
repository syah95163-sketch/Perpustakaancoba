<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['peran']) || $_SESSION['peran'] != "admin") {
    header("location:index.php");
    exit;
}

// Proses Pendaftaran Anggota Baru
if (isset($_POST['daftar_anggota'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kontak = mysqli_real_escape_string($koneksi, $_POST['kontak']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    mysqli_query($koneksi, "INSERT INTO pengguna (nama, kontak, password, peran) VALUES ('$nama', '$kontak', '$password', 'pengunjung')");
    echo "<script>alert('Anggota baru berhasil didaftarkan!'); window.location='anggota.php';</script>";
}

// Proses Reset Kata Sandi ke Default ('123456')
if (isset($_GET['reset_id'])) {
    $id_anggota = mysqli_real_escape_string($koneksi, $_GET['reset_id']);
    mysqli_query($koneksi, "UPDATE pengguna SET password='123456' WHERE id_pengguna='$id_anggota' AND peran='pengunjung'");
    echo "<script>alert('Kata sandi anggota berhasil direset menjadi: 123456'); window.location='anggota.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Anggota - Perpustakaan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f9; color: #333; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #2c3e50; color: #fff; padding: 12px 20px; border-radius: 8px; }
        .nav-links a { color: #ecf0f1; text-decoration: none; margin-right: 15px; font-weight: bold; padding: 6px 10px; border-radius: 4px; }
        .nav-links a:hover, .nav-links a.active { background: #34495e; }
        .btn-logout { background: #e74c3c; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        
        .layout { display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap; }
        .form-box { flex: 1; min-width: 280px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); height: fit-content; }
        .table-box { flex: 2; min-width: 400px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); }
        
        input { width: 100%; padding: 8px; margin: 6px 0 14px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        th { background-color: #007BFF; color: white; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="nav-links">
            <a href="admin.php">Dashboard & Buku</a>
            <a href="anggota.php" class="active">Manajemen Anggota</a>
            <a href="sirkulasi.php">Sirkulasi (Kasir)</a>
        </div>
        <div>
            <span style="margin-right: 15px;">Admin: <?php echo $_SESSION['nama']; ?></span>
            <a href="logout.php" class="btn-logout">Keluar</a>
        </div>
    </div>

    <div class="layout">
        <!-- Formulir Pendaftaran Anggota -->
        <div class="form-box">
            <h3 style="margin-top: 0;">Daftarkan Anggota Baru</h3>
            <form method="POST">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Contoh: Siti Aminah" required>

                <label>Nomor HP / Kontak</label>
                <input type="text" name="kontak" placeholder="Contoh: 08123456789" required>

                <label>Kata Sandi Awal</label>
                <input type="text" name="password" value="123456" required>

                <button type="submit" name="daftar_anggota">Daftarkan Anggota</button>
            </form>
        </div>

        <!-- Daftar Anggota Terdaftar -->
        <div class="table-box">
            <h3 style="margin-top: 0;">Daftar Anggota Perpustakaan</h3>
            <table>
                <tr>
                    <th>ID Anggota</th>
                    <th>Nama Lengkap</th>
                    <th>Kontak</th>
                    <th>Aksi</th>
                </tr>
                <?php
                $anggota = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE peran='pengunjung' ORDER BY id_pengguna DESC");
                while ($row = mysqli_fetch_assoc($anggota)) {
                ?>
                <tr>
                    <td><strong><?php echo $row['id_pengguna']; ?></strong></td>
                    <td><?php echo $row['nama']; ?></td>
                    <td><?php echo $row['kontak']; ?></td>
                    <td>
                        <a href="kartu_anggota.php?id=<?php echo $row['id_pengguna']; ?>" target="_blank" style="color: #28a745; font-weight: bold;">Cetak Kartu</a> | 
                        <a href="anggota.php?reset_id=<?php echo $row['id_pengguna']; ?>" style="color: #e67e22;" onclick="return confirm('Reset kata sandi anggota ini menjadi 123456?');">Reset Sandi</a>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>

</body>
</html>