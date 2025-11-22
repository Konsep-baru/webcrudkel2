<?php
session_start();

// Konfigurasi Database
$host = "localhost";
$user = "root"; 
$pass = "";
$db   = "db_perpustakaan";

// Membuat koneksi ke database
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("❌ Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset ke utf8 untuk support karakter Indonesia
mysqli_set_charset($koneksi, "utf8");

// Fungsi untuk mencegah SQL Injection
function bersihkan_input($data) {
    global $koneksi;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($koneksi, $data);
}

// Fungsi untuk redirect dengan pesan
function redirect($url, $pesan = "") {
    if (!empty($pesan)) {
        $_SESSION['pesan'] = $pesan;
    }
    header("Location: " . $url);
    exit;
}

// Fungsi untuk menampilkan pesan
function tampilkan_pesan() {
    if (isset($_SESSION['pesan'])) {
        echo '<div class="alert info">' . $_SESSION['pesan'] . '</div>';
        unset($_SESSION['pesan']);
    }
}

// FUNGSI CEK LOGIN - YANG BARU DITAMBAH
function cek_login() {
    if (!isset($_SESSION['admin'])) {
        // Simpan URL yang diakses untuk redirect setelah login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        
        // Redirect ke login page
        header("Location: login.php");
        exit;
    }
}

// Fungsi cek login dengan return boolean (optional)
function is_logged_in() {
    return isset($_SESSION['admin']);
}

// Fungsi untuk mendapatkan data admin yang login
function get_admin_data() {
    global $koneksi;
    if (isset($_SESSION['admin'])) {
        $username = $_SESSION['admin'];
        $sql = "SELECT * FROM admin WHERE username = '$username'";
        $result = mysqli_query($koneksi, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
    }
    return null;
}

// Fungsi base_url untuk memudahkan path
function base_url($url = null) {
    $base_url = "http://localhost/perpustakaan_digital";
    if ($url != null) {
        return $base_url . "/" . $url;
    } else {
        return $base_url;
    }
}

// Auto-create default admin jika belum ada
$check_admin = mysqli_query($koneksi, "SELECT * FROM admin LIMIT 1");
if (mysqli_num_rows($check_admin) == 0) {
    $default_password = md5('pustaka'); // Password default
    $sql_admin = "INSERT INTO admin (username, password, nama_admin) VALUES ('admin', '$default_password', 'Administrator')";
    
    if (!mysqli_query($koneksi, $sql_admin)) {
        error_log("Gagal membuat admin default: " . mysqli_error($koneksi));
    }
}

// Cek jika tabel tidak ada, buat secara otomatis
$tables = ['admin', 'buku', 'anggota', 'peminjaman', 'pengembalian'];
foreach ($tables as $table) {
    $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($check_table) == 0) {
        // Jika tabel tidak ada, jalankan SQL untuk membuatnya
        buat_tabel_otomatis($koneksi);
        break;
    }
}

// Fungsi untuk membuat tabel otomatis
function buat_tabel_otomatis($koneksi) {
    $sql_queries = [
        "CREATE TABLE IF NOT EXISTS admin (
            id_admin INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            nama_admin VARCHAR(100) NOT NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS buku (
            id_buku INT AUTO_INCREMENT PRIMARY KEY,
            isbn VARCHAR(20),
            judul VARCHAR(255) NOT NULL,
            penulis VARCHAR(100) NOT NULL,
            penerbit VARCHAR(100),
            tahun YEAR,
            stok INT DEFAULT 0,
            lokasi VARCHAR(50),
            tanggal_tiba VARCHAR(50)
        )",
        
        "CREATE TABLE IF NOT EXISTS anggota (
            id_anggota INT AUTO_INCREMENT PRIMARY KEY,
            nis VARCHAR(20) UNIQUE NOT NULL,
            nama VARCHAR(100) NOT NULL,
            kelas VARCHAR(20) NOT NULL,
            jurusan VARCHAR(50),
            no_telp VARCHAR(15),
            alamat TEXT
        )",
        
        "CREATE TABLE IF NOT EXISTS peminjaman (
            id_peminjaman INT AUTO_INCREMENT PRIMARY KEY,
            id_anggota INT,
            id_buku INT,
            tanggal_pinjam DATE,
            tanggal_kembali DATE,
            status ENUM('dipinjam', 'kembali') DEFAULT 'dipinjam',
            FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
            FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS pengembalian (
            id_pengembalian INT AUTO_INCREMENT PRIMARY KEY,
            id_peminjaman INT,
            tanggal_pengembalian DATE,
            denda INT DEFAULT 0,
            FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman) ON DELETE CASCADE
        )",
        
        "INSERT IGNORE INTO admin (username, password, nama_admin) VALUES 
        ('admin', MD5('admin123'), 'Administrator')",
        
    ];
    
    foreach ($sql_queries as $sql) {
        if (!mysqli_query($koneksi, $sql)) {
            error_log("Error executing SQL: " . mysqli_error($koneksi));
        }
    }
}

// Fungsi untuk log activity (bisa dikembangkan)
function log_activity($activity) {
    // Bisa disimpan ke database atau file log
    error_log(date('Y-m-d H:i:s') . " - " . $activity . "\n", 3, "activity.log");
}

// Set timezone ke Indonesia
date_default_timezone_set('Asia/Jakarta');

// Error reporting (disable di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>