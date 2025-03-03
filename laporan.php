<?php
include "config/database.php";
include "partials/navbar.php";

// Proses filter transaksi berdasarkan jenis dan tanggal
$filter_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

$query = "SELECT transaksi.*, barang.nama_barang, barang.satuan FROM transaksi 
          JOIN barang ON transaksi.barang_id = barang.id 
          WHERE 1=1";

if ($filter_jenis) {
    $query .= " AND transaksi.jenis = '$filter_jenis'";
}
if ($filter_tanggal) {
    $query .= " AND DATE(transaksi.tanggal) = '$filter_tanggal'";
}
$query .= " ORDER BY transaksi.tanggal DESC";

$transaksi = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function confirmDelete(id) {
            if (confirm("Apakah Anda yakin ingin menghapus transaksi ini?")) {
                window.location.href = "hapus_transaksi.php?id=" + id;
            }
        }
    </script>
</head>
<body>

<div class="container mt-4">
    <h2>Laporan Transaksi Barang</h2>
    
    <form method="GET" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Jenis Transaksi</label>
            <select name="jenis" class="form-control">
                <option value="">Semua</option>
                <option value="masuk" <?= ($filter_jenis == 'masuk') ? 'selected' : '' ?>>Barang Masuk</option>
                <option value="keluar" <?= ($filter_jenis == 'keluar') ? 'selected' : '' ?>>Barang Keluar</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?= $filter_tanggal ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    
    <!-- Tabel Laporan Transaksi -->
    <table class="table table-bordered mt-4">
        <thead class="table-dark text-center">
            <tr>
                <th>No</th> 
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Jenis</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; // Nomor urut dimulai dari 1
            while ($row = mysqli_fetch_assoc($transaksi)) { ?>
                <tr class="text-center">
                    <td><?= $no++; ?></td> <!-- Menggunakan nomor urut -->
                    <td><?= $row['nama_barang']; ?></td>
                    <td><?= $row['jumlah']; ?></td>
                    <td><?= $row['satuan']; ?></td>
                    <td><span class="badge bg-<?= $row['jenis'] == 'masuk' ? 'success' : 'danger' ?>">
                        <?= ucfirst($row['jenis']); ?>
                    </span></td>
                    <td><?= $row['tanggal']; ?></td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $row['id']; ?>)">Hapus</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
