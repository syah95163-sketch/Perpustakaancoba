<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['peran']) || $_SESSION['peran'] != "admin") { header("location:index.php"); exit; }

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku='$id'");
$buku = mysqli_fetch_assoc($query);

if (isset($_POST['update'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $penerbit = mysqli_real_escape_string($koneksi, $_POST['penerbit']);
    $tahun = mysqli_real_escape_string($koneksi, $_POST['tahun']);
    $sinopsis = mysqli_real_escape_string($koneksi, $_POST['sinopsis']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $lokasi_rak = mysqli_real_escape_string($koneksi, $_POST['lokasi_rak']);
    $stok = (int) $_POST['stok'];
    $status_buku = mysqli_real_escape_string($koneksi, $_POST['status_buku']);
    $gambar_baru = $_FILES['gambar']['name'];

    if ($gambar_baru != "") {
        $tmp = $_FILES['gambar']['tmp_name'];
        if ($buku['gambar'] != "" && file_exists("uploads/" . $buku['gambar'])) {
            unlink("uploads/" . $buku['gambar']);
        }
        move_uploaded_file($tmp, "uploads/" . $gambar_baru);
        mysqli_query($koneksi, "UPDATE buku SET judul='$judul', penulis='$penulis', penerbit='$penerbit', tahun='$tahun', sinopsis='$sinopsis', kategori='$kategori', lokasi_rak='$lokasi_rak', stok='$stok', status_buku='$status_buku', gambar='$gambar_baru' WHERE id_buku='$id'");
    } else {
        mysqli_query($koneksi, "UPDATE buku SET judul='$judul', penulis='$penulis', penerbit='$penerbit', tahun='$tahun', sinopsis='$sinopsis', kategori='$kategori', lokasi_rak='$lokasi_rak', stok='$stok', status_buku='$status_buku' WHERE id_buku='$id'");
    }
    
    echo "<script>alert('Informasi buku berhasil diperbarui!'); window.location='admin.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head><title>Edit Buku</title></head>
<body style="font-family: Arial; padding: 20px; background: #f4f4f9;">
    <div style="background: white; padding: 20px; max-width: 450px; margin: auto; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h2>Edit Informasi Buku</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Judul Buku</label><br>
            <input type="text" name="judul" value="<?php echo $buku['judul']; ?>" required style="width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box;"><br>
            
            <label>Penulis</label><br>
            <input type="text" name="penulis" value="<?php echo $buku['penulis']; ?>" required style="width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box;"><br>

            <label>Penerbit</label><br>
            <input type="text" name="penerbit" value="<?php echo $buku['penerbit']; ?>" style="width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box;"><br>

            <label>Tahun Terbit</label><br>
            <input type="number" name="tahun" value="<?php echo $buku['tahun']; ?>" required style="width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box;"><br>

            <label>Sinopsis</label><br>
            <textarea name="sinopsis" style="width: 100%; padding: 8px; margin-bottom: 10px;" required><?php echo $buku['sinopsis']; ?></textarea><br>

            <label>Kategori</label><br>
            <input type="text" name="kategori" value="<?php echo $buku['kategori']; ?>" style="width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box;"><br>

            <label>Lokasi Rak</label><br>
            <input type="text" name="lokasi_rak" value="<?php echo $buku['lokasi_rak']; ?>" style="width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box;"><br>
            
            <label>Stok</label><br>
            <input type="number" name="stok" value="<?php echo $buku['stok']; ?>" required style="width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box;"><br>

            <label>Status Ketersediaan</label><br>
            <select name="status_buku" style="width: 100%; padding: 8px; margin-bottom: 10px;">
                <option value="Aktif" <?php if($buku['status_buku'] == 'Aktif') echo 'selected'; ?>>Aktif (Tampil di Katalog)</option>
                <option value="Nonaktif" <?php if($buku['status_buku'] == 'Nonaktif') echo 'selected'; ?>>Nonaktif (Hilang / Rusak)</option>
            </select><br>
            
            <label>Ganti Cover (Kosongkan jika tetap)</label><br>
            <input type="file" name="gambar" accept="image/*" style="margin-bottom: 20px;"><br>
            
            <button type="submit" name="update" style="background: #007BFF; color: white; padding: 10px; border: none; width: 100%; border-radius: 4px; cursor: pointer;">Simpan Perubahan</button>
            <a href="admin.php" style="display: block; text-align: center; margin-top: 10px; color: #e74c3c; text-decoration: none;">Batal</a>
        </form>
    </div>
</body>
</html>