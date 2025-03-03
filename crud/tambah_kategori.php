<?php
ob_start();
session_start();

include "../partials/navbar.php";
include "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["nama_kategori"])) {
        $_SESSION['error'] = "Nama kategori tidak boleh kosong!";
        header("Location: tambah_kategori.php");
        exit();
    }
    
    $nama_kategori = mysqli_real_escape_string($conn, htmlspecialchars($_POST["nama_kategori"]));    

    // Cek apakah kategori sudah ada
    $cek_query = "SELECT COUNT(*) as total FROM kategori WHERE nama_kategori = '$nama_kategori'";
    $cek_result = mysqli_query($conn, $cek_query);
    $cek_data = mysqli_fetch_assoc($cek_result);

    if ($cek_data['total'] > 0) {
        header("Location: tambah_kategori.php?error=exists");
    } else {
        $query = "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')";
        if (mysqli_query($conn, $query)) {
            header("Location: tambah_kategori.php?success=1");
        } else {
            header("Location: tambah_kategori.php?error=failed");
        }
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Kategori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Tambah Kategori</h2>
    <?php
    if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success">Kategori berhasil ditambahkan!</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'exists'): ?>
        <div class="alert alert-danger">Kategori sudah ada! Gunakan nama lain.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'failed'): ?>
        <div class="alert alert-danger">Terjadi kesalahan saat menambahkan kategori.</div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nama Kategori</label>
            <input type="text" name="nama_kategori" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="../index.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>
</body>
</html>
