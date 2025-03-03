<?php
include "config/database.php"; // Pastikan koneksi database tersedia

// Pastikan ada parameter ID di URL
if (isset($_GET['id'])) {
    $barang_id = intval($_GET['id']); // untuk menghindari SQL Injection

    // Cek apakah barang dengan ID tersebut ada
    $cek_barang = mysqli_query($conn, "SELECT * FROM barang WHERE id = $barang_id");
    
    if (mysqli_num_rows($cek_barang) > 0) {
        // Jika barang ditemukan, hapus dari database
        $hapus_query = "DELETE FROM barang WHERE id = $barang_id";
        if (mysqli_query($conn, $hapus_query)) {
            echo "<script>
                alert('Barang berhasil dihapus!');
                window.location.href = 'index.php';
            </script>";
        } else {
            echo "<script>
                alert('Terjadi kesalahan saat menghapus barang.');
                window.location.href = 'index.php';
            </script>";
        }
    } else {
        // Jika barang tidak ditemukan, kembalikan ke dashboard
        echo "<script>
            alert('Barang tidak ditemukan!');
            window.location.href = 'index.php';
        </script>";
    }
} else {
    echo "<script>
        alert('ID barang tidak valid!');
        window.location.href = 'index.php';
    </script>";
}
?>
