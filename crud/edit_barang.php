<?php
session_start();
include "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $barang_id = $_POST["barang_id"];
    $kategori_id = $_POST["kategori_id"];
    $supplier_id = $_POST["supplier_id"];
    $satuan = htmlspecialchars($_POST["satuan"]);

    // Validasi server-side untuk input satuan
    if (empty($satuan)) {
        $_SESSION['error_satuan'] = "Satuan tidak boleh kosong!";
        header("Location: ../index.php");
        exit();
    } else {
        unset($_SESSION['error_satuan']);
    }

    // Proses update data
    $query = "UPDATE barang SET kategori_id = '$kategori_id', supplier_id = '$supplier_id', satuan = '$satuan' WHERE id = '$barang_id'";
    if (mysqli_query($conn, $query)) {
        header("Location: ../index.php");
    } else {
        echo "Gagal mengupdate data: " . mysqli_error($conn);
    }
}
?>
