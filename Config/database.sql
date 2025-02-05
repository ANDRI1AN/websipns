-- phpMyAdmin SQL Dump
-- version 5.2.0
-- Host: localhost
-- Generation Time: Nov 10, 2024
-- Server version: 10.4.27-MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- Buat database
DROP DATABASE IF EXISTS si_pns;
CREATE DATABASE si_pns;
USE si_pns;

-- Tabel pengguna untuk admin
CREATE TABLE pengguna (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel instansi
CREATE TABLE instansi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_instansi VARCHAR(100) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(20),
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel golongan
CREATE TABLE golongan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_golongan VARCHAR(10) NOT NULL,
    pangkat VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel jabatan
CREATE TABLE jabatan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_jabatan VARCHAR(100) NOT NULL,
    level_jabatan ENUM('struktural', 'fungsional') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel data PNS dengan kolom foto
CREATE TABLE pns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nip VARCHAR(18) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    tempat_lahir VARCHAR(100) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    alamat TEXT NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    instansi_id INT NOT NULL,
    jabatan_id INT NOT NULL,
    golongan_id INT NOT NULL,
    pendidikan_terakhir VARCHAR(50) NOT NULL,
    tanggal_masuk DATE NOT NULL,
    status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif',
    foto VARCHAR(255) DEFAULT NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instansi_id) REFERENCES instansi(id),
    FOREIGN KEY (jabatan_id) REFERENCES jabatan(id),
    FOREIGN KEY (golongan_id) REFERENCES golongan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel kenaikan pangkat
CREATE TABLE kenaikan_pangkat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pns_id INT,
    golongan_lama_id INT,
    golongan_baru_id INT,
    tanggal_kenaikan DATE NOT NULL,
    nomor_sk VARCHAR(50) NOT NULL,
    tanggal_sk DATE NOT NULL,
    jenis_kenaikan ENUM('reguler', 'pilihan', 'struktural', 'fungsional') NOT NULL,
    status ENUM('diajukan', 'proses', 'disetujui', 'ditolak') DEFAULT 'diajukan',
    keterangan TEXT,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pns_id) REFERENCES pns(id),
    FOREIGN KEY (golongan_lama_id) REFERENCES golongan(id),
    FOREIGN KEY (golongan_baru_id) REFERENCES golongan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel riwayat pendidikan
CREATE TABLE riwayat_pendidikan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pns_id INT,
    tingkat_pendidikan VARCHAR(50) NOT NULL,
    nama_institusi VARCHAR(100) NOT NULL,
    jurusan VARCHAR(100),
    tahun_lulus YEAR NOT NULL,
    nomor_ijazah VARCHAR(50),
    FOREIGN KEY (pns_id) REFERENCES pns(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel riwayat jabatan
CREATE TABLE riwayat_jabatan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pns_id INT,
    jabatan_id INT,
    nomor_sk VARCHAR(50) NOT NULL,
    tanggal_sk DATE NOT NULL,
    tmt_jabatan DATE NOT NULL,
    status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif',
    FOREIGN KEY (pns_id) REFERENCES pns(id),
    FOREIGN KEY (jabatan_id) REFERENCES jabatan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data default untuk admin (password: admin123)
INSERT INTO pengguna (username, password, nama_lengkap) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- Insert data untuk golongan
INSERT INTO golongan (nama_golongan, pangkat) VALUES
('I/a', 'Juru Muda'),
('I/b', 'Juru Muda Tingkat I'),
('I/c', 'Juru'),
('I/d', 'Juru Tingkat I'),
('II/a', 'Pengatur Muda'),
('II/b', 'Pengatur Muda Tingkat I'),
('II/c', 'Pengatur'),
('II/d', 'Pengatur Tingkat I'),
('III/a', 'Penata Muda'),
('III/b', 'Penata Muda Tingkat I'),
('III/c', 'Penata'),
('III/d', 'Penata Tingkat I'),
('IV/a', 'Pembina'),
('IV/b', 'Pembina Tingkat I'),
('IV/c', 'Pembina Utama Muda'),
('IV/d', 'Pembina Utama Madya'),
('IV/e', 'Pembina Utama');

-- Insert data untuk instansi
INSERT INTO instansi (nama_instansi, alamat, telepon) VALUES
('Kantor Pusat', 'Jl. Utama No. 123', '021-555-0123'),
('Dinas Pendidikan', 'Jl. Pendidikan No. 1', '021-555-0124'),
('Dinas Kesehatan', 'Jl. Kesehatan No. 2', '021-555-0125'),
('Dinas Pekerjaan Umum', 'Jl. PU No. 3', '021-555-0126'),
('Dinas Perhubungan', 'Jl. Perhubungan No. 4', '021-555-0127'),
('Dinas Sosial', 'Jl. Sosial No. 5', '021-555-0128'),
('Dinas Kependudukan', 'Jl. Kependudukan No. 6', '021-555-0129');

-- Insert data untuk jabatan
INSERT INTO jabatan (nama_jabatan, level_jabatan) VALUES
('Kepala Dinas', 'struktural'),
('Sekretaris', 'struktural'),
('Kepala Bidang', 'struktural'),
('Kepala Seksi', 'struktural'),
('Kepala Sub Bagian', 'struktural'),
('Staff Administrasi', 'fungsional'),
('Staff Keuangan', 'fungsional'),
('Staff IT', 'fungsional'),
('Staff Kepegawaian', 'fungsional'),
('Analis', 'fungsional'),
('Pengawas', 'fungsional'),
('Auditor', 'fungsional'),
('Peneliti', 'fungsional'),
('Perencana', 'fungsional'),
('Arsiparis', 'fungsional');

-- Trigger untuk mencatat foto yang akan dihapus
DELIMITER //
CREATE TRIGGER before_delete_pns 
BEFORE DELETE ON pns
FOR EACH ROW 
BEGIN
    SET @old_foto = OLD.foto;
END//
DELIMITER ;

COMMIT;