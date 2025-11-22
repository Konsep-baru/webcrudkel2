<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku - Perpustakaan Digital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="header-content">
                <h1>📚 KATALOG BUKU</h1>
                <p>Daftar Koleksi Buku Perpustakaan</p>
            </div>
            <nav class="main-nav">
                <a href="index.php">Beranda</a>
                <a href="buku_public.php" class="active">Katalog Buku</a>
                <a href="login.php">Admin Login</a>
            </nav>
        </header>

        <main>
            <!-- Pencarian Buku -->
            <div class="search-section">
                <form method="get" class="search-form">
                    <input type="text" name="cari" placeholder="Cari judul buku, penulis, atau penerbit..." 
                           value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
                    <button type="submit" class="btn-primary">🔍 Cari</button>
                </form>
            </div>

            <!-- Daftar Buku -->
            <div class="books-grid">
                <?php
                // Query buku dengan pencarian
                $where = "";
                if (isset($_GET['cari']) && !empty($_GET['cari'])) {
                    $cari = mysqli_real_escape_string($koneksi, $_GET['cari']);
                    $where = "WHERE judul LIKE '%$cari%' OR penulis LIKE '%$cari%' OR penerbit LIKE '%$cari%'";
                }

                $buku = mysqli_query($koneksi, 
                    "SELECT * FROM buku $where ORDER BY judul ASC");

                if (mysqli_num_rows($buku) > 0):
                    while($row = mysqli_fetch_array($buku)):
                ?>
                <div class="book-card">
                    <div class="book-cover">
                        <div class="book-icon">📖</div>
                    </div>
                    <div class="book-info">
                        <h3 class="book-title"><?= $row['judul'] ?></h3>
                        <p class="book-author">✍️ <?= $row['penulis'] ?></p>
                        <p class="book-publisher">🏢 <?= $row['penerbit'] ?></p>
                        <p class="book-year">📅 Tahun: <?= $row['tahun'] ?></p>
                        <p class="book-stock">
                            <?php if($row['stok'] > 0): ?>
                                <span class="available">✅ Tersedia: <?= $row['stok'] ?> buku</span>
                            <?php else: ?>
                                <span class="unavailable">❌ Sedang Habis</span>
                            <?php endif; ?>
                        </p>
                        <?php if($row['isbn']): ?>
                            <p class="book-isbn">🔢 ISBN: <?= $row['isbn'] ?></p>
                        <?php endif; ?>
                        <?php if($row['lokasi']): ?>
                            <p class="book-location">📍 <?= $row['lokasi'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="no-books">
                    <h3>📚 Tidak ada buku ditemukan</h3>
                    <p>Silakan coba dengan kata kunci lain atau</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info untuk Anggota -->
            <div class="member-info">
                <div class="info-card">
                    <h3>📋 Informasi untuk Anggota</h3>
                    <p>Untuk melakukan peminjaman buku, silakan hubungi administrator perpustakaan atau login ke sistem.</p>
                    <div class="action-buttons">
                        <a href="index.php" class="btn-secondary">Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>