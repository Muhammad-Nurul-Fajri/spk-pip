-- ============================================
-- SPK PIP WP — Migration V2
-- Full restructure: 6 criteria, expanded siswa,
-- hashed passwords, new tables
-- ============================================

USE spk_pip_wp;

-- ============================================
-- 1. CLEAR OLD DATA (order matters for FKs)
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE hasil_wp;
TRUNCATE TABLE penilaian;
TRUNCATE TABLE sub_kriteria;
TRUNCATE TABLE kriteria;
TRUNCATE TABLE siswa;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 2. EXPAND siswa TABLE
-- ============================================
ALTER TABLE siswa ADD COLUMN id_user INT AFTER id;
ALTER TABLE siswa ADD COLUMN nis VARCHAR(20) AFTER kode_alternatif;
ALTER TABLE siswa ADD COLUMN nisn VARCHAR(20) AFTER nis;
ALTER TABLE siswa ADD COLUMN tempat_lahir VARCHAR(100) AFTER kelas;
ALTER TABLE siswa ADD COLUMN tanggal_lahir DATE AFTER tempat_lahir;
ALTER TABLE siswa ADD COLUMN alamat TEXT AFTER tanggal_lahir;
ALTER TABLE siswa ADD COLUMN no_hp VARCHAR(20) AFTER alamat;
ALTER TABLE siswa ADD COLUMN foto VARCHAR(255) AFTER no_hp;
ALTER TABLE siswa ADD COLUMN pekerjaan_ortu VARCHAR(100) AFTER foto;
ALTER TABLE siswa ADD COLUMN penghasilan_ortu VARCHAR(100) AFTER pekerjaan_ortu;
ALTER TABLE siswa ADD COLUMN jumlah_tanggungan INT DEFAULT 0 AFTER penghasilan_ortu;
ALTER TABLE siswa ADD COLUMN status_kartu_miskin VARCHAR(100) AFTER jumlah_tanggungan;
ALTER TABLE siswa ADD COLUMN nilai_akhir_semester DOUBLE DEFAULT 0 AFTER status_kartu_miskin;
ALTER TABLE siswa ADD COLUMN hafalan_quran INT DEFAULT 0 AFTER nilai_akhir_semester;
ALTER TABLE siswa ADD COLUMN status_pendaftaran ENUM('draft','submitted','verified','processed','accepted','rejected') DEFAULT 'draft' AFTER hafalan_quran;

-- ============================================
-- 3. NEW TABLES
-- ============================================
CREATE TABLE IF NOT EXISTS dokumen_pendaftaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_siswa INT,
    jenis_dokumen ENUM('kk','ktp_ortu','kartu_bantuan','raport'),
    nama_file VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_siswa) REFERENCES siswa(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200),
    isi TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rekap_pip_tahunan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tahun_ajaran VARCHAR(20) UNIQUE,
    jumlah_penerima INT DEFAULT 0,
    total_dana DOUBLE DEFAULT 0
);

-- ============================================
-- 4. SEED USERS (bcrypt hashed passwords)
-- ============================================
INSERT INTO users (id, nama, username, password, role) VALUES
(1, 'Administrator', 'admin', '$2y$12$Q/VYh04QC9fTjQYWCBuud.oeFXzzwVCA9Mf9OXT3Azo7qfgVtVs0K', 'admin'),
(2, 'Ketua Yayasan', 'ketua', '$2y$12$5yn626giAmEy/yVDsvn2EeA52awCste37dYYbtSwznryMKpj8V09y', 'ketua_yayasan'),
(3, 'Ahmad Fauzi', 'siswa1', '$2y$12$YBbKCNhA7v0h4ZdvVKYqau5zyvrXkwx7ZxC8YNwOkJp4oCvEaVtS2', 'siswa');

