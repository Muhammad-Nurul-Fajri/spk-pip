CREATE DATABASE IF NOT EXISTS spk_pip_wp;
USE spk_pip_wp;

-- Table users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100),
    username VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin','ketua_yayasan','siswa')
);

-- Table siswa
CREATE TABLE IF NOT EXISTS siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_alternatif VARCHAR(10) UNIQUE,
    nama VARCHAR(100),
    kelas VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table kriteria
CREATE TABLE IF NOT EXISTS kriteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_kriteria VARCHAR(10) UNIQUE,
    nama_kriteria VARCHAR(100),
    bobot INT,
    jenis ENUM('benefit','cost')
);

-- Table sub_kriteria
CREATE TABLE IF NOT EXISTS sub_kriteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kriteria INT,
    nama_sub VARCHAR(100),
    nilai INT,
    FOREIGN KEY (id_kriteria) REFERENCES kriteria(id) ON DELETE CASCADE
);

-- Table penilaian
CREATE TABLE IF NOT EXISTS penilaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_siswa INT,
    id_kriteria INT,
    nilai DOUBLE,
    FOREIGN KEY (id_siswa) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (id_kriteria) REFERENCES kriteria(id) ON DELETE CASCADE
);

-- Table hasil_wp
CREATE TABLE IF NOT EXISTS hasil_wp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_siswa INT,
    nilai_s DOUBLE,
    nilai_v DOUBLE,
    ranking INT,
    FOREIGN KEY (id_siswa) REFERENCES siswa(id) ON DELETE CASCADE
);

-- Insert Default Users
INSERT INTO users (nama, username, password, role) VALUES
('Administrator', 'admin', 'admin123', 'admin'),
('Ketua Yayasan', 'ketua', 'ketua123', 'ketua_yayasan'),
('Budi Santoso (Siswa)', 'siswa', 'siswa123', 'siswa')
ON DUPLICATE KEY UPDATE id=id;

-- Insert Default Kriteria
INSERT INTO kriteria (id, kode_kriteria, nama_kriteria, bobot, jenis) VALUES
(1, 'C1', 'Penghasilan Orang Tua', 5, 'cost'),
(2, 'C2', 'Jumlah Tanggungan', 4, 'benefit'),
(3, 'C3', 'Nilai Rata-rata Raport', 3, 'benefit'),
(4, 'C4', 'Kehadiran', 3, 'benefit'),
(5, 'C5', 'Kepemilikan Kartu Bantuan', 2, 'benefit')
ON DUPLICATE KEY UPDATE id=id;

-- Insert Default Sub Kriteria
INSERT INTO sub_kriteria (id, id_kriteria, nama_sub, nilai) VALUES
-- C1 Penghasilan Orang Tua
(1, 1, '< Rp 1.000.000', 5),
(2, 1, 'Rp 1.000.000 - Rp 2.000.000', 4),
(3, 1, 'Rp 2.000.001 - Rp 3.000.000', 3),
(4, 1, 'Rp 3.000.001 - Rp 4.000.000', 2),
(5, 1, '> Rp 4.000.000', 1),
-- C2 Jumlah Tanggungan
(6, 2, '> 4 Anak', 5),
(7, 2, '4 Anak', 4),
(8, 2, '3 Anak', 3),
(9, 2, '2 Anak', 2),
(10, 2, '1 Anak', 1),
-- C3 Nilai Rata-rata Raport
(11, 3, '> 90', 5),
(12, 3, '81 - 90', 4),
(13, 3, '71 - 80', 3),
(14, 3, '60 - 70', 2),
(15, 3, '< 60', 1),
-- C4 Kehadiran
(16, 4, '> 95%', 5),
(17, 4, '90% - 95%', 4),
(18, 4, '85% - 89%', 3),
(19, 4, '80% - 84%', 2),
(20, 4, '< 80%', 1),
-- C5 Kepemilikan Kartu
(21, 5, 'Memiliki KIP & PKH', 5),
(22, 5, 'Memiliki salah satu (KIP/PKH/KKS)', 4),
(23, 5, 'Hanya memiliki SKTM', 3),
(24, 5, 'Tidak memiliki kartu bantuan / SKTM', 1)
ON DUPLICATE KEY UPDATE id=id;

-- Insert Default Siswa
INSERT INTO siswa (id, kode_alternatif, nama, kelas) VALUES
(1, 'A1', 'Budi Santoso', 'XII IPA 1'),
(2, 'A2', 'Ani Lestari', 'XI IPS 2'),
(3, 'A3', 'Citra Dewi', 'X-3'),
(4, 'A4', 'Dedi Wijaya', 'XII IPS 1')
ON DUPLICATE KEY UPDATE id=id;

-- Insert Default Penilaian
INSERT INTO penilaian (id_siswa, id_kriteria, nilai) VALUES
(1, 1, 5), (1, 2, 3), (1, 3, 4), (1, 4, 4), (1, 5, 4),
(2, 1, 4), (2, 2, 5), (2, 3, 5), (2, 4, 5), (2, 5, 5),
(3, 1, 3), (3, 2, 2), (3, 3, 3), (3, 4, 3), (3, 5, 3),
(4, 1, 2), (4, 2, 4), (4, 3, 4), (4, 4, 5), (4, 5, 1);
