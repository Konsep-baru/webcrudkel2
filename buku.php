<?php
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// CREATE - Tambah Buku
if (isset($_POST['tambah'])) {
    $isbn = mysqli_real_escape_string($koneksi, $_POST['isbn']);
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $penerbit = mysqli_real_escape_string($koneksi, $_POST['penerbit']);
    $tahun = mysqli_real_escape_string($koneksi, $_POST['tahun']);
    $stok = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $tanggal_tiba = mysqli_real_escape_string($koneksi, $_POST['tanggal_tiba']);

    $sql = "INSERT INTO buku (isbn, judul, penulis, penerbit, tahun, stok, lokasi, tanggal_tiba) 
            VALUES ('$isbn', '$judul', '$penulis', '$penerbit', '$tahun', '$stok', '$lokasi', '$tanggal_tiba')";
    
    if (mysqli_query($koneksi, $sql)) {
        $success = "Buku berhasil ditambahkan!";
    } else {
        $error = "Error: " . mysqli_error($koneksi);
    }
}

// UPDATE - Edit Buku
if (isset($_POST['edit'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id_buku']);
    $isbn = mysqli_real_escape_string($koneksi, $_POST['isbn']);
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $penerbit = mysqli_real_escape_string($koneksi, $_POST['penerbit']);
    $tahun = mysqli_real_escape_string($koneksi, $_POST['tahun']);
    $stok = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $tanggal_tiba = mysqli_real_escape_string($koneksi, $_POST['tanggal_tiba']);

    $sql = "UPDATE buku SET isbn='$isbn', judul='$judul', penulis='$penulis', 
            penerbit='$penerbit', tahun='$tahun', stok='$stok', lokasi='$lokasi',
            tanggal_tiba='$tanggal_tiba' 
            WHERE id_buku='$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        $success = "Buku berhasil diupdate!";
    } else {
        $error = "Error: " . mysqli_error($koneksi);
    }
}

// DELETE - Hapus Buku
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $sql = "DELETE FROM buku WHERE id_buku='$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        $success = "Buku berhasil dihapus!";
    } else {
        $error = "Error: " . mysqli_error($koneksi);
    }
}

// SEARCH - Pencarian Buku
$search_query = "";
$search_param = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_param = mysqli_real_escape_string($koneksi, $_GET['search']);
    $search_query = " WHERE judul LIKE '%$search_param%' 
                      OR penulis LIKE '%$search_param%' 
                      OR penerbit LIKE '%$search_param%' 
                      OR isbn LIKE '%$search_param%'";
}

