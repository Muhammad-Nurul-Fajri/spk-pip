<?php
session_start();
require_once '../../config/koneksi.php';
require_role('siswa');

$id_user = $_SESSION['id_user'];

// Get siswa data
$stmt = mysqli_prepare($koneksi, "SELECT * FROM siswa WHERE id_user = ?");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$siswa) { header("Location: dashboard.php"); exit; }

$can_edit = in_array($siswa['status_pendaftaran'], ['draft', 'submitted']);

// Get existing documents
$docs = [];
$dq = mysqli_prepare($koneksi, "SELECT jenis_dokumen, nama_file FROM dokumen_pendaftaran WHERE id_siswa = ?");
mysqli_stmt_bind_param($dq, "i", $siswa['id']);
mysqli_stmt_execute($dq);
$dr = mysqli_stmt_get_result($dq);
while ($d = mysqli_fetch_assoc($dr)) { $docs[$d['jenis_dokumen']] = $d['nama_file']; }
mysqli_stmt_close($dq);

// Get sub_kriteria options
$sub_options = [];
$sq = mysqli_query($koneksi, "SELECT id_kriteria, nama_sub, nilai FROM sub_kriteria ORDER BY id_kriteria, nilai DESC");
while ($s = mysqli_fetch_assoc($sq)) { $sub_options[$s['id_kriteria']][] = $s; }

// Session errors/old data
$errors = $_SESSION['pendaftaran_errors'] ?? [];
$old = $_SESSION['pendaftaran_old'] ?? [];
unset($_SESSION['pendaftaran_errors'], $_SESSION['pendaftaran_old']);

// Merge old POST data over siswa data for re-display after error
$data = $siswa;
if (!empty($old)) {
    foreach ($old as $k => $v) { $data[$k] = $v; }
}

