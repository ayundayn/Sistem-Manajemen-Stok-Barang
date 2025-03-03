<?php
include "../config/database.php";

if (isset($_GET['barang_id'])) {
    $barang_id = $_GET['barang_id'];

    $query = "
        SELECT s.id, s.nama 
        FROM supplier s
        INNER JOIN barang b ON b.supplier_id = s.id
        WHERE b.id = '$barang_id'
    ";
    $result = mysqli_query($conn, $query);

    $suppliers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $suppliers[] = $row;
    }

    echo json_encode($suppliers);
}
