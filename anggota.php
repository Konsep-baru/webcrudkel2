<?php
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// CREATE - Tambah Anggota
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $no_telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    
    $sql = "INSERT INTO anggota (nama, no_telp, alamat) 
            VALUES ('$nama', '$no_telp', '$alamat')";
    
    if (mysqli_query($koneksi, $sql)) {
        $success = "Anggota berhasil ditambahkan!";
    } else {
        $error = "Error: " . mysqli_error($koneksi);
    }
}

// UPDATE - Edit Anggota
if (isset($_POST['edit'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id_anggota']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $no_telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    
    $sql = "UPDATE anggota SET nama='$nama', no_telp='$no_telp', alamat='$alamat' 
            WHERE id_anggota='$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        $success = "Data anggota berhasil diupdate!";
    } else {
        $error = "Error: " . mysqli_error($koneksi);
    }
}

// DELETE - Hapus Anggota
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $sql = "DELETE FROM anggota WHERE id_anggota='$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        $success = "Anggota berhasil dihapus!";
    } else {
        $error = "Error: " . mysqli_error($koneksi);
    }
}

// READ - Ambil data anggota
$anggota = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY id_anggota DESC");

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $edit_data = mysqli_query($koneksi, "SELECT * FROM anggota WHERE id_anggota='$id'");
    $edit_data = mysqli_fetch_assoc($edit_data);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Anggota - Sistem Perpustakaan</title>
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
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
        }
        
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
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
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-edit {
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        
        .btn-edit:hover {
            background: #bfdbfe;
        }
        
        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .btn-delete:hover {
            background: #fecaca;
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
            
            .actions {
                flex-direction: column;
                gap: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>👥 Sistem Kelola Anggota Perpustakaan</h1>
            <p>Kelola data anggota perpustakaan dengan mudah</p>
        </header>

        <main>
            <?php if (isset($success)): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>

            <!-- FORM TAMBAH/EDIT ANGGOTA -->
            <div class="form-container">
                <h2><?= $edit_data ? '✏️ Edit Anggota' : '➕ Tambah Anggota Baru' ?></h2>
                <form method="post">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id_anggota" value="<?= $edit_data['id_anggota'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" 
                                   value="<?= $edit_data ? $edit_data['nama'] : '' ?>" 
                                   placeholder="Nama lengkap anggota" required>
                        </div>
                        
                        <div class="form-group">
                            <label>No. Telepon</label>
                            <input type="text" name="no_telp" 
                                   value="<?= $edit_data ? $edit_data['no_telp'] : '' ?>" 
                                   placeholder="Nomor telepon aktif">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" placeholder="Alamat lengkap" rows="3"><?= $edit_data ? $edit_data['alamat'] : '' ?></textarea>
                    </div>
                    
                    <button type="submit" name="<?= $edit_data ? 'edit' : 'tambah' ?>" class="btn-primary">
                        <?= $edit_data ? 'Update Anggota' : 'Tambah Anggota' ?>
                    </button>
                    
                    <?php if ($edit_data): ?>
                        <a href="anggota.php" class="btn-secondary">Batal</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- TABEL DATA ANGGOTA -->
            <div class="table-container">
                <h2>📋 Daftar Anggota Perpustakaan</h2>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>No. Telepon</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        mysqli_data_seek($anggota, 0); // Reset pointer
                        while($row = mysqli_fetch_array($anggota)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td><?= $row['no_telp'] ?: '-' ?></td>
                            <td><?= strlen($row['alamat']) > 50 ? substr($row['alamat'], 0, 50) . '...' : $row['alamat'] ?></td>
                            <td class="actions">
                                <a href="anggota.php?edit=<?= $row['id_anggota'] ?>" class="btn-edit">Edit</a>
                                <a href="anggota.php?hapus=<?= $row['id_anggota'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Yakin hapus anggota ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Konfirmasi sebelum menghapus
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Yakin hapus anggota ini?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>