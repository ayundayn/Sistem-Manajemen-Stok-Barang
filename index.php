<?php
if (isset($_GET['refresh'])) {
    header("Cache-Control: no-cache, must-revalidate");
}

include "config/database.php";
include "partials/navbar.php";

// Gunakan variabel @rownum untuk menambahkan nomor urut
mysqli_query($conn, "SET @rownum = 0");
$result = mysqli_query($conn, "SELECT (@rownum := @rownum + 1) AS nomor, 
                                      barang.id, barang.nama_barang, kategori.nama_kategori, 
                                      kategori.id AS kategori_id, supplier.id AS supplier_id, 
                                      barang.stok, supplier.nama AS nama_supplier, barang.satuan 
                               FROM barang 
                               LEFT JOIN kategori ON barang.kategori_id = kategori.id
                               LEFT JOIN supplier ON barang.supplier_id = supplier.id");

// Ambil daftar kategori untuk dropdown modal
$kategori_result = mysqli_query($conn, "SELECT * FROM kategori");
$kategori_options = [];
while ($row = mysqli_fetch_assoc($kategori_result)) {
    $kategori_options[] = $row;
}

$query = "SELECT transaksi.*, barang.nama_barang, barang.satuan FROM transaksi 
          JOIN barang ON transaksi.barang_id = barang.id 
          WHERE 1=1";

$transaksi = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Toko Jaya Mandiri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function openEditBarangModal(button) {
            let id = button.getAttribute("data-id");
            let namaBarang = button.getAttribute("data-nama");
            let kategoriId = button.getAttribute("data-kategori");
            let supplierId = button.getAttribute("data-supplier");
            let satuan = button.getAttribute("data-satuan");

            document.getElementById("editBarangId").value = id;
            document.getElementById("editNamaBarang").innerText = namaBarang;
            document.getElementById("editSatuan").value = satuan;

            // Pilih kategori yang sesuai
            let selectKategori = document.getElementById("editKategori");
            for (let option of selectKategori.options) {
                option.selected = option.value == kategoriId;
            }

            // Pilih supplier yang sesuai
            let selectSupplier = document.getElementById("editSupplier");
            for (let option of selectSupplier.options) {
                option.selected = option.value == supplierId;
            }

            var modal = new bootstrap.Modal(document.getElementById('editBarangModal'));
            modal.show();
        }

        function confirmDelete(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus barang "${nama}"?`)) {
                window.location.href = "hapus_barang.php?id=" + id;
            }
        }
    </script>
</head>
<body>

<!-- Tabel Daftar Barang -->
<div class="container mt-4 mb-3">
    <div class="card shadow">
        <div class="card-body">
            <h2>Daftar Stok Barang</h2>
            <p>Daftar ini merupakan daftar barang yang tersedia di gudang.</p>

            <table class="table table-bordered">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Supplier</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr class="text-center">
                            <td><?= $row['nomor']; ?></td>
                            <td><?= $row['nama_barang']; ?></td>
                            <td><?= $row['nama_kategori'] ?: "Tidak ada kategori"; ?></td>
                            <td><?= $row['nama_supplier'] ?: "Tidak ada supplier"; ?></td>
                            <td><?= $row['stok']; ?></td>
                            <td><?= $row['satuan']; ?></td>
                            <td>
                                <div class="d-md-inline-flex d-grid gap-2 w-100">
                                <button class="btn btn-warning btn-sm flex-fill"
                                    data-id="<?= $row['id']; ?>"
                                    data-nama="<?= htmlspecialchars($row['nama_barang']); ?>"
                                    data-kategori="<?= $row['kategori_id'] ?? ''; ?>"
                                    data-supplier="<?= $row['supplier_id'] ?? ''; ?>"
                                    data-satuan="<?= htmlspecialchars($row['satuan']); ?>"
                                    onclick="openEditBarangModal(this)">
                                    Edit
                                </button>
                                    <button class="btn btn-danger btn-sm flex-fill"
                                        onclick="confirmDelete(<?= $row['id']; ?>, '<?= htmlspecialchars($row['nama_barang']); ?>')">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tabel Transaksi Barang -->
<div class="container mt-4 mb-3">
    <div class="card shadow">
        <div class="card-body">
            <h2>Daftar Transaksi Barang</h2>

            <table class="table table-bordered mt-3">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th> 
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Jenis</th>
                        <th>Tanggal</th>
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
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tabel Retur -->
<div class="container mt-4 mb-3">
    <div class="card shadow">
        <div class="card-body">
            <h2>Daftar Retur Barang</h2>

            <table class="table table-bordered mt-3">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Barang</th>
                        <th>Supplier</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Alasan</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $retur_result = mysqli_query($conn, "SELECT 
                                                                        ROW_NUMBER() OVER (ORDER BY retur_barang.tanggal ASC, retur_barang.id ASC) AS row_num, 
                                                                        retur_barang.id, 
                                                                        barang.nama_barang, 
                                                                        supplier.nama AS nama_supplier, 
                                                                        retur_barang.jumlah, 
                                                                        barang.satuan, 
                                                                        retur_barang.alasan, 
                                                                        retur_barang.tanggal 
                                                                    FROM retur_barang 
                                                                    JOIN barang ON retur_barang.barang_id = barang.id
                                                                    JOIN supplier ON retur_barang.supplier_id = supplier.id
                                                                    ORDER BY retur_barang.tanggal ASC, retur_barang.id ASC");

                    while ($row = mysqli_fetch_assoc($retur_result)) { ?>
                        <tr class="text-center">
                            <td><?= $row['row_num']; ?></td>
                            <td><?= $row['nama_barang']; ?></td>
                            <td><?= $row['nama_supplier']; ?></td>
                            <td><?= $row['jumlah']; ?></td>
                            <td><?= $row['satuan']; ?></td>
                            <td><?= $row['alasan']; ?></td>
                            <td><?= $row['tanggal']; ?></td>
                        </tr>
                    <?php } ?>
                    
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Barang -->
<div class="modal fade" id="editBarangModal" tabindex="-1" aria-labelledby="editBarangModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBarangModalLabel">Edit Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="crud/edit_barang.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="barang_id" id="editBarangId">
                    <p><strong>Barang:</strong> <span id="editNamaBarang"></span></p>

                    <!-- Pilih Kategori -->
                    <div class="mb-3">
                        <label class="form-label">Pilih Kategori</label>
                        <select name="kategori_id" id="editKategori" class="form-control">
                            <option value="">Pilih Kategori</option>
                            <?php
                            $kategori_result = mysqli_query($conn, "SELECT * FROM kategori");
                            while ($kategori = mysqli_fetch_assoc($kategori_result)) {
                                echo "<option value='{$kategori['id']}'>{$kategori['nama_kategori']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Pilih Supplier -->
                    <div class="mb-3">
                        <label class="form-label">Pilih Supplier</label>
                        <select name="supplier_id" id="editSupplier" class="form-control">
                            <option value="">Pilih Supplier</option>
                            <?php
                            $supplier_result = mysqli_query($conn, "SELECT * FROM supplier");
                            while ($supplier = mysqli_fetch_assoc($supplier_result)) {
                                echo "<option value='{$supplier['id']}'>{$supplier['nama']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Form Edit Satuan -->
                    <div class="mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" id="editSatuan" class="form-control" placeholder="Masukkan satuan (ex: kg, liter, pcs)">
                        <!-- Tempat pesan error -->
                        <small id="errorSatuan" class="text-danger" style="display: none;">Satuan tidak boleh kosong!</small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const formEdit = document.querySelector("#editBarangModal form");
        const editSatuan = document.getElementById("editSatuan");
        const errorSatuan = document.getElementById("errorSatuan");

        formEdit.addEventListener("submit", function (e) {
            let valid = true;

            // Validasi input satuan
            if (editSatuan.value.trim() === "") {
                errorSatuan.style.display = "block";
                valid = false;
            } else {
                errorSatuan.style.display = "none";
            }

            // Cegah submit jika ada error
            if (!valid) {
                e.preventDefault();
            }
        });
    });
</script>

</body>
</html>
