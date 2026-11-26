<?php
session_start();
include 'koneksi.php';

if ($_SESSION['peran'] != "admin") { header("location:index.php"); exit; }

if (isset($_POST['simpan'])) {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun = $_POST['tahun'];
    $sinopsis = $_POST['sinopsis'];
    $kategori = $_POST['kategori'];
    $stok = $_POST['stok'];
    $lokasi_rak = $_POST['lokasi_rak'];
    // Proses Gambar
    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    $path = "uploads/" . $gambar;
    
    // Pindahkan file gambar ke folder uploads
    if(move_uploaded_file($tmp, $path)) {
        mysqli_query($koneksi, "INSERT INTO buku (judul, penulis, penerbit, tahun, sinopsis, kategori, stok, lokasi_rak, gambar) VALUES ('$judul', '$penulis', '$penerbit', '$tahun', '$sinopsis', '$kategori', '$stok', '$lokasi_rak', '$gambar')");
        echo "<script>alert('Buku berhasil ditambahkan!'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Gagal mengunggah gambar!');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Tambah Buku</title></head>
<body style="font-family: Arial; padding: 20px; background: #f4f4f9;">
    <div style="background: white; padding: 20px; max-width: 400px; margin: auto; border-radius: 8px;">
        <h2>Tambah Buku Baru</h2>
        <!-- enctype="multipart/form-data" WAJIB ada jika form mengunggah file/gambar -->
        <form method="POST" enctype="multipart/form-data">
            <label>Judul Buku</label><br>
            <input type="text" name="judul" required style="width: 100%; padding: 8px; margin-bottom: 10px;"><br>
            
            <label>Penulis</label><br>
            <input type="text" name="penulis" required style="width: 100%; padding: 8px; margin-bottom: 10px;"><br>
            
            <label>Penerbit</label><br>
            <input type="text" name="penerbit" style="width: 100%; padding: 8px; margin-bottom: 10px;"><br>
            
            <label>Tahun Terbit</label><br>
            <input type="number" name="tahun" required style="width: 100%; padding: 8px; margin-bottom: 10px;"><br>
            
            <label>Sinopsis</label><br>
            <textarea name="sinopsis" style="width: 100%; padding: 8px; margin-bottom: 10px;" required></textarea><br>
            
            <label>Kategori</label><br>
            <input type="text" name="kategori" style="width: 100%; padding: 8px; margin-bottom: 10px;"><br>
            
            <label>Stok</label><br>
            <input type="number" name="stok" required style="width: 100%; padding: 8px; margin-bottom: 10px;"><br>
            
            <label>Lokasi Rak</label><br>
            <input type="text" name="lokasi_rak" style="width: 100%; padding: 8px; margin-bottom: 10px;"><br>
            
            <label>Upload Gambar Cover</label><br>
            <input type="file" name="gambar" required accept="image/*" style="margin-bottom: 20px;"><br>
            
            <button type="submit" name="simpan" style="background: #28a745; color: white; padding: 10px; border: none; width: 100%;">Simpan Buku</button>
            <a href="admin.php" style="display: block; text-align: center; margin-top: 10px; color: red;">Batal</a>
        </form>
    </div>
</body>
</html>