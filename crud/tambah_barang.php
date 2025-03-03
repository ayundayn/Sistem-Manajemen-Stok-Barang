<?php
ob_start();
session_start();

include "../config/database.php";
include "../partials/navbar.php";

// Ambil data kategori & supplier
$kategori_result = mysqli_query($conn, "SELECT * FROM kategori");
$supplier_result = mysqli_query($conn, "SELECT * FROM supplier");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // Validasi form kosong
    if (empty($_POST['nama_barang'])) {
        $errors['nama_barang'] = "Nama barang tidak boleh kosong!";
    }
    if (empty($_POST['kategori_id'])) {
        $errors['kategori_id'] = "Kategori harus dipilih!";
    }
    if (empty($_POST['supplier_id'])) {
        $errors['supplier_id'] = "Supplier harus dipilih!";
    }

    if (count($errors) > 0) {

        // Simpan data lama ke session
        $_SESSION['old'] = [
            'nama_barang' => $_POST['nama_barang'],
            'kategori_id' => $_POST['kategori_id'],
            'supplier_id' => $_POST['supplier_id']
        ];
        
        // Cek apakah semua form kosong
        if (count($errors) === 3) {
            $_SESSION['main_error'] = "Form tidak boleh kosong!";
        } else {
            $_SESSION['errors'] = $errors;
        }
        
        header("Location: tambah_barang.php");
        exit();
    }    

    $nama_barang = mysqli_real_escape_string($conn, htmlspecialchars($_POST['nama_barang']));
    $kategori_id = $_POST['kategori_id'];
    $supplier_id = $_POST['supplier_id'];

    // Cek apakah barang sudah ada
    $cek_query = "SELECT COUNT(*) as count FROM barang WHERE nama_barang = '$nama_barang'";
    $cek_result = mysqli_query($conn, $cek_query);
    $row = mysqli_fetch_assoc($cek_result);

    if ($row['count'] > 0) {
        // Jika barang sudah ada, beri notifikasi
        header("Location: tambah_barang.php?error=Barang sudah ada");
        exit;
    }

    // Set stok menjadi 0
    $stok = 0;

    $query = "INSERT INTO barang (nama_barang, kategori_id, supplier_id) 
              VALUES ('$nama_barang', '$kategori_id','$supplier_id')";
    mysqli_query($conn, $query);

    // Ambil ID barang yang baru saja dimasukkan
    $barang_id = mysqli_insert_id($conn);

    // Insert ke tabel transaksi dengan jenis 'masuk'
    $query_transaksi = "INSERT INTO transaksi (barang_id, jumlah, jenis, tanggal) 
                        VALUES ('$barang_id', 0, 'masuk', NOW())";
    mysqli_query($conn, $query_transaksi);

    header("Location: tambah_barang.php?success=1");
    exit;
}

unset($_SESSION['old']);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Tambah Barang</h2>

    <?php if (isset($_SESSION['main_error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['main_error']; ?>
        </div>
        <?php unset($_SESSION['main_error']); ?>
    <?php endif; ?>
    
    <?php 
    if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success">Barang berhasil ditambahkan!</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= $_GET['error']; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label>Nama Barang</label>
            <input type="text" name="nama_barang" class="form-control" value="<?= isset($_SESSION['old']['nama_barang']) ? $_SESSION['old']['nama_barang'] : '' ?>">
            <?php if (isset($_SESSION['errors']['nama_barang'])): ?>
                <small class="text-danger"><?= $_SESSION['errors']['nama_barang']; ?></small>
            <?php endif; ?>
        </div>
        
        <div class="mb-3">
            <label>Kategori</label>
            <select name="kategori_id" id="kategori_id" class="form-control" onchange="toggleStok()">
                <option value="">Pilih Kategori</option>
                <?php while ($row = mysqli_fetch_assoc($kategori_result)) { ?>
                    <option value="<?= $row['id']; ?>" <?= (isset($_SESSION['old']['kategori_id']) && $_SESSION['old']['kategori_id'] == $row['id']) ? 'selected' : '' ?>>
                        <?= $row['nama_kategori']; ?>
                    </option>
                <?php } ?>
            </select>
            <?php if (isset($_SESSION['errors']['kategori_id'])): ?>
                <small class="text-danger"><?= $_SESSION['errors']['kategori_id']; ?></small>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label>Supplier</label>
            <select name="supplier_id" class="form-control">
                <option value="">Pilih Supplier</option>
                <?php while ($row = mysqli_fetch_assoc($supplier_result)) { ?>
                    <option value="<?= $row['id']; ?>" <?= (isset($_SESSION['old']['supplier_id']) && $_SESSION['old']['supplier_id'] == $row['id']) ? 'selected' : '' ?>>
                        <?= $row['nama']; ?>
                    </option>
                <?php } ?>
            </select>
            <?php if (isset($_SESSION['errors']['supplier_id'])): ?>
                <small class="text-danger"><?= $_SESSION['errors']['supplier_id']; ?></small>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Tambah Barang</button>
    </form>
</div>
</body>
</html>
