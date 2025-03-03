<?php
include "config/database.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data transaksi untuk mengembalikan stok barang
    $query = "SELECT * FROM transaksi WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $transaksi = mysqli_fetch_assoc($result);

    if ($transaksi) {
        $barang_id = $transaksi['barang_id'];
        $jumlah = $transaksi['jumlah'];
        $jenis = $transaksi['jenis'];

        // Update stok barang sebelum menghapus transaksi
        if ($jenis == 'masuk') {
            $update_stok = "UPDATE barang SET stok = stok - $jumlah WHERE id = '$barang_id'";
        } else {
            $update_stok = "UPDATE barang SET stok = stok + $jumlah WHERE id = '$barang_id'";
        }
        mysqli_query($conn, $update_stok);

        // Hapus transaksi dari database
        $delete_query = "DELETE FROM transaksi WHERE id = '$id'";
        mysqli_query($conn, $delete_query);
    }
}

header("Location: laporan.php");
exit();
?>
