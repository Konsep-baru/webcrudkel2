<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Perpustakaan Digital - Selamat Datang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="main-header">
            <div class="header-content">
                <h1>📚 PERPUSTAKAAN DIGITAL</h1>
                <p>Sistem Manajemen Perpustakaan Modern</p>
            </div>
            <nav class="main-nav">
                <a href="index.php" class="active">Beranda</a>
                <a href="katalog.php">Katalog Buku</a>
                <a href="login.php">Admin Login</a>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h2>Selamat Datang di Sistem Perpustakaan Digital</h2>
                <p>Kelola koleksi buku, anggota, dan transaksi peminjaman dengan mudah dan efisien</p>
                <div class="hero-buttons">

                </div>
            </div>
            <div class="hero-image">
                <div class="library-icon">📖</div>
            </div>
        </section>

        <!-- Fitur Section -->
        <section class="features-section">
            <h2>Fitur Utama Sistem</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📖</div>
                    <h3>Manajemen Koleksi Buku</h3>
                    <p>Kelola data buku lengkap dengan ISBN, penulis, penerbit, stok, dan lokasi penyimpanan</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Data Anggota</h3>
                    <p>Kelola data anggota dengan informasi lengkap seperti NIS, kelas, jurusan, dan kontak</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔄</div>
                    <h3>Transaksi Digital</h3>
                    <p>Proses peminjaman dan pengembalian buku dengan sistem otomatis dan perhitungan denda</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Laporan & Statistik</h3>
                    <p>Monitor aktivitas perpustakaan dengan dashboard statistik dan laporan terintegrasi</p>
                </div>
            </div>
        </section>

        <!-- Statistik Section -->
        <section class="stats-section">
            <h2>Statistik Perpustakaan</h2>
            <div class="stats-grid">
                <?php
                // Ambil data statistik
                $total_buku = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku");
                $total_buku = mysqli_fetch_assoc($total_buku)['total'];

                $total_anggota = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM anggota");
                $total_anggota = mysqli_fetch_assoc($total_anggota)['total'];

                $buku_tersedia = mysqli_query($koneksi, "SELECT SUM(stok) as total FROM buku");
                $buku_tersedia = mysqli_fetch_assoc($buku_tersedia)['total'];

                $sedang_dipinjam = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status='dipinjam'");
                $sedang_dipinjam = mysqli_fetch_assoc($sedang_dipinjam)['total'];
                ?>
                
                <div class="stat-item">
                    <div class="stat-number"><?= $total_buku ?></div>
                    <div class="stat-label">Judul Buku</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number"><?= $total_anggota ?></div>
                    <div class="stat-label">Anggota</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number"><?= $buku_tersedia ?></div>
                    <div class="stat-label">Buku Tersedia</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number"><?= $sedang_dipinjam ?></div>
                    <div class="stat-label">Sedang Dipinjam</div>
                </div>
            </div>
        </section>

        <!-- Info Section -->
        <section class="info-section">
            <div class="info-content">
                <h2>Tentang Sistem Ini</h2>
                <p>Sistem Perpustakaan Digital ini dikembangkan menggunakan teknologi web modern dengan PHP, MySQL, dan CSS3. Sistem ini dirancang untuk memudahkan pengelolaan perpustakaan sekolah dengan fitur-fitur yang lengkap dan antarmuka yang user-friendly.</p>
                
            </div>
        </section>

        <!-- Footer -->
        <footer class="main-footer">
            <div class="footer-content">
                <p>&copy; 2025 Sistem Perpustakaan Digital. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>