-- ============================================
-- 5. SEED 6 CRITERIA (per prompt specification)
-- ============================================
INSERT INTO kriteria (id, kode_kriteria, nama_kriteria, bobot, jenis) VALUES
(1, 'C1', 'Pekerjaan Orang Tua',               20, 'cost'),
(2, 'C2', 'Penghasilan Orang Tua',              25, 'cost'),
(3, 'C3', 'Jumlah Tanggungan',                  15, 'benefit'),
(4, 'C4', 'Status Pemegang Kartu Kemiskinan',   15, 'benefit'),
(5, 'C5', 'Nilai Akhir Semester',               15, 'benefit'),
(6, 'C6', 'Hafalan Al-Quran',                   10, 'benefit');

-- ============================================
-- 6. SEED SUB KRITERIA (scale 1-5)
-- ============================================
INSERT INTO sub_kriteria (id, id_kriteria, nama_sub, nilai) VALUES
-- C1 Pekerjaan Orang Tua (Cost: higher value = less favorable job → higher score)
(1,  1, 'Buruh Harian',           5),
(2,  1, 'Petani / Nelayan',       4),
(3,  1, 'Wiraswasta / Pedagang',  3),
(4,  1, 'Karyawan Swasta',        2),
(5,  1, 'PNS / TNI / Polri',      1),
-- C2 Penghasilan Orang Tua (Cost)
(6,  2, '< Rp 1.000.000',                 5),
(7,  2, 'Rp 1.000.000 - Rp 2.000.000',    4),
(8,  2, 'Rp 2.000.001 - Rp 3.000.000',    3),
(9,  2, 'Rp 3.000.001 - Rp 5.000.000',    2),
(10, 2, '> Rp 5.000.000',                 1),
-- C3 Jumlah Tanggungan (Benefit)
(11, 3, '> 5 Orang',   5),
(12, 3, '4 - 5 Orang', 4),
(13, 3, '3 Orang',     3),
(14, 3, '2 Orang',     2),
(15, 3, '1 Orang',     1),
-- C4 Status Pemegang Kartu Kemiskinan (Benefit)
(16, 4, 'KIP (Kartu Indonesia Pintar)',                5),
(17, 4, 'PKH (Program Keluarga Harapan)',              4),
(18, 4, 'KKS / KPS (Kartu Keluarga Sejahtera)',       3),
(19, 4, 'SKTM (Surat Keterangan Tidak Mampu)',        2),
(20, 4, 'Tidak Memiliki Kartu Bantuan',               1),
-- C5 Nilai Akhir Semester (Benefit)
(21, 5, '> 90 (Sangat Baik)',    5),
(22, 5, '81 - 90 (Baik)',       4),
(23, 5, '71 - 80 (Cukup)',      3),
(24, 5, '61 - 70 (Kurang)',     2),
(25, 5, '< 60 (Sangat Kurang)', 1),
-- C6 Hafalan Al-Quran (Benefit)
(26, 6, '> 10 Juz',    5),
(27, 6, '6 - 10 Juz',  4),
(28, 6, '3 - 5 Juz',   3),
(29, 6, '1 - 2 Juz',   2),
(30, 6, 'Belum Hafal',  1);

