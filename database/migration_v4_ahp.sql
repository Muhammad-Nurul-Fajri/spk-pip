-- ============================================
-- SPK PIP WP — Migration V4
-- Add AHP (Analytical Hierarchy Process) tables
-- for pairwise comparison, weights, and consistency
-- ============================================

USE spk_pip_wp;

-- ============================================
-- 1. PAIRWISE COMPARISON TABLE
-- Stores Saaty's 1-9 scale comparison values
-- between each pair of criteria
-- ============================================
CREATE TABLE IF NOT EXISTS ahp_perbandingan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kriteria_1 INT NOT NULL,
    id_kriteria_2 INT NOT NULL,
    nilai DOUBLE NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kriteria_1) REFERENCES kriteria(id) ON DELETE CASCADE,
    FOREIGN KEY (id_kriteria_2) REFERENCES kriteria(id) ON DELETE CASCADE,
    UNIQUE KEY unique_pair (id_kriteria_1, id_kriteria_2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. AHP RESULTS TABLE
-- Stores the Priority Vector (AHP weight)
-- for each criterion after validated calculation
-- ============================================
CREATE TABLE IF NOT EXISTS ahp_hasil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kriteria INT NOT NULL,
    priority_vector DOUBLE NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kriteria) REFERENCES kriteria(id) ON DELETE CASCADE,
    UNIQUE KEY unique_kriteria (id_kriteria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. AHP CONSISTENCY TABLE
-- Stores λmax, CI, RI, CR, and validation status
-- Only the latest record is used
-- ============================================
CREATE TABLE IF NOT EXISTS ahp_konsistensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lambda_max DOUBLE NOT NULL DEFAULT 0,
    ci DOUBLE NOT NULL DEFAULT 0,
    ri DOUBLE NOT NULL DEFAULT 0,
    cr DOUBLE NOT NULL DEFAULT 0,
    status ENUM('konsisten','tidak_konsisten') NOT NULL DEFAULT 'tidak_konsisten',
    jumlah_kriteria INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
