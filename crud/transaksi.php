<?php
ob_start();
session_start();

include "../config/database.php";
include "../partials/navbar.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // Cek jika semua form kosong
    if (
        empty(trim($_POST['barang_id'])) &&
        empty(trim($_POST['jumlah'])) &&
        empty(trim($_POST['satuan'])) &&
        empty(trim($_POST['jenis']))
    ) {
        $errors['form_kosong'] = "Form tidak boleh kosong!";
    } else {
        // Validasi form kosong secara spesifik
        if (empty(trim($_POST['barang_id']))) {
            $errors['barang_id'] = "Barang harus dipilih!";
        }
        if (empty(trim($_POST['jumlah']))) {
            $errors['jumlah'] = "Jumlah tidak boleh kosong!";
        }
        if (empty(trim($_POST['jenis']))) {
            $errors['jenis'] = "Jenis transaksi harus dipilih!";
        }
    }

    // Cek jika ada error
    if (count($errors) > 0) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = [
            'barang_id' => $_POST['barang_id'],
            'jumlah' => $_POST['jumlah'],
            'jenis' => $_POST['jenis']
        ];
        
        header("Location: transaksi.php");
        exit();
    }

    $barang_id = $_POST["barang_id"];
    $jumlah = $_POST["jumlah"];
    $jenis = $_POST["jenis"];
    
    // Ambil satuan barang berdasarkan barang_id yang dipilih
    $query_satuan = "SELECT satuan FROM barang WHERE id = '$barang_id'";
    $result_satuan = mysqli_query($conn, $query_satuan);
    $row_satuan = mysqli_fetch_assoc($result_satuan);
    $satuan = $row_satuan["satuan"];

    // Ambil stok barang saat ini
    $query_stok = "SELECT stok FROM barang WHERE id = '$barang_id'";
    $result_stok = mysqli_query($conn, $query_stok);
    $row = mysqli_fetch_assoc($result_stok);
    $stok_sekarang = $row["stok"];

    // Tambahkan pengecekan ini
    if ($jenis == "keluar" && $stok_sekarang < $jumlah) {
        header("Location: transaksi.php?error=Stok tidak mencukupi.");
        exit();
    }

    if ($jenis == "keluar" && $stok_sekarang <= 0) {
        header("Location: transaksi.php?error=Barang dengan stok kosong hanya bisa dilakukan transaksi Barang Masuk.");
        exit();
    }    

    if ($jumlah <= 0) {
        header("Location: transaksi.php?error=Jumlah harus lebih dari 0.");
        exit();
    }    

    // Simpan ke tabel transaksi
    $sql = "INSERT INTO transaksi (barang_id, jumlah, jenis, tanggal) 
            VALUES ('$barang_id', '$jumlah', '$jenis', NOW())";
    if (!mysqli_query($conn, $sql)) {
        header("Location: transaksi.php?error=Transaksi gagal!");
        exit();
    }

    // Update stok barang
    $sql_update = ($jenis == 'masuk') 
        ? "UPDATE barang SET stok = stok + $jumlah WHERE id = '$barang_id'"
        : "UPDATE barang SET stok = stok - $jumlah WHERE id = '$barang_id'";

    if (!mysqli_query($conn, $sql_update)) {
        header("Location: transaksi.php?error=Gagal mengupdate stok.");
        exit();
    }

    unset($_SESSION['old']);
    header("Location: transaksi.php?success=1");
    exit();
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Form Transaksi Barang</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Transaksi berhasil ditambahkan!</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= $_GET['error']; ?></div>
    <?php elseif (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <?php if (isset($_SESSION['errors']['form_kosong'])): ?>
                <?= $_SESSION['errors']['form_kosong']; ?>
            <?php else: ?>
                <ul>
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?= $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Pilih Barang</label>
            <select name="barang_id" class="form-control">
                <option value="">Pilih Barang</option>
                <?php
                $result = mysqli_query($conn, "SELECT * FROM barang");
                while ($row = mysqli_fetch_assoc($result)) {
                    $selected = (isset($_SESSION['old']['barang_id']) && $_SESSION['old']['barang_id'] == $row['id']) ? 'selected' : '';
                    echo "<option value='{$row['id']}' $selected>{$row['nama_barang']} ({$row['stok']} tersedia)</option>";
                }                
                ?>
            </select>
            <?php if (isset($_SESSION['errors']['barang_id'])): ?>
                <div class="text-danger"><?= $_SESSION['errors']['barang_id']; ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Jumlah</label>
            <input type="number" name="jumlah" class="form-control" min="1" oninput="validity.valid||(value='');" value="<?= $_SESSION['old']['jumlah'] ?? '' ?>">
            <?php if (isset($_SESSION['errors']['jumlah'])): ?>
                <div class="text-danger"><?= $_SESSION['errors']['jumlah']; ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Jenis Transaksi</label>
            <select name="jenis" class="form-control">
                <option value="">Pilih Jenis</option>
                <!-- Opsi akan diperbarui oleh JavaScript berdasarkan stok -->
            </select>
            <?php if (isset($_SESSION['errors']['jenis'])): ?>
                <div class="text-danger"><?= $_SESSION['errors']['jenis']; ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="../index.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<script>
    // JavaScript untuk mengubah opsi jenis transaksi berdasarkan stok barang
    const barangSelect = document.querySelector('select[name="barang_id"]');
    const jenisSelect = document.querySelector('select[name="jenis"]');
    const stokData = <?= json_encode(mysqli_fetch_all(mysqli_query($conn, "SELECT id, stok FROM barang"), MYSQLI_ASSOC)); ?>;

    barangSelect.addEventListener('change', function() {
        const selectedBarangId = this.value;
        const barang = stokData.find(b => b.id === selectedBarangId);
        
        if (barang) {
            const stok = parseInt(barang.stok);
            
            // Reset opsi jenis transaksi
            jenisSelect.innerHTML = '<option value="">Pilih Jenis</option>';
            
            if (stok > 0) {
                jenisSelect.innerHTML += '<option value="masuk">Barang Masuk</option>';
                jenisSelect.innerHTML += '<option value="keluar">Barang Keluar</option>';
            } else {
                jenisSelect.innerHTML += '<option value="masuk">Barang Masuk</option>';
            }
        } else {
            // Jika tidak ada barang yang dipilih, reset dropdown
            jenisSelect.innerHTML = '<option value="">Pilih Jenis</option>';
        }
    });
</script>
</body>
</html>

</body>
</html>
