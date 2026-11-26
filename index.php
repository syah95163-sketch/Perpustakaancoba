<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $query = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE nama='$nama' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['id_pengguna'] = $data['id_pengguna'];
        $_SESSION['peran'] = $data['peran'];

        if ($data['peran'] == 'admin') {
            header("location:admin.php");
        } else {
            header("location:beranda.php");
        }
        exit;
    } else {
        echo "<script>alert('Nama atau Password salah!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Perpustakaan</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 320px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 8px 0 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #0056b3; }
        .guest-link { display: block; text-align: center; margin-top: 15px; padding-top: 12px; border-top: 1px solid #eee; color: #28a745; text-decoration: none; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 style="text-align: center; margin-top: 0;">Perpustakaan Kita</h2>
        <form method="POST" action="">
            <label>Nama Pengguna</label>
            <input type="text" name="nama" required>
            
            <label>Password</label>
            <input type="password" name="password" required>
            
            <button type="submit" name="login">Masuk</button>
        </form>
        <a href="beranda.php" class="guest-link">Jelajahi sebagai Pengunjung Umum &rarr;</a>
    </div>
</body>
</html>