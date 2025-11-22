<?php
include 'config.php';

// CEK LOGIN - Pastikan user sudah login
cek_login();

// VERIFIKASI ADMIN - Tambahan validasi
$admin_data = get_admin_data();
if (!$admin_data) {
    // Jika data admin tidak ditemukan, logout paksa
    session_destroy();
    redirect('login.php', 'Sesi tidak valid. Silakan login kembali.');
}

// VERIFIKASI SESSION - Cek konsistensi session
if ($_SESSION['admin'] !== $admin_data['username']) {
    session_destroy();
    redirect('login.php', 'Data session tidak konsisten. Silakan login kembali.');
}

// CEK SESSION TIMEOUT (opsional - 1 jam)
$session_timeout = 3600; // 1 jam dalam detik
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $session_timeout)) {
    session_destroy();
    redirect('login.php', 'Sesi telah berakhir. Silakan login kembali.');
}

// Hitung statistik
$total_buku = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku"))['total'];
$total_anggota = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM anggota"))['total'];
$total_pinjam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status='dipinjam'"))['total'];
$buku_tersedia = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(stok) as total FROM buku"))['total'];

// Data buku yang sedang dipinjam
$buku_dipinjam = mysqli_query($koneksi, 
    "SELECT p.*, a.nama as nama_anggota, a.kelas, b.judul as judul_buku 
     FROM peminjaman p 
     JOIN anggota a ON p.id_anggota = a.id_anggota 
     JOIN buku b ON p.id_buku = b.id_buku 
     WHERE p.status='dipinjam' 
     ORDER BY p.tanggal_pinjam DESC 
     LIMIT 5");

// Buku dengan stok menipis (stok <= 2)
$stok_menipis = mysqli_query($koneksi, 
    "SELECT * FROM buku WHERE stok <= 2 ORDER BY stok ASC LIMIT 5");

// Aktivitas terbaru
$aktivitas_terbaru = mysqli_query($koneksi,
    "SELECT p.*, a.nama as nama_anggota, b.judul as judul_buku,
            CASE 
                WHEN p.status = 'dipinjam' THEN 'meminjam'
                ELSE 'mengembalikan'
            END as aksi
     FROM peminjaman p
     JOIN anggota a ON p.id_anggota = a.id_anggota
     JOIN buku b ON p.id_buku = b.id_buku
     ORDER BY p.tanggal_pinjam DESC
     LIMIT 8");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan Digital</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
        }
        
        .user-info {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .welcome-section h1 {
            margin: 0;
            font-size: 2rem;
        }
        
        .welcome-section p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid #3498db;
            position: relative;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.buku { border-left-color: #e74c3c; }
        .stat-card.anggota { border-left-color: #2ecc71; }
        .stat-card.pinjam { border-left-color: #f39c12; }
        .stat-card.tersedia { border-left-color: #9b59b6; }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 0.5rem 0;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-weight: bold;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .dashboard-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .dashboard-card h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e8f4fd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 0.8rem;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }
        
        .activity-time {
            font-size: 0.8rem;
            color: #7f8c8d;
        }
        
        .quick-actions {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .quick-actions h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        

        
        .action-btn {
            background: white;
            border: 2px solid #3498db;
            color: #3498db;
            padding: 1rem;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: bold;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        
        .action-btn:hover {
            background: #3498db;
            color: white;
            transform: translateY(-3px);
        }
        
        .action-icon {
            font-size: 1.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #7f8c8d;
        }
        
        .empty-state .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .session-info {
            background: #e8f4fd;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 1rem;
            font-size: 0.8rem;
            color: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .user-info {
                position: static;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📊 DASHBOARD ADMIN</h1>
            <nav>
               <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin ingin logout?')">🚪 Logout (<?= $admin_data['nama_admin'] ?>)</a>
            </nav>
        </header>

        <main>
            <?php tampilkan_pesan(); ?>
            
            <!-- Session Info -->
            <div class="session-info">
                🔐 Login sebagai: <strong><?= $admin_data['nama_admin'] ?></strong> | 
                ⏰ Login: <strong><?= date('H:i', $_SESSION['login_time'] ?? time()) ?></strong>
            </div>

            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1>Selamat Datang, <?= $admin_data['nama_admin'] ?>! 👋</h1>
                <p>Kelola perpustakaan digital dengan mudah dan efisien</p>
                <div class="user-info">
                    🕒 <?= date('H:i') ?> | 📅 <?= date('d/m/Y') ?>
                </div>
            </div>

            <!-- Statistik -->
            <div class="stats-grid">
                <div class="stat-card buku">
                    <div class="stat-icon">📚</div>
                    <div class="stat-number"><?= $total_buku ?></div>
                    <div class="stat-label">Total Buku</div>
                </div>
                
                <div class="stat-card anggota">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?= $total_anggota ?></div>
                    <div class="stat-label">Total Anggota</div>
                </div>
                
                <div class="stat-card pinjam">
                    <div class="stat-icon">🔄</div>
                    <div class="stat-number"><?= $total_pinjam ?></div>
                    <div class="stat-label">Sedang Dipinjam</div>
                </div>
                
                <div class="stat-card tersedia">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number"><?= $buku_tersedia ?></div>
                    <div class="stat-label">Buku Tersedia</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>🚀 Quick Actions</h2>
                <div class="action-buttons">
                    <a href="buku.php?action=tambah" class="action-btn">
                        <span class="action-icon">➕</span>
                        <span>Tambah Buku</span>
                    </a>
                    
                    <a href="anggota.php?action=tambah" class="action-btn">
                        <span class="action-icon">👥</span>
                        <span>Tambah Anggota</span>
                    </a>
                    
                    <a href="transaksi.php" class="action-btn">
                        <span class="action-icon">📥</span>
                        <span>Transaksi</span>
                    </a>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Buku Sedang Dipinjam -->
                <div class="dashboard-card">
                    <h3>📖 Buku Sedang Dipinjam</h3>
                    <?php if (mysqli_num_rows($buku_dipinjam) > 0): ?>
                        <div class="activity-list">
                            <?php while($pinjam = mysqli_fetch_array($buku_dipinjam)): ?>
                            <div class="activity-item">
                                <div class="activity-icon">📚</div>
                                <div class="activity-content">
                                    <div class="activity-title"><?= $pinjam['judul_buku'] ?></div>
                                    <div class="activity-time">
                                        <?= $pinjam['nama_anggota'] ?> (<?= $pinjam['kelas'] ?>) • 
                                        Kembali: <?= date('d/m/Y', strtotime($pinjam['tanggal_kembali'])) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="icon">📚</div>
                            <p>Tidak ada buku yang sedang dipinjam</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Stok Menipis -->
                <div class="dashboard-card">
                    <h3>>📚 Stok Buku </h3>
                    <?php if (mysqli_num_rows($stok_menipis) > 0): ?>
                        <div class="activity-list">
                            <?php while($buku = mysqli_fetch_array($stok_menipis)): ?>
                            <div class="activity-item">
                                <div class="activity-icon" style="background: #fde8e8; color: #e74c3c;">❗</div>
                                <div class="activity-content">
                                    <div class="activity-title"><?= $buku['judul'] ?></div>
                                    <div class="activity-time">
                                        Stok: <?= $buku['stok'] ?> buku • <?= $buku['lokasi'] ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="icon">✅</div>
                            <p>Semua stok buku mencukupi</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Aktivitas Terbaru -->
                <div class="dashboard-card">
                    <h3>📋 Aktivitas Terbaru</h3>
                    <?php if (mysqli_num_rows($aktivitas_terbaru) > 0): ?>
                        <div class="activity-list">
                            <?php while($aktivitas = mysqli_fetch_array($aktivitas_terbaru)): ?>
                            <div class="activity-item">
                                <div class="activity-icon" style="background: #e8f6f3; color: #27ae60;">
                                    <?= $aktivitas['status'] == 'dipinjam' ? '📥' : '📤' ?>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?= $aktivitas['nama_anggota'] ?> <?= $aktivitas['aksi'] ?> "<?= $aktivitas['judul_buku'] ?>"
                                    </div>
                                    <div class="activity-time">
                                        <?= date('d/m/Y H:i', strtotime($aktivitas['tanggal_pinjam'])) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="icon">📊</div>
                            <p>Belum ada aktivitas</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto refresh statistik setiap 60 detik
        setInterval(() => {
            // Hanya refresh jika user aktif
            if (!document.hidden) {
                fetch(window.location.href)
                    .then(response => response.text())
                    .then(html => {
                        // Bisa ditambahkan update specific elements saja
                        console.log('Dashboard updated:', new Date().toLocaleTimeString());
                    })
                    .catch(err => console.log('Refresh error:', err));
            }
        }, 60000);
        
        // Session timeout warning (15 menit sebelum timeout)
        setTimeout(() => {
            const warning = confirm('Sesi Anda akan segera berakhir. Apakah ingin memperpanjang sesi?');
            if (warning) {
                // Refresh page untuk memperpanjang sesi
                window.location.reload();
            }
        }, 45 * 60 * 1000); // 45 menit
        
        // Confirm sebelum logout
        document.querySelector('.logout-btn').addEventListener('click', function(e) {
            if (!confirm('Yakin ingin logout?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>