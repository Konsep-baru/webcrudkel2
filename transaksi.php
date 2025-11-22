<?php
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// PROSES PEMINJAMAN
if (isset($_POST['proses_peminjaman'])) {
    $id_anggota = mysqli_real_escape_string($koneksi, $_POST['id_anggota']);
    $id_buku = mysqli_real_escape_string($koneksi, $_POST['id_buku']);
    $tanggal_pinjam = date('Y-m-d');
    $tanggal_kembali = date('Y-m-d', strtotime('+7 days')); // 7 hari dari pinjam
    
    // Cek stok buku
    $cek_stok = mysqli_query($koneksi, "SELECT stok FROM buku WHERE id_buku='$id_buku'");
    $stok_data = mysqli_fetch_assoc($cek_stok);
    
    if ($stok_data['stok'] > 0) {
        // Insert data peminjaman
        $sql = "INSERT INTO peminjaman (id_anggota, id_buku, tanggal_pinjam, tanggal_kembali, status) 
                VALUES ('$id_anggota', '$id_buku', '$tanggal_pinjam', '$tanggal_kembali', 'dipinjam')";
        
        if (mysqli_query($koneksi, $sql)) {
            // Kurangi stok buku
            mysqli_query($koneksi, "UPDATE buku SET stok = stok - 1 WHERE id_buku='$id_buku'");
            $success = "Buku berhasil dipinjamkan!";
        } else {
            $error = "Error: " . mysqli_error($koneksi);
        }
    } else {
        $error = "Stok buku tidak tersedia!";
    }
}