-- ============================================
-- 7. SEED SAMPLE SISWA (with application data)
-- ============================================
INSERT INTO siswa (id, id_user, kode_alternatif, nis, nisn, nama, kelas, tempat_lahir, tanggal_lahir, alamat, no_hp, pekerjaan_ortu, penghasilan_ortu, jumlah_tanggungan, status_kartu_miskin, nilai_akhir_semester, hafalan_quran, status_pendaftaran) VALUES
(1, 3, 'A1', '10001', '0012345001', 'Ahmad Fauzi',    'XII IPA',  'Sibuhuan', '2007-03-15', 'Jl. Merdeka No. 10, Sibuhuan', '081234567890', 'Buruh Harian', '< Rp 1.000.000', 4, 'KIP (Kartu Indonesia Pintar)', 85.5, 5, 'verified'),
(2, NULL, 'A2', '10002', '0012345002', 'Siti Aminah',   'XI IPS',   'Padang Lawas', '2008-07-22', 'Desa Aek Nabara, Padang Lawas', '081234567891', 'Petani / Nelayan', 'Rp 1.000.000 - Rp 2.000.000', 5, 'PKH (Program Keluarga Harapan)', 78.0, 3, 'verified'),
(3, NULL, 'A3', '10003', '0012345003', 'Muhammad Rizki', 'XII IPS',  'Gunung Tua', '2007-11-08', 'Jl. Imam Bonjol No. 5, Gunung Tua', '081234567892', 'Wiraswasta / Pedagang', 'Rp 2.000.001 - Rp 3.000.000', 3, 'SKTM (Surat Keterangan Tidak Mampu)', 90.0, 8, 'verified'),
(4, NULL, 'A4', '10004', '0012345004', 'Nur Aisyah',    'X',        'Sibuhuan', '2009-01-30', 'Gg. Mawar No. 3, Sibuhuan', '081234567893', 'Karyawan Swasta', 'Rp 3.000.001 - Rp 5.000.000', 2, 'Tidak Memiliki Kartu Bantuan', 72.5, 1, 'verified'),
(5, NULL, 'A5', '10005', '0012345005', 'Rahmat Hidayat', 'XI IPA',   'Batang Toru', '2008-05-12', 'Jl. Lintas Sumatera, Batang Toru', '081234567894', 'Buruh Harian', 'Rp 1.000.000 - Rp 2.000.000', 6, 'KKS / KPS (Kartu Keluarga Sejahtera)', 82.0, 4, 'verified');

-- ============================================
-- 8. SEED PENILAIAN (based on student data → sub_kriteria mapping)
-- ============================================
INSERT INTO penilaian (id_siswa, id_kriteria, nilai) VALUES
-- Ahmad Fauzi (A1): Buruh=5, <1jt=5, 4org=4, KIP=5, 85.5→4, 5juz=3
(1, 1, 5), (1, 2, 5), (1, 3, 4), (1, 4, 5), (1, 5, 4), (1, 6, 3),
-- Siti Aminah (A2): Petani=4, 1-2jt=4, 5org=4, PKH=4, 78→3, 3juz=3
(2, 1, 4), (2, 2, 4), (2, 3, 4), (2, 4, 4), (2, 5, 3), (2, 6, 3),
-- Muhammad Rizki (A3): Wiraswasta=3, 2-3jt=3, 3org=3, SKTM=2, 90→5, 8juz=4
(3, 1, 3), (3, 2, 3), (3, 3, 3), (3, 4, 2), (3, 5, 5), (3, 6, 4),
-- Nur Aisyah (A4): Karyawan=2, 3-5jt=2, 2org=2, Tidak ada=1, 72.5→3, 1juz=2
(4, 1, 2), (4, 2, 2), (4, 3, 2), (4, 4, 1), (4, 5, 3), (4, 6, 2),
-- Rahmat Hidayat (A5): Buruh=5, 1-2jt=4, >5org=5, KKS=3, 82→4, 4juz=3
(5, 1, 5), (5, 2, 4), (5, 3, 5), (5, 4, 3), (5, 5, 4), (5, 6, 3);

-- ============================================
-- 9. SEED HISTORICAL PIP DATA (for charts)
-- ============================================
INSERT INTO rekap_pip_tahunan (tahun_ajaran, jumlah_penerima, total_dana) VALUES
('2022-2023', 25, 11250000),
('2023-2024', 30, 13500000),
('2024-2025', 28, 12600000);

-- ============================================
-- 10. SEED SAMPLE ANNOUNCEMENTS
-- ============================================
INSERT INTO pengumuman (judul, isi, created_by) VALUES
('Pendaftaran PIP 2025-2026 Dibuka', 'Pendaftaran penerima bantuan Program Indonesia Pintar (PIP) tahun ajaran 2025-2026 telah resmi dibuka. Silakan lengkapi data pendaftaran melalui sistem ini. Batas waktu pengisian data: 30 Juli 2026.', 1),
('Verifikasi Data Tahap 1 Selesai', 'Proses verifikasi data pendaftaran tahap pertama telah selesai dilakukan oleh pihak sekolah. Siswa yang datanya sudah terverifikasi dapat melihat status pada dashboard masing-masing.', 1);
