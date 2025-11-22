
CREATE DATABASE db_perpustakaan;
USE db_perpustakaan;

CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255),
    nama_admin VARCHAR(100)
);

CREATE TABLE buku (
    id_buku INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20),
    judul VARCHAR(255),
    penulis VARCHAR(100),
    penerbit VARCHAR(100),
    tahun YEAR,
    stok INT,
    lokasi VARCHAR(50),
    tanggal_tiba DATE
);

CREATE TABLE anggota (
    id_anggota INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(20),
    nama VARCHAR(100),
    kelas VARCHAR(20),
    jurusan VARCHAR(50),
    no_telp VARCHAR(15),
    alamat TEXT
);

CREATE TABLE peminjaman (
    id_peminjaman INT AUTO_INCREMENT PRIMARY KEY,
    id_anggota INT,
    id_buku INT,
    tanggal_pinjam DATE,
    tanggal_kembali DATE,
    status ENUM('dipinjam', 'kembali') DEFAULT 'dipinjam',
    FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota),
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku)
);

CREATE TABLE pengembalian (
    id_pengembalian INT AUTO_INCREMENT PRIMARY KEY,
    id_peminjaman INT,
    tanggal_pengembalian DATE,
    denda INT DEFAULT 0,
    FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman)
);
