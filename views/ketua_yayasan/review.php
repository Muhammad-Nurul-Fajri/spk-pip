<?php
session_start();
require_once '../../config/koneksi.php';
require_role('ketua_yayasan');

$id_siswa = intval($_GET['id'] ?? 0);

// Get student details
$stmt = mysqli_prepare($koneksi, "SELECT s.*, h.nilai_s, h.nilai_v, h.ranking, h.status_verifikasi 
                                 FROM siswa s 
                                 LEFT JOIN hasil_wp h ON s.id = h.id_siswa 
                                 WHERE s.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id_siswa);
mysqli_stmt_execute($stmt);
$siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$siswa) {
    header("Location: laporan.php");
    exit;
}

// Get student assessments/criteria ratings
$penilaian = [];
$pq = mysqli_prepare($koneksi, "SELECT p.nilai, k.kode_kriteria, k.nama_kriteria, k.jenis 
                               FROM penilaian p 
                               JOIN kriteria k ON p.id_kriteria = k.id 
                               WHERE p.id_siswa = ? 
                               ORDER BY k.kode_kriteria");
mysqli_stmt_bind_param($pq, "i", $id_siswa);
mysqli_stmt_execute($pq);
$pr = mysqli_stmt_get_result($pq);
while ($row = mysqli_fetch_assoc($pr)) {
    $penilaian[] = $row;
}
mysqli_stmt_close($pq);

// Handle verification form submission
$pesan_sukses = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status_dec = $_POST['status_verifikasi'] ?? '';
    
    if (in_array($status_dec, ['layak', 'tidak_layak'])) {
        // Start transaction for consistency
        mysqli_begin_transaction($koneksi);
        
        try {
            // Update hasil_wp
            $stmt_wp = mysqli_prepare($koneksi, "UPDATE hasil_wp SET status_verifikasi = ? WHERE id_siswa = ?");
            mysqli_stmt_bind_param($stmt_wp, "si", $status_dec, $id_siswa);
            mysqli_stmt_execute($stmt_wp);
            mysqli_stmt_close($stmt_wp);
            
            // Synchronize student status
            $new_siswa_status = ($status_dec === 'layak') ? 'accepted' : 'rejected';
            $stmt_s = mysqli_prepare($koneksi, "UPDATE siswa SET status_pendaftaran = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt_s, "si", $new_siswa_status, $id_siswa);
            mysqli_stmt_execute($stmt_s);
            mysqli_stmt_close($stmt_s);
            
            mysqli_commit($koneksi);
            $_SESSION['verifikasi_success'] = "Status kelayakan untuk " . htmlspecialchars($siswa['nama']) . " berhasil diubah menjadi: " . ($status_dec === 'layak' ? 'LAYAK' : 'TIDAK LAYAK');
            
            header("Location: laporan.php");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $pesan_error = "Terjadi kesalahan database: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Harap pilih status kelayakan yang valid.";
    }
}

$page_title = 'Review Rekomendasi Seleksi PIP';
$active_menu = 'laporan';
$asset_depth = 2;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../layouts/head.php'; ?>
</head>
<body>
<?php include '../layouts/sidebar_ketua.php'; ?>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-clipboard-check"></i>
            <h4>Review Rekomendasi PIP</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Ketua Yayasan'); ?></div>
        </div>
    </div>

    <?php if ($pesan_error): ?>
        <div class="alert alert-danger" style="border-radius:10px;">
            <i class="fa fa-times-circle me-2"></i><?php echo $pesan_error; ?>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <!-- Student Detail Info Card -->
        <div class="col-lg-6">
            <div class="card-custom h-100">
                <h5 class="mb-4 text-success" style="font-weight: 700;">
                    <i class="fa fa-user-graduate me-2"></i>Profil Lengkap Siswa
                </h5>
                <table class="table table-borderless" style="font-size: 13.5px; text-align: left;">
                    <tr>
                        <td width="35%" class="text-muted">Nama Lengkap</td>
                        <td>: <strong><?php echo htmlspecialchars($siswa['nama']); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">NIS / NISN</td>
                        <td>: <?php echo htmlspecialchars($siswa['nis'] ?: '-'); ?> / <?php echo htmlspecialchars($siswa['nisn'] ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kelas</td>
                        <td>: <?php echo htmlspecialchars($siswa['kelas'] ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tempat / Tanggal Lahir</td>
                        <td>: <?php echo htmlspecialchars($siswa['tempat_lahir'] ?: '-'); ?>, <?php echo $siswa['tanggal_lahir'] ? date('d F Y', strtotime($siswa['tanggal_lahir'])) : '-'; ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Alamat Rumah</td>
                        <td>: <?php echo htmlspecialchars($siswa['alamat'] ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. HP / WhatsApp</td>
                        <td>: <?php echo htmlspecialchars($siswa['no_hp'] ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Pekerjaan Orang Tua</td>
                        <td>: <?php echo htmlspecialchars($siswa['pekerjaan_ortu'] ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Penghasilan Orang Tua</td>
                        <td>: <?php echo htmlspecialchars($siswa['penghasilan_ortu'] ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jumlah Tanggungan</td>
                        <td>: <?php echo $siswa['jumlah_tanggungan'] ?: '0'; ?> Orang</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Kartu Miskin</td>
                        <td>: <?php echo htmlspecialchars($siswa['status_kartu_miskin'] ?: 'Tidak Ada'); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Calculation Matrix Results -->
        <div class="col-lg-6">
            <div class="card-custom h-100 d-flex flex-column justify-content-between">
                <div>
                    <h5 class="mb-4 text-success" style="font-weight: 700;">
                        <i class="fa fa-calculator me-2"></i>Hasil Perhitungan & Ranking
                    </h5>
                    
                    <!-- Ranking Summary -->
                    <div class="d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 bg-light">
                        <div>
                            <p class="text-muted mb-1 small">Ranking Seleksi</p>
                            <h3 class="fw-bold mb-0 text-success">
                                <i class="fa fa-trophy text-warning me-1"></i> #<?php echo $siswa['ranking'] ?: '-'; ?>
                            </h3>
                        </div>
                        <div class="text-end">
                            <p class="text-muted mb-1 small">Nilai Preferensi (V)</p>
                            <h4 class="fw-bold mb-0 text-dark"><?php echo number_format($siswa['nilai_v'], 5); ?></h4>
                        </div>
                        <div class="text-end">
                            <p class="text-muted mb-1 small">Nilai Vektor S</p>
                            <h5 class="mb-0 text-secondary"><?php echo number_format($siswa['nilai_s'], 5); ?></h5>
                        </div>
                    </div>

                    <!-- Criteria Score Table -->
                    <h6 class="fw-bold mb-2 small text-muted text-uppercase">Skor Matriks Kriteria Penilaian:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Kriteria</th>
                                    <th>Nilai Input</th>
                                    <th>Skor Matriks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $input_vals = [
                                    'C1' => $siswa['pekerjaan_ortu'],
                                    'C2' => $siswa['penghasilan_ortu'],
                                    'C3' => $siswa['jumlah_tanggungan'] . ' Orang',
                                    'C4' => $siswa['status_kartu_miskin'],
                                    'C5' => $siswa['nilai_akhir_semester'],
                                    'C6' => $siswa['hafalan_quran'] . ' Juz',
                                ];
                                foreach ($penilaian as $pn): 
                                ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?php echo $pn['kode_kriteria']; ?></span></td>
                                        <td class="text-start"><?php echo htmlspecialchars($pn['nama_kriteria']); ?></td>
                                        <td><?php echo htmlspecialchars($input_vals[$pn['kode_kriteria']] ?? '-'); ?></td>
                                        <td><span class="badge-benefit"><?php echo $pn['nilai']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Verification Action Form -->
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa fa-signature me-1 text-success"></i>Tentukan Kelayakan Penerima PIP:</h6>
                    <form action="" method="POST" id="formReview">
                        <div class="d-flex gap-4 mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_verifikasi" id="statusLayak" value="layak" 
                                    <?php echo ($siswa['status_verifikasi'] === 'layak') ? 'checked' : ''; ?> required>
                                <label class="form-check-label fw-bold text-success" for="statusLayak">
                                    <i class="bi bi-check-circle-fill me-1"></i> Layak Menerima Bantuan
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_verifikasi" id="statusTidakLayak" value="tidak_layak"
                                    <?php echo ($siswa['status_verifikasi'] === 'tidak_layak') ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-bold text-danger" for="statusTidakLayak">
                                    <i class="bi bi-x-circle-fill me-1"></i> Tidak Layak Menerima
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success-custom flex-grow-1" id="btnConfirmSave">
                                <i class="fa fa-save me-1"></i> Simpan Keputusan
                            </button>
                            <a href="laporan.php" class="btn btn-secondary-custom">
                                <i class="fa fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal (Task 3) -->
<div class="modal fade" id="modalConfirm" tabindex="-1" aria-labelledby="modalConfirmLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalConfirmLabel"><i class="fa fa-question-circle me-2"></i>Konfirmasi Keputusan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-exclamation-triangle-fill text-warning mb-3" style="font-size: 50px;"></i>
                <h5 class="fw-bold">Apakah Anda yakin dengan keputusan ini?</h5>
                <p class="text-muted mb-0 small">Keputusan akan segera disimpan dan status pendaftaran siswa akan langsung diperbarui.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnActualSubmit">Ya, Simpan</button>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnConfirmSave = document.getElementById('btnConfirmSave');
    const btnActualSubmit = document.getElementById('btnActualSubmit');
    const formReview = document.getElementById('formReview');
    const modalConfirm = new bootstrap.Modal(document.getElementById('modalConfirm'));

    btnConfirmSave.addEventListener('click', () => {
        // Validate form
        const statusSelected = document.querySelector('input[name="status_verifikasi"]:checked');
        if (!statusSelected) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Harap pilih salah satu status kelayakan terlebih dahulu!',
                confirmButtonColor: '#2e7d32'
            });
            return;
        }
        // Show confirmation modal
        modalConfirm.show();
    });

    btnActualSubmit.addEventListener('click', () => {
        modalConfirm.hide();
        formReview.submit();
    });
});
</script>
</body>
</html>
