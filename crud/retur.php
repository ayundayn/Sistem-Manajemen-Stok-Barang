<?php
ob_start();
session_start();
include "../config/database.php";
include "../partials/navbar.php";

// Ambil data retur dari database
$query_retur = "SELECT r.id, b.nama_barang, b.satuan, s.nama AS nama_supplier, r.jumlah, r.alasan, r.tanggal, r.status 
                FROM retur_barang r
                JOIN barang b ON r.barang_id = b.id
                JOIN supplier s ON r.supplier_id = s.id
                ORDER BY r.tanggal DESC";

$result_retur = mysqli_query($conn, $query_retur);

// Ambil data barang dan supplier
$barang_result = mysqli_query($conn, "SELECT id, nama_barang FROM barang");
$supplier_result = mysqli_query($conn, "SELECT id, nama FROM supplier");

// Jika ada request untuk menyelesaikan retur
if (isset($_GET['done_id'])) {
    $retur_id = $_GET['done_id'];

    // Update status retur menjadi "Selesai"
    $query_done = "UPDATE retur_barang SET status = 'Selesai' WHERE id = '$retur_id'";
    $result_done = mysqli_query($conn, $query_done);

    if ($result_done) {
        $_SESSION['success'] = "Retur telah diselesaikan!";
    } else {
        $_SESSION['error'] = "Gagal memperbarui status retur!";
    }

    header("Location: retur.php");
    exit();
}

// Jika ada data yang dikirim (Tambah Retur)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];
    
    // Validasi form kosong
    if (empty($_POST['barang_id'])) {
        $errors['barang_id'] = "Barang harus dipilih!";
    }
    if (empty($_POST['supplier_id'])) {
        $errors['supplier_id'] = "Supplier harus dipilih!";
    }
    if (empty($_POST['jumlah'])) {
        $errors['jumlah'] = "Jumlah tidak boleh kosong!";
    }
    if (empty($_POST['alasan'])) {
        $errors['alasan'] = "Alasan tidak boleh kosong!";
    }

    if (count($errors) > 0) {
        // Simpan data lama ke session
        $_SESSION['old'] = [
            'barang_id' => $_POST['barang_id'],
            'supplier_id' => $_POST['supplier_id'],
            'jumlah' => $_POST['jumlah'],
            'alasan' => $_POST['alasan']
        ];

        $_SESSION['errors'] = $errors;

        header("Location: retur.php");
        exit();
    }

    // Mulai transaksi
    mysqli_begin_transaction($conn);

    $barang_id = $_POST["barang_id"];
    $supplier_id = $_POST["supplier_id"];
    $jumlah = $_POST["jumlah"];
    $alasan = mysqli_real_escape_string($conn, $_POST["alasan"]);

    $query_retur = "INSERT INTO retur_barang (barang_id, supplier_id, jumlah, alasan) VALUES ('$barang_id', '$supplier_id', '$jumlah', '$alasan')";
    $result_retur = mysqli_query($conn, $query_retur);

    $query_update_stok = "UPDATE barang SET stok = stok - $jumlah WHERE id = '$barang_id'";
    $result_update_stok = mysqli_query($conn, $query_update_stok);

    if ($result_retur && $result_update_stok) {
        mysqli_commit($conn);
        $_SESSION['success'] = "Retur barang berhasil disimpan!";
    } else {
        mysqli_rollback($conn);
        $_SESSION['error'] = "Gagal menyimpan retur barang!";
    }

    header("Location: retur.php");
    exit();
}

unset($_SESSION['old']);
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Retur Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h2>Data Retur Barang</h2>

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

    <!-- Tombol Tambah Retur -->
    <a href="tambah_retur.php" class="btn btn-primary mb-3">Tambah Retur</a>

    <!-- Tabel Retur Barang -->
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark text-center">
                <tr>
                    <th>No</th>
                    <th>Barang</th>
                    <th>Supplier</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                    <th>Alasan</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result_retur) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result_retur)): ?>
                        <tr class="text-center">
                            <td><?= $no++; ?></td>
                            <td><?= $row['nama_barang']; ?></td>
                            <td><?= $row['nama_supplier']; ?></td>
                            <td><?= $row['jumlah']; ?></td>
                            <td><?= $row['satuan']; ?></td> <!-- Menampilkan satuan dari database -->
                            <td><?= $row['alasan']; ?></td>
                            <td><?= $row['tanggal']; ?></td>
                            <td>
                                <?php if ($row['status'] == 'Selesai'): ?>
                                    <span class="badge bg-success">Selesai</span>
                                <?php else: ?>
                                    <a href="?done_id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">Selesaikan</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data retur</td>
                    </tr>
                <?php endif; ?>

                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    $(document).ready(function () {
                        $('#barang').on('change', function () {
                            var barangId = $(this).val();

                            if (barangId) {
                                $.ajax({
                                    url: 'get_supplier.php',
                                    type: 'GET',
                                    data: { barang_id: barangId },
                                    dataType: 'json',
                                    success: function (data) {
                                        $('#supplier').empty(); // Hapus semua opsi
                                        $('#supplier').append('<option value="">-- Pilih Supplier --</option>');
                                        
                                        $.each(data, function (index, supplier) {
                                            $('#supplier').append('<option value="' + supplier.id + '">' + supplier.nama + '</option>');
                                        });
                                    },
                                    error: function () {
                                        alert('Gagal mengambil data supplier.');
                                    }
                                });
                            } else {
                                $('#supplier').empty();
                                $('#supplier').append('<option value="">-- Pilih Supplier --</option>');
                            }
                        });
                    });
                </script>

            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Retur -->
<div class="modal fade" id="returModal" tabindex="-1" aria-labelledby="returModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returModalLabel">Tambah Retur Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="retur.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Pilih Barang</label>
                        <select name="barang_id" class="form-control">
                            <option value="">-- Pilih Barang --</option>
                            <?php while ($barang = mysqli_fetch_assoc($barang_result)) { ?>
                                <option value="<?= $barang['id']; ?>" <?= (isset($_SESSION['old']['barang_id']) && $_SESSION['old']['barang_id'] == $barang['id']) ? 'selected' : '' ?>>
                                    <?= $barang['nama_barang']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Supplier</label>
                        <select name="supplier_id" class="form-control">
                            <option value="">-- Pilih Supplier --</option>
                            <?php while ($supplier = mysqli_fetch_assoc($supplier_result)) { ?>
                                <option value="<?= $supplier['id']; ?>" <?= (isset($_SESSION['old']['supplier_id']) && $_SESSION['old']['supplier_id'] == $supplier['id']) ? 'selected' : '' ?>>
                                    <?= $supplier['nama']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" value="<?= isset($_SESSION['old']['jumlah']) ? $_SESSION['old']['jumlah'] : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan</label>
                        <textarea name="alasan" class="form-control"><?= isset($_SESSION['old']['alasan']) ? $_SESSION['old']['alasan'] : '' ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Simpan Retur</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
