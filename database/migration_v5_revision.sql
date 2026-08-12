-- ============================================
-- SPK PIP WP — Migration V5 (Revision)
-- Implement proposal normalized AHP weights, sub-criteria alignment,
-- registration_order, document type update (ktp_ortu -> sertifikat_hafalan)
-- ============================================

USE spk_pip_wp;

-- 1. Modify kriteria.bobot to DOUBLE to support normalized weights (e.g. -0.168)
ALTER TABLE kriteria MODIFY COLUMN bobot DOUBLE NOT NULL;

-- 2. Update criteria with exact normalized AHP weights & names from proposal
UPDATE kriteria SET nama_kriteria = 'Pekerjaan Orang Tua', bobot = -0.168, jenis = 'cost' WHERE kode_kriteria = 'C1';
UPDATE kriteria SET nama_kriteria = 'Penghasilan Orang Tua', bobot = -0.371, jenis = 'cost' WHERE kode_kriteria = 'C2';
UPDATE kriteria SET nama_kriteria = 'Jumlah Tanggungan', bobot = 0.249, jenis = 'benefit' WHERE kode_kriteria = 'C3';
UPDATE kriteria SET nama_kriteria = 'Status Pemegang Kartu Kemiskinan', bobot = 0.107, jenis = 'benefit' WHERE kode_kriteria = 'C4';
UPDATE kriteria SET nama_kriteria = 'Nilai Akhir Semester', bobot = 0.065, jenis = 'benefit' WHERE kode_kriteria = 'C5';
UPDATE kriteria SET nama_kriteria = 'Hafalan Al-Qur\'an', bobot = 0.040, jenis = 'benefit' WHERE kode_kriteria = 'C6';

-- 3. Replace sub_kriteria to match exact proposal scale & values
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE sub_kriteria;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO sub_kriteria (id, id_kriteria, nama_sub, nilai) VALUES
-- C1 Pekerjaan Orang Tua (1..5)
(1, 1, 'Buruh Harian', 1),
(2, 1, 'Petani / Nelayan', 2),
(3, 1, 'Wiraswasta / Pedagang', 3),
(4, 1, 'Karyawan Swasta', 4),
(5, 1, 'PNS / TNI / Polri', 5),

-- C2 Penghasilan Orang Tua (1..5)
(6, 2, '<= Rp 500.000', 1),
(7, 2, 'Rp 501.000 - Rp 999.000', 2),
(8, 2, 'Rp 1.000.000 - Rp 1.999.000', 3),
(9, 2, 'Rp 2.000.000 - Rp 4.999.000', 4),
(10, 2, '> Rp 5.000.000', 5),

-- C3 Jumlah Tanggungan (1..5)
(11, 3, '1 Orang', 1),
(12, 3, '2 - 3 Orang', 2),
(13, 3, '4 - 5 Orang', 3),
(14, 3, '6 - 7 Orang', 4),
(15, 3, '>= 8 Orang', 5),

-- C4 Status Pemegang Kartu Kemiskinan (1, 4, 5)
(16, 4, 'Tidak Memiliki Kartu Bantuan', 1),
(17, 4, 'Pemegang KKS / PKH / KPS / SKTM', 4),
(18, 4, 'Pemegang KIP (Kartu Indonesia Pintar)', 5),

-- C5 Nilai Akhir Semester (1..5)
(19, 5, '< 70', 1),
(20, 5, '70 - 75', 2),
(21, 5, '76 - 80', 3),
(22, 5, '81 - 85', 4),
(23, 5, '86 - 100', 5),

-- C6 Hafalan Al-Qur'an (1, 3, 5)
(24, 6, '<= 1 Juz', 1),
(25, 6, '2 Juz', 3),
(26, 6, '>= 3 Juz', 5);

-- 4. Add registration_order column to siswa if not exists
ALTER TABLE siswa ADD COLUMN registration_order INT AFTER id;

-- 5. Update dokumen_pendaftaran enum type (ktp_ortu -> sertifikat_hafalan)
ALTER TABLE dokumen_pendaftaran MODIFY COLUMN jenis_dokumen ENUM('kk','ktp_ortu','sertifikat_hafalan','kartu_bantuan','raport');
UPDATE dokumen_pendaftaran SET jenis_dokumen = 'sertifikat_hafalan' WHERE jenis_dokumen = 'ktp_ortu';
ALTER TABLE dokumen_pendaftaran MODIFY COLUMN jenis_dokumen ENUM('kk','sertifikat_hafalan','kartu_bantuan','raport');