// PROSES PENGEMBALIAN
if (isset($_POST['proses_pengembalian'])) {
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
    $tanggal_pengembalian = date('Y-m-d');
    
    // Hitung denda (jika telat)
    $pinjam_data = mysqli_query($koneksi, 
        "SELECT p.*, b.id_buku FROM peminjaman p 
         JOIN buku b ON p.id_buku = b.id_buku 
         WHERE p.id_peminjaman='$id_peminjaman'");
    $pinjam = mysqli_fetch_assoc($pinjam_data);
    
    $denda = 0;
    $tenggat = strtotime($pinjam['tanggal_kembali']);
    $kembali = strtotime($tanggal_pengembalian);
    
    if ($kembali > $tenggat) {
        $telat_hari = ($kembali - $tenggat) / (60 * 60 * 24);
        $denda = $telat_hari * 1000; // Denda Rp 1000/hari
    }
    
    // Update status peminjaman
    mysqli_query($koneksi, "UPDATE peminjaman SET status='kembali' WHERE id_peminjaman='$id_peminjaman'");
    
    // Tambah stok buku
    mysqli_query($koneksi, "UPDATE buku SET stok = stok + 1 WHERE id_buku='{$pinjam['id_buku']}'");
    
    // Insert data pengembalian
    $sql = "INSERT INTO pengembalian (id_peminjaman, tanggal_pengembalian, denda) 
            VALUES ('$id_peminjaman', '$tanggal_pengembalian', '$denda')";
    
    if (mysqli_query($koneksi, $sql)) {
        $success = "Buku berhasil dikembalikan!" . ($denda > 0 ? " Denda: Rp " . number_format($denda) : "");
    } else {
        $error = "Error: " . mysqli_error($koneksi);
    }
}

// Ambil data untuk dropdown
$anggota_list = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY nama");
$buku_list = mysqli_query($koneksi, "SELECT * FROM buku WHERE stok > 0 ORDER BY judul");

// Data peminjaman aktif
$peminjaman_aktif = mysqli_query($koneksi, 
    "SELECT p.*, a.nama as nama_anggota, a.kelas, b.judul as judul_buku 
     FROM peminjaman p 
     JOIN anggota a ON p.id_anggota = a.id_anggota 
     JOIN buku b ON p.id_buku = b.id_buku 
     WHERE p.status='dipinjam' 
     ORDER BY p.tanggal_pinjam DESC");

// Riwayat peminjaman
$riwayat_peminjaman = mysqli_query($koneksi, 
    "SELECT p.*, a.nama as nama_anggota, b.judul as judul_buku, pg.denda, pg.tanggal_pengembalian
     FROM peminjaman p 
     JOIN anggota a ON p.id_anggota = a.id_anggota 
     JOIN buku b ON p.id_buku = b.id_buku 
     LEFT JOIN pengembalian pg ON p.id_peminjaman = pg.id_peminjaman
     ORDER BY p.tanggal_pinjam DESC 
     LIMIT 20");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Peminjaman - Sistem Perpustakaan</title>
    <style>
        /* Reset dan Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        h1 {
            color: #1e293b;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        /* Alert Messages */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert.success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert.error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        /* Form Container */
        .form-container {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .form-container h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-row {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #475569;
            font-size: 14px;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn-primary, .btn-secondary {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
            margin-right: 10px;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-secondary {
            background: #94a3b8;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #64748b;
        }
        
        /* Table Container */
        .table-container {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 24px;
            overflow-x: auto;
        }
        
        .table-container h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #475569;
            font-size: 14px;
        }
        
        tr:hover {
            background-color: #f8fafc;
        }
        
        .status-dipinjam {
            background: #fef3c7;
            color: #92400e;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-kembali {
            background: #d1fae5;
            color: #065f46;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .denda {
            color: #dc2626;
            font-weight: 500;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔄 Sistem Transaksi Perpustakaan</h1>
            <p>Kelola peminjaman dan pengembalian buku dengan mudah</p>
        </header>

        <main>
            <?php if (isset($success)): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>

            <!-- Form Peminjaman -->
            <div class="form-container">
                <h2>📥 Peminjaman Buku</h2>
                <form method="post">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Pilih Anggota:</label>
                            <select name="id_anggota" required>
                                <option value="">-- Pilih Anggota --</option>
                                <?php while($anggota = mysqli_fetch_array($anggota_list)): ?>
                                    <option value="<?= $anggota['id_anggota'] ?>">
                                        <?= $anggota['nis'] ?> - <?= $anggota['nama'] ?> (<?= $anggota['kelas'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Pilih Buku:</label>
                            <select name="id_buku" required>
                                <option value="">-- Pilih Buku --</option>
                                <?php 
                                mysqli_data_seek($buku_list, 0); // Reset pointer
                                while($buku = mysqli_fetch_array($buku_list)): ?>
                                    <option value="<?= $buku['id_buku'] ?>">
                                        <?= $buku['judul'] ?> (Stok: <?= $buku['stok'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" name="proses_peminjaman" class="btn-primary">
                        ✅ Proses Peminjaman
                    </button>
                </form>
            </div>

            <!-- Form Pengembalian -->
            <div class="form-container">
                <h2>📤 Pengembalian Buku</h2>
                <form method="post">
                    <div class="form-group">
                        <label>Pilih Peminjaman Aktif:</label>
                        <select name="id_peminjaman" required>
                            <option value="">-- Pilih Peminjaman --</option>
                            <?php while($pinjam = mysqli_fetch_array($peminjaman_aktif)): ?>
                                <option value="<?= $pinjam['id_peminjaman'] ?>">
                                    <?= $pinjam['nama_anggota'] ?> - <?= $pinjam['judul_buku'] ?> 
                                    (Pinjam: <?= date('d/m/Y', strtotime($pinjam['tanggal_pinjam'])) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="proses_pengembalian" class="btn-primary">
                        🔄 Proses Pengembalian
                    </button>
                </form>
            </div>

            <!-- Tabel Buku Dipinjam -->
            <div class="table-container">
                <h2>📋 Buku Sedang Dipinjam</h2>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($peminjaman_aktif, 0); // Reset pointer
                        $no = 1; 
                        while($pinjam = mysqli_fetch_array($peminjaman_aktif)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $pinjam['nama_anggota'] ?> (<?= $pinjam['kelas'] ?>)</td>
                            <td><?= $pinjam['judul_buku'] ?></td>
                            <td><?= date('d/m/Y', strtotime($pinjam['tanggal_pinjam'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($pinjam['tanggal_kembali'])) ?></td>
                            <td><span class="status-dipinjam">Dipinjam</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tabel Riwayat -->
            <div class="table-container">
                <h2>📊 Riwayat Transaksi Terakhir</h2>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Denda</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while($riwayat = mysqli_fetch_array($riwayat_peminjaman)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $riwayat['nama_anggota'] ?></td>
                            <td><?= $riwayat['judul_buku'] ?></td>
                            <td><?= date('d/m/Y', strtotime($riwayat['tanggal_pinjam'])) ?></td>
                            <td><?= $riwayat['tanggal_pengembalian'] ? date('d/m/Y', strtotime($riwayat['tanggal_pengembalian'])) : '-' ?></td>
                            <td class="<?= $riwayat['denda'] ? 'denda' : '' ?>">
                                <?= $riwayat['denda'] ? 'Rp ' . number_format($riwayat['denda']) : '-' ?>
                            </td>
                            <td>
                                <?php if($riwayat['status'] == 'dipinjam'): ?>
                                    <span class="status-dipinjam">Dipinjam</span>
                                <?php else: ?>
                                    <span class="status-kembali">Kembali</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Konfirmasi sebelum memproses
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Proses transaksi ini?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>