$page_title = 'Pendaftaran PIP';
$active_menu = 'pendaftaran';
$asset_depth = 2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include '../layouts/head.php'; ?>
</head>
<body>
<?php include '../layouts/sidebar_siswa.php'; ?>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-file-pen"></i>
            <h4>Pendaftaran Bantuan PIP</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? $siswa['nama']); ?></div>
        </div>
    </div>

    <?php if (!$can_edit): ?>
        <div class="alert alert-info" style="border-radius:10px;">
            <i class="fa fa-info-circle me-2"></i>Data Anda sudah <strong><?php echo $siswa['status_pendaftaran']; ?></strong> dan tidak dapat diubah.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="border-radius:10px;">
            <strong>Terdapat kesalahan:</strong>
            <ul class="mb-0 mt-1"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form action="../../app/controllers/PendaftaranController.php" method="POST" enctype="multipart/form-data" id="formPendaftaran">
    <div class="row g-3">

        <!-- DATA PRIBADI -->
        <div class="col-12">
            <div class="card-custom">
                <h5 class="mb-3" style="color:var(--primary);font-weight:bold;"><i class="fa fa-user me-2"></i>Data Pribadi Siswa</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required value="<?php echo htmlspecialchars($data['nama'] ?? ''); ?>" <?php echo $can_edit?'':'disabled'; ?>>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">NIS <span class="text-danger">*</span></label>
                        <input type="text" name="nis" class="form-control" required value="<?php echo htmlspecialchars($data['nis'] ?? ''); ?>" <?php echo $can_edit?'':'disabled'; ?>>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">NISN</label>
                        <input type="text" name="nisn" class="form-control" value="<?php echo htmlspecialchars($data['nisn'] ?? ''); ?>" <?php echo $can_edit?'':'disabled'; ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Kelas <span class="text-danger">*</span></label>
                        <input type="text" name="kelas" class="form-control" required value="<?php echo htmlspecialchars($data['kelas'] ?? ''); ?>" <?php echo $can_edit?'':'disabled'; ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control" value="<?php echo htmlspecialchars($data['tempat_lahir'] ?? ''); ?>" <?php echo $can_edit?'':'disabled'; ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo $data['tanggal_lahir'] ?? ''; ?>" <?php echo $can_edit?'':'disabled'; ?>>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold small">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2" <?php echo $can_edit?'':'disabled'; ?>><?php echo htmlspecialchars($data['alamat'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">No. HP / WhatsApp</label>
                        <input type="text" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($data['no_hp'] ?? ''); ?>" <?php echo $can_edit?'':'disabled'; ?>>
                    </div>
                    <?php if ($can_edit): ?>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Foto (Opsional)</label>
                        <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png" style="height:auto;padding:8px;">
                        <?php if ($data['foto']): ?><small class="text-muted">File saat ini: <?php echo $data['foto']; ?></small><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- DATA ORANG TUA (KRITERIA) -->
        <div class="col-12">
            <div class="card-custom">
                <h5 class="mb-3" style="color:var(--primary);font-weight:bold;"><i class="fa fa-users me-2"></i>Data Orang Tua / Wali (Kriteria Penilaian)</h5>
                <div class="row g-3">
                    <!-- C1: Pekerjaan -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Pekerjaan Orang Tua (C1) <span class="text-danger">*</span></label>
                        <select name="pekerjaan_ortu" class="form-select" required <?php echo $can_edit?'':'disabled'; ?>>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($sub_options[1] ?? [] as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt['nama_sub']); ?>"
                                    <?php echo ($data['pekerjaan_ortu'] ?? '') === $opt['nama_sub'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($opt['nama_sub']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- C2: Penghasilan -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Penghasilan Orang Tua (C2) <span class="text-danger">*</span></label>
                        <select name="penghasilan_ortu" class="form-select" required <?php echo $can_edit?'':'disabled'; ?>>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($sub_options[2] ?? [] as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt['nama_sub']); ?>"
                                    <?php echo ($data['penghasilan_ortu'] ?? '') === $opt['nama_sub'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($opt['nama_sub']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- C3: Tanggungan -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Jumlah Tanggungan (C3) <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_tanggungan" class="form-control" min="1" max="20" required
                               value="<?php echo $data['jumlah_tanggungan'] ?? ''; ?>" <?php echo $can_edit?'':'disabled'; ?>>
                    </div>
                    <!-- C4: Kartu Miskin -->
                    <div class="col-md-8">
                        <label class="form-label fw-bold small">Status Pemegang Kartu Kemiskinan (C4) <span class="text-danger">*</span></label>
                        <select name="status_kartu_miskin" class="form-select" required <?php echo $can_edit?'':'disabled'; ?>>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($sub_options[4] ?? [] as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt['nama_sub']); ?>"
                                    <?php echo ($data['status_kartu_miskin'] ?? '') === $opt['nama_sub'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($opt['nama_sub']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- DATA AKADEMIK -->
        <div class="col-12">
            <div class="card-custom">
                <h5 class="mb-3" style="color:var(--primary);font-weight:bold;"><i class="fa fa-graduation-cap me-2"></i>Data Akademik</h5>
                <div class="row g-3">
                    <!-- C5: Nilai -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Nilai Akhir Semester (C5) <span class="text-danger">*</span></label>
                        <input type="number" name="nilai_akhir_semester" class="form-control" min="0" max="100" step="0.1" required
                               value="<?php echo $data['nilai_akhir_semester'] ?? ''; ?>" <?php echo $can_edit?'':'disabled'; ?>>
                        <small class="text-muted">Skala 0 - 100</small>
                    </div>
                    <!-- C6: Hafalan -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Jumlah Hafalan Al-Qur'an (C6) <span class="text-danger">*</span></label>
                        <select name="hafalan_quran" class="form-select" required <?php echo $can_edit?'':'disabled'; ?>>
                            <option value="">-- Pilih --</option>
                            <option value="1" <?php echo (isset($data['hafalan_quran']) && intval($data['hafalan_quran']) <= 1) ? 'selected' : ''; ?>><= 1 Juz</option>
                            <option value="2" <?php echo (isset($data['hafalan_quran']) && intval($data['hafalan_quran']) == 2) ? 'selected' : ''; ?>>2 Juz</option>
                            <option value="3" <?php echo (isset($data['hafalan_quran']) && intval($data['hafalan_quran']) >= 3) ? 'selected' : ''; ?>>>= 3 Juz</option>
                        </select>
                        <small class="text-muted">Kategori hafalan Al-Qur'an (sub-kriteria C6)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- DOKUMEN PENDUKUNG -->
        <?php if ($can_edit): ?>
        <div class="col-12">
            <div class="card-custom">
                <h5 class="mb-3" style="color:var(--primary);font-weight:bold;"><i class="fa fa-paperclip me-2"></i>Dokumen Pendukung</h5>
                <p class="text-muted small mb-3">Format: PDF, JPG, PNG. Maks 5MB per file.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Kartu Keluarga (KK)</label>
                        <input type="file" name="kk" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="height:auto;padding:8px;">
                        <?php if (isset($docs['kk'])): ?><small class="text-success"><i class="fa fa-check"></i> <?php echo $docs['kk']; ?></small><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Sertifikat Hafalan Al-Qur'an</label>
                        <input type="file" name="sertifikat_hafalan" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="height:auto;padding:8px;">
                        <?php if (isset($docs['sertifikat_hafalan'])): ?><small class="text-success"><i class="fa fa-check"></i> <?php echo $docs['sertifikat_hafalan']; ?></small><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Kartu Bantuan (KIP/KKS/PKH)</label>
                        <input type="file" name="kartu_bantuan" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="height:auto;padding:8px;">
                        <?php if (isset($docs['kartu_bantuan'])): ?><small class="text-success"><i class="fa fa-check"></i> <?php echo $docs['kartu_bantuan']; ?></small><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Raport Terakhir</label>
                        <input type="file" name="raport" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="height:auto;padding:8px;">
                        <?php if (isset($docs['raport'])): ?><small class="text-success"><i class="fa fa-check"></i> <?php echo $docs['raport']; ?></small><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- BUTTONS -->
        <?php if ($can_edit): ?>
        <div class="col-12">
            <div class="card-custom">
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" name="action" value="save_draft" class="btn-batal">
                        <i class="fa fa-save me-1"></i> Simpan Draft
                    </button>
                    <button type="submit" name="action" value="submit" class="btn-simpan"
                            onclick="return confirm('Apakah Anda yakin ingin mengajukan pendaftaran? Setelah data diajukan dan diverifikasi admin, data tidak dapat diubah kembali.')">
                        <i class="fa fa-paper-plane me-1"></i> Ajukan Pendaftaran
                    </button>
                    <a href="dashboard.php" class="btn-batal">
                        <i class="fa fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="col-12">
            <a href="dashboard.php" class="btn-batal"><i class="fa fa-arrow-left me-1"></i> Kembali ke Dashboard</a>
        </div>
        <?php endif; ?>

    </div>
    </form>
</div>

<?php include '../layouts/footer.php'; ?>
</body>
</html>
