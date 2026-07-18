-- ============================================
-- SPK PIP WP — Migration V3
-- Add verification status workflow for Ketua Yayasan approval
-- ============================================

USE spk_pip_wp;

-- Add status_verifikasi column to hasil_wp
ALTER TABLE hasil_wp 
ADD COLUMN status_verifikasi ENUM('menunggu_penilaian','layak','tidak_layak') 
DEFAULT 'menunggu_penilaian' AFTER ranking;