// READ - Ambil data buku
$buku = mysqli_query($koneksi, "SELECT * FROM buku $search_query ORDER BY id_buku DESC");

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $edit_data = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku='$id'");
    $edit_data = mysqli_fetch_assoc($edit_data);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - Sistem Perpustakaan</title>
    <style>
        /* Reset dan Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
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
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        /* Alert Messages */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            border: 1px solid;
        }
        
        .alert.success {
            background-color: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }
        
        .alert.error {
            background-color: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }
        
        /* Search Container */
        .search-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        
        .search-container h2 {
            font-size: 16px;
            margin-bottom: 12px;
            color: #475569;
            font-weight: 600;
        }
        
        .search-form {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .search-input {
            flex: 1;
            padding: 10px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .search-btn, .reset-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .search-btn {
            background: #3b82f6;
            color: white;
        }
        
        .search-btn:hover {
            background: #2563eb;
        }
        
        .reset-btn {
            background: #6b7280;
            color: white;
        }
        
        .reset-btn:hover {
            background: #4b5563;
        }
        
        .search-info {
            margin-top: 12px;
            color: #64748b;
            font-size: 14px;
            font-style: italic;
        }
        
        /* Form Container */
        .form-container {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
        }
        
        .form-container h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1e293b;
            font-weight: 600;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 6px;
            font-weight: 500;
            color: #475569;
            font-size: 14px;
        }
        
        .form-group input {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn-primary, .btn-secondary {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        /* Table Container */
        .table-container {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        
        .table-container h2 {
            font-size: 18px;
            padding: 20px 24px;
            background: #f8fafc;
            color: #1e293b;
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .table-info {
            font-size: 14px;
            color: #64748b;
            font-weight: normal;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        
        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #475569;
        }
        
        tr:hover {
            background-color: #f8fafc;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid;
        }
        
        .btn-edit {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }
        
        .btn-edit:hover {
            background: #dbeafe;
        }
        
        .btn-delete {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }
        
        .btn-delete:hover {
            background: #fee2e2;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #64748b;
            font-style: italic;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .container {
                padding: 16px;
            }
            
            table {
                font-size: 13px;
            }
            
            th, td {
                padding: 8px 12px;
            }
            
            .actions {
                flex-direction: column;
                gap: 4px;
            }
            
            .table-container h2 {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📚 Kelola Data Buku</h1>
            <p style="color: #64748b; font-size: 14px;">Sistem Manajemen Perpustakaan</p>
        </header>

        <main>
            <?php if (isset($success)): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>

            <!-- FORM PENCARIAN -->
            <div class="search-container">
                <h2>🔍 Cari Buku</h2>
                <form method="get" class="search-form">
                    <input type="text" name="search" class="search-input" 
                           placeholder="Cari berdasarkan judul, penulis, penerbit, atau ISBN..." 
                           value="<?= htmlspecialchars($search_param) ?>">
                    <button type="submit" class="search-btn">Cari</button>
                    <?php if (!empty($search_param)): ?>
                        <a href="buku.php" class="reset-btn">Reset</a>
                    <?php endif; ?>
                </form>
                <?php if (!empty($search_param)): ?>
                    <div class="search-info">
                        Menampilkan hasil pencarian untuk: <strong>"<?= htmlspecialchars($search_param) ?>"</strong>
                    </div>
                <?php endif; ?>
            </div>

            <!-- FORM TAMBAH/EDIT BUKU -->
            <div class="form-container">
                <h2><?= $edit_data ? '✏️ Edit Buku' : '➕ Tambah Buku Baru' ?></h2>
                <form method="post">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id_buku" value="<?= $edit_data['id_buku'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>ISBN</label>
                            <input type="text" name="isbn" 
                                   value="<?= $edit_data ? $edit_data['isbn'] : '' ?>" 
                                   placeholder="Kode ISBN">
                        </div>
                        
                        <div class="form-group">
                            <label>Judul Buku</label>
                            <input type="text" name="judul" 
                                   value="<?= $edit_data ? $edit_data['judul'] : '' ?>" 
                                   placeholder="Masukkan judul buku" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Penulis</label>
                            <input type="text" name="penulis" 
                                   value="<?= $edit_data ? $edit_data['penulis'] : '' ?>" 
                                   placeholder="Nama penulis" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Penerbit</label>
                            <input type="text" name="penerbit" 
                                   value="<?= $edit_data ? $edit_data['penerbit'] : '' ?>" 
                                   placeholder="Nama penerbit">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tahun Terbit</label>
                            <input type="number" name="tahun" 
                                   value="<?= $edit_data ? $edit_data['tahun'] : '' ?>" 
                                   placeholder="Tahun terbit" min="1900" max="2030">
                        </div>
                        
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stok" 
                                   value="<?= $edit_data ? $edit_data['stok'] : '' ?>" 
                                   placeholder="Jumlah stok" required min="0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Lokasi</label>
                            <input type="text" name="lokasi" 
                                   value="<?= $edit_data ? $edit_data['lokasi'] : '' ?>" 
                                   placeholder="Lokasi penyimpanan">
                        </div>
                        
                        <div class="form-group">
                            <label>Tanggal Tiba</label>
                            <input type="date" name="tanggal_tiba" 
                                   value="<?= $edit_data ? $edit_data['tanggal_tiba'] : '' ?>">
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" name="<?= $edit_data ? 'edit' : 'tambah' ?>" class="btn-primary">
                            <?= $edit_data ? 'Update Buku' : 'Tambah Buku' ?>
                        </button>
                        
                        <?php if ($edit_data): ?>
                            <a href="buku.php" class="btn-secondary">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- TABEL DATA BUKU -->
            <div class="table-container">
                <h2>
                    📖 Daftar Buku
                    <span class="table-info">
                        <?= !empty($search_param) ? '(Hasil Pencarian)' : '' ?>
                    </span>
                </h2>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ISBN</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Penerbit</th>
                                <th>Tahun</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1; 
                            mysqli_data_seek($buku, 0);
                            $total_buku = mysqli_num_rows($buku);
                            
                            if ($total_buku > 0): 
                                while($row = mysqli_fetch_array($buku)): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $row['isbn'] ?: '-' ?></td>
                                <td><?= $row['judul'] ?></td>
                                <td><?= $row['penulis'] ?></td>                                        
                                <td><?= $row['penerbit'] ?></td>
                                <td><?= $row['tahun'] ?></td>
                                <td>
                                    <span style="font-weight: 500; color: <?= $row['stok'] > 0 ? '#059669' : '#dc2626' ?>">
                                        <?= $row['stok'] ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="buku.php?edit=<?= $row['id_buku'] ?>" class="btn-edit">Edit</a>
                                    <a href="buku.php?hapus=<?= $row['id_buku'] ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Yakin hapus buku ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="8" class="no-results">
                                    <?php if (!empty($search_param)): ?>
                                        Tidak ditemukan buku dengan kata kunci "<?= htmlspecialchars($search_param) ?>"
                                        <br>
                                        <a href="buku.php" class="reset-btn" style="margin-top: 10px; display: inline-block;">Tampilkan Semua Buku</a>
                                    <?php else: ?>
                                        Belum ada data buku. Silakan tambah buku baru.
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Konfirmasi sebelum menghapus
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Yakin hapus buku ini?')) {
                        e.preventDefault();
                    }
                });
            });
            
            // Auto focus pada input pencarian
            const searchInput = document.querySelector('.search-input');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>