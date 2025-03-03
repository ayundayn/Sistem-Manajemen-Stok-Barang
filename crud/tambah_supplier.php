<?php
ob_start();
session_start(); 

include "../partials/navbar.php";
include "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // Validasi form kosong
    if (empty($_POST['nama'])) {
        $errors['nama'] = "Nama supplier tidak boleh kosong!";
    }
    if (empty($_POST['kontak'])) {
        $errors['kontak'] = "Kontak tidak boleh kosong!";
    }
    if (empty($_POST['alamat'])) {
        $errors['alamat'] = "Alamat tidak boleh kosong!";
    }

    if (count($errors) > 0) {
        // Simpan data lama ke session
        $_SESSION['old'] = [
            'nama' => $_POST['nama'],
            'kontak' => $_POST['kontak'],
            'alamat' => $_POST['alamat']
        ];
        
        // Cek apakah semua form kosong
        if (count($errors) === 3) {
            $_SESSION['main_error'] = "Form tidak boleh kosong!";
        } else {
            $_SESSION['errors'] = $errors;
        }
        header("Location: tambah_supplier.php");
        exit();
    }

    // Jika tidak ada error, proses insert data
    $nama = mysqli_real_escape_string($conn, htmlspecialchars($_POST['nama']));
    $kontak = mysqli_real_escape_string($conn, htmlspecialchars($_POST['kontak']));
    $alamat = mysqli_real_escape_string($conn, htmlspecialchars($_POST['alamat']));

    $query = "INSERT INTO supplier (nama, kontak, alamat) VALUES ('$nama', '$kontak', '$alamat')";
    mysqli_query($conn, $query);
    
    header("Location: tambah_supplier.php?success=1");
    exit;
}

unset($_SESSION['old']);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Tambah Supplier</h2>

        <!-- Tampilkan alert jika semua form kosong -->
        <?php if (isset($_SESSION['main_error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['main_error']; ?>
            </div>
            <?php unset($_SESSION['main_error']); ?>
        <?php endif; ?>

        <!-- Tampilkan alert jika ada error pada form -->
        <?php if (isset($_SESSION['errors'])): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?= $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php elseif (isset($_GET['success'])): ?>
            <div class="alert alert-success">Supplier berhasil ditambahkan!</div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label>Nama Supplier</label>
                <input type="text" name="nama" class="form-control" value="<?= isset($_SESSION['old']['nama']) ? $_SESSION['old']['nama'] : '' ?>">
            </div>
            <div class="mb-3">
                <label>Kontak</label>
                <input type="number" name="kontak" class="form-control" value="<?= isset($_SESSION['old']['kontak']) ? $_SESSION['old']['kontak'] : '' ?>">
            </div>
            <div class="mb-3">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control"><?= isset($_SESSION['old']['alamat']) ? $_SESSION['old']['alamat'] : '' ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Supplier</button>
        </form>
    </div>
</body>
</html>
