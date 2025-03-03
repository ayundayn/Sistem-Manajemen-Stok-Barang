<?php
include dirname(__DIR__) . "/config/config.php";
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Google Font -->
<head>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif !important;
        }
        h2 {
            font-weight: 900;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= $base_url ?>/index.php">Toko Jaya Mandiri</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : ''; ?>"
                        href="<?= $base_url ?>/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= ($current_page == 'tambah_barang.php' || $current_page == 'tambah_kategori.php' || $current_page == 'tambah_supplier.php') ? 'active' : ''; ?>"
                        href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Manajemen Barang
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item <?= ($current_page == 'tambah_barang.php') ? 'active' : ''; ?>"
                                href="<?= $base_url ?>/crud/tambah_barang.php">Tambah Barang</a></li>
                            <li><a class="dropdown-item <?= ($current_page == 'tambah_kategori.php') ? 'active' : ''; ?>"
                                href="<?= $base_url ?>/crud/tambah_kategori.php">Tambah Kategori</a></li>
                            <li><a class="dropdown-item <?= ($current_page == 'tambah_supplier.php') ? 'active' : ''; ?>"
                                href="<?= $base_url ?>/crud/tambah_supplier.php">Tambah Supplier</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'transaksi.php') ? 'active' : ''; ?>"
                        href="<?= $base_url ?>/crud/transaksi.php">Transaksi Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'retur.php') ? 'active' : ''; ?>"
                        href="<?= $base_url ?>/crud/retur.php">Retur Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'laporan.php') ? 'active' : ''; ?>"
                        href="<?= $base_url ?>/laporan.php">Laporan</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Mengaktifkan semua dropdown
            var dropdownElements = document.querySelectorAll('.dropdown-toggle');
            dropdownElements.forEach(function (dropdown) {
                new bootstrap.Dropdown(dropdown);
            });

            // Mengatasi masalah dropdown yang tidak bisa diklik setelah pindah halaman
            document.querySelectorAll(".dropdown-menu .dropdown-item").forEach(function (item) {
                item.addEventListener("click", function () {
                    var navbarCollapse = document.querySelector(".navbar-collapse");
                    if (window.innerWidth < 992 && navbarCollapse.classList.contains("show")) {
                        var navbarToggler = document.querySelector(".navbar-toggler");
                        navbarToggler.click();
                    }
                });
            });

            // Mengatasi navbar tidak bisa toggle di layar kecil
            var navbarToggler = document.querySelector(".navbar-toggler");
            navbarToggler.addEventListener("click", function () {
                var navbarCollapse = document.querySelector(".navbar-collapse");
                navbarCollapse.classList.toggle("show");
            });
        });
    </script>

    <!-- Bootstrap JS (versi terbaru) -->
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Bundle dengan Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>