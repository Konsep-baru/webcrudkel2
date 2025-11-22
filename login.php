<?php 
include 'config.php';

// FORCE TAMPILKAN LOGIN - Hapus session admin dulu
if (isset($_SESSION['admin'])) {
    unset($_SESSION['admin']);
    unset($_SESSION['nama_admin']);
    unset($_SESSION['id_admin']);
}

$error = "";
$attempt_count = 0;
$max_attempts = 5;

// Proses login
if ($_POST) {
    $username = bersihkan_input($_POST['username']);
    $password = md5(bersihkan_input($_POST['password']));
    
    // Validasi input
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        $sql = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
        $result = mysqli_query($koneksi, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $admin_data = mysqli_fetch_assoc($result);
            
            // Set session data
            $_SESSION['admin'] = $admin_data['username'];
            $_SESSION['nama_admin'] = $admin_data['nama_admin'];
            $_SESSION['id_admin'] = $admin_data['id_admin'];
            $_SESSION['login_time'] = time();
            
            // Log activity
            log_activity("Admin {$admin_data['nama_admin']} login berhasil");
            
            // Redirect ke dashboard
            header("Location: dashboard.php");
            exit;
            
        } else {
            $error = "Username atau password salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Perpustakaan Digital</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-form {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .login-form h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .btn-login {
            width: 100%;
            background: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 1rem;
        }
        
        .btn-login:hover {
            background: #2980b9;
        }
        
        .login-info {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .back-link {
            text-align: center;
        }
        
        .back-link a {
            color: #3498db;
            text-decoration: none;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
            font-size: 3rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <div class="logo">📚</div>
            <h2>Login Admin</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" placeholder="Masukkan username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" placeholder="Masukkan password" required>
                </div>
                
                <button type="submit" class="btn-login">🔐 Login</button>
            </form>
            
            
            <div class="back-link">
                <a href="index.php">← Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>