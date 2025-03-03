<?php
ob_start();
session_start();

include "../config/database.php";
include "../partials/navbar.php";

// Ambil data barang dan supplier
$barang_result = mysqli_query($conn, "SELECT id, nama_barang, stok FROM barang WHERE stok > 0");
$supplier_result = mysqli_query($conn, "SELECT id, nama FROM supplier");

// Jika ada data yang dikirim (Tambah Retur)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // Validasi form kosong
    if (empty($_POST['barang_id']) && empty($_POST['supplier_id']) && empty($_POST['jumlah']) && empty($_POST['alasan'])) {
        $_SESSION['error'] = "Form tidak boleh kosong!";
        header("Location: tambah_retur.php");
        exit();
    }

    if (empty($_POST['barang_id'])) {
        $errors['barang_id'] = "Barang harus dipilih!";
    }
    if (empty($_POST['supplier_id'])) {
        $errors['supplier_id'] = "Supplier harus dipilih!";
    }
    if (empty($_POST['jumlah'])) {
        $errors['jumlah'] = "Jumlah tidak boleh kosong!";
    } elseif ($_POST['jumlah'] < 1) {
        $errors['jumlah'] = "Jumlah tidak boleh kurang dari 1!";
    }    
    if (empty($_POST['alasan'])) {
        $errors['alasan'] = "Alasan tidak boleh kosong!";
    }

    if (count($errors) > 0) {
        $_SESSION['errors'] = $errors;
        header("Location: tambah_retur.php");
        exit();
    }

    $barang_id = $_POST["barang_id"];
    $jumlah = $_POST["jumlah"];

    // Cek stok barang sebelum menyimpan retur
    $stok_result = mysqli_query($conn, "SELECT stok FROM barang WHERE id = '$barang_id'");
    $stok_data = mysqli_fetch_assoc($stok_result);
    $stok_tersedia = $stok_data['stok'];

    if ($jumlah > $stok_tersedia) {
        $_SESSION['error'] = "Jumlah retur melebihi stok yang tersedia!";
        header("Location: tambah_retur.php");
        exit();
    }

    // Mulai transaksi
    mysqli_begin_transaction($conn);

    $barang_id = $_POST["barang_id"];
    $supplier_id = $_POST["supplier_id"];
    $jumlah = $_POST["jumlah"];
    $alasan = mysqli_real_escape_string($conn, htmlspecialchars($_POST["alasan"]));

    $query_retur = "INSERT INTO retur_barang (barang_id, supplier_id, jumlah, alasan) VALUES ('$barang_id', '$supplier_id', '$jumlah', '$alasan')";
    $result_retur = mysqli_query($conn, $query_retur);

    $query_update_stok = "UPDATE barang SET stok = stok - $jumlah WHERE id = '$barang_id'";
    $result_update_stok = mysqli_query($conn, $query_update_stok);

    if ($result_retur && $result_update_stok) {
        mysqli_commit($conn);
        $_SESSION['success'] = "Retur barang berhasil disimpan!";
        header("Location: retur.php");
        exit();
    } else {
        mysqli_rollback($conn);
        $_SESSION['error'] = "Gagal menyimpan retur barang!";
        header("Location: tambah_retur.php");
        exit();
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Retur Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h2>Tambah Retur Barang</h2>

    <!-- Tampilkan Notifikasi -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <form action="tambah_retur.php" method="POST">
        <div class="mb-3">
            <label class="form-label">Pilih Barang</label>
            <select name="barang_id" class="form-control" id="barang">
                <option value="">-- Pilih Barang --</option>
                <?php while ($barang = mysqli_fetch_assoc($barang_result)) { ?>
                    <?php if ($barang['stok'] > 0): ?>
                        <option value="<?= $barang['id']; ?>"><?= $barang['nama_barang']; ?> (Stok: <?= $barang['stok']; ?>)</option>
                    <?php endif; ?>
                <?php } ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Pilih Supplier</label>
            <select name="supplier_id" id="supplier_id" class="form-control">
                <option value="">-- Pilih Supplier --</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah</label>
            <input type="number" name="jumlah" class="form-control" min="1" oninput="validity.valid||(value='');" value="<?= $_SESSION['old']['jumlah'] ?? '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Alasan</label>
            <textarea name="alasan" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Simpan Retur</button>
        <a href="retur.php" class="btn btn-secondary">Batal</a>
    </form>

    <script>
        document.querySelector('select[name="barang_id"]').addEventListener('change', function () {
            const barangId = this.value;
            const supplierDropdown = document.getElementById('supplier_id');

            // Kosongkan dropdown supplier
            supplierDropdown.innerHTML = '<option value="">-- Pilih Supplier --</option>';

            if (barangId) {
                fetch(`get_supplier.php?barang_id=${barangId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            data.forEach(supplier => {
                                const option = document.createElement('option');
                                option.value = supplier.id;
                                option.text = supplier.nama;
                                supplierDropdown.add(option);
                            });
                        } else {
                            supplierDropdown.innerHTML = '<option value="">Tidak ada supplier</option>';
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    </script>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
