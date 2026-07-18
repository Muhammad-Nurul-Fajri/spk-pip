<?php
session_start();
require_once '../../config/koneksi.php';
require_role('siswa');

$id_user = $_SESSION['id_user'];

// Get student profile
$stmt = mysqli_prepare($koneksi, "SELECT * FROM siswa WHERE id_user = ?");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Get penilaian values
$penilaian = [];
if ($siswa) {
    $pq = mysqli_prepare($koneksi, "SELECT p.nilai, k.kode_kriteria, k.nama_kriteria, k.jenis FROM penilaian p JOIN kriteria k ON p.id_kriteria=k.id WHERE p.id_siswa=? ORDER BY k.kode_kriteria");
    mysqli_stmt_bind_param($pq, "i", $siswa['id']);
    mysqli_stmt_execute($pq);
    $pr = mysqli_stmt_get_result($pq);
    while ($row = mysqli_fetch_assoc($pr)) $penilaian[] = $row;
    mysqli_stmt_close($pq);
}

// Get ranking result
$hasil = null;
if ($siswa) {
    $hq = mysqli_prepare($koneksi, "SELECT h.*, (SELECT COUNT(*) FROM hasil_wp) as total_peserta FROM hasil_wp h WHERE h.id_siswa = ?");
    mysqli_stmt_bind_param($hq, "i", $siswa['id']);
    mysqli_stmt_execute($hq);
    $hasil = mysqli_fetch_assoc(mysqli_stmt_get_result($hq));
    mysqli_stmt_close($hq);
}

// Get announcements (latest 5)
$pengumuman_list = [];
$aq = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY created_at DESC LIMIT 5");
while ($a = mysqli_fetch_assoc($aq)) $pengumuman_list[] = $a;

// Session success message
$success_msg = $_SESSION['pendaftaran_success'] ?? '';
unset($_SESSION['pendaftaran_success']);

// Status progression
$status = $siswa['status_pendaftaran'] ?? 'draft';
$status_map = [
    'draft' => ['label' => 'Draft', 'step' => 1, 'badge' => 'badge-draft'],
    'submitted' => ['label' => 'Diajukan', 'step' => 2, 'badge' => 'badge-submitted'],
    'verified' => ['label' => 'Terverifikasi', 'step' => 3, 'badge' => 'badge-verified'],
    'processed' => ['label' => 'Diproses Sistem', 'step' => 4, 'badge' => 'badge-processed'],
    'accepted' => ['label' => 'Lolos Seleksi', 'step' => 5, 'badge' => 'badge-accepted'],
    'rejected' => ['label' => 'Tidak Lolos', 'step' => 5, 'badge' => 'badge-rejected'],
];
$current = $status_map[$status] ?? $status_map['draft'];

$page_title = 'Dashboard Siswa';
$active_menu = 'dashboard';
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
            <i class="fa fa-house"></i>
            <h4>Dashboard Siswa</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? ''); ?></div>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: <?php echo json_encode($success_msg); ?>,
                    confirmButtonColor: '#2e7d32'
                });
            });
        </script>
    <?php endif; ?>

    <!-- STATUS & PROGRESS TRACKER -->
    <div class="card-custom">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
            <h5 style="font-weight:bold;color:var(--text);margin:0;">Status Pendaftaran</h5>
            <span class="badge-status <?php echo $current['badge']; ?>">
                <i class="fa fa-circle" style="font-size:8px;"></i> <?php echo $current['label']; ?>
            </span>
        </div>
        <div class="progress-tracker">
            <?php
            $steps = [
                ['icon' => '1', 'label' => 'Pendaftaran'],
                ['icon' => '2', 'label' => 'Lengkapi Data'],
                ['icon' => '3', 'label' => 'Verifikasi'],
                ['icon' => '4', 'label' => 'Proses WP'],
                ['icon' => '5', 'label' => 'Hasil'],
            ];
            foreach ($steps as $i => $s):
                $step_num = $i + 1;
                $class = '';
                if ($step_num < $current['step']) $class = 'done';
                elseif ($step_num == $current['step']) $class = 'active';
            ?>
            <div class="progress-step <?php echo $class; ?>">
                <div class="step-circle">
                    <?php if ($class === 'done'): ?><i class="fa fa-check"></i><?php else: echo $s['icon']; endif; ?>
                </div>
                <div class="step-label"><?php echo $s['label']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($status === 'draft'): ?>
            <div class="text-center mt-2">
                <a href="pendaftaran.php" class="btn-simpan" style="display:inline-flex;align-items:center;gap:6px;">
                    <i class="fa fa-file-pen"></i> Lengkapi Data Pendaftaran
                </a>
            </div>
        <?php elseif ($status === 'submitted'): ?>
            <div class="text-center mt-2">
                <p class="text-muted small mb-2">Data sudah diajukan. Anda masih dapat mengedit sebelum diverifikasi admin.</p>
                <a href="pendaftaran.php" class="btn-batal" style="display:inline-flex;align-items:center;gap:6px;">
                    <i class="fa fa-edit"></i> Edit Data
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-3">
        <!-- HASIL SELEKSI (if final) -->
        <?php if ($status === 'accepted' || $status === 'rejected'): ?>
        <div class="col-12">
            <div class="card-custom" style="border-left: 5px solid <?php echo ($status==='accepted')?'#4caf50':'#e53935'; ?>;">
                <div class="d-flex align-items-center gap-3">
                    <div style="font-size:40px;color:<?php echo ($status==='accepted')?'#4caf50':'#e53935'; ?>;">
                        <i class="fa <?php echo ($status==='accepted')?'fa-circle-check':'fa-circle-xmark'; ?>"></i>
                    </div>
                    <div>
                        <h5 style="margin:0;font-weight:bold;"><?php echo ($status==='accepted')?'Selamat! Anda Lolos Seleksi PIP':'Maaf, Anda Belum Lolos Seleksi PIP'; ?></h5>
                        <p class="text-muted mb-0 small"><?php echo ($status==='accepted')?'Anda telah ditetapkan sebagai penerima bantuan Program Indonesia Pintar.':'Berdasarkan hasil perhitungan metode Weighted Product, Anda belum memenuhi kriteria penerima PIP pada periode ini.'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- DATA SUMMARY -->
        <?php if ($siswa && $siswa['nis']): ?>
        <div class="col-lg-7">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="font-weight:bold;color:var(--text);margin:0;"><i class="fa fa-id-card me-2" style="color:var(--primary);"></i>Ringkasan Data</h5>
                    <?php if (in_array($status, ['draft','submitted'])): ?>
                        <a href="pendaftaran.php" class="btn-add" style="font-size:12px;padding:6px 12px;"><i class="fa fa-edit me-1"></i>Edit</a>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless" style="font-size:13px;">
                        <tr><td style="width:40%;color:#777;text-align:left;">Nama</td><td style="text-align:left;font-weight:bold;"><?php echo htmlspecialchars($siswa['nama']); ?></td></tr>
                        <tr><td style="color:#777;text-align:left;">NIS / NISN</td><td style="text-align:left;"><?php echo htmlspecialchars($siswa['nis']); ?> / <?php echo htmlspecialchars($siswa['nisn'] ?? '-'); ?></td></tr>
                        <tr><td style="color:#777;text-align:left;">Kelas</td><td style="text-align:left;"><?php echo htmlspecialchars($siswa['kelas']); ?></td></tr>
                        <tr><td style="color:#777;text-align:left;">Pekerjaan Ortu</td><td style="text-align:left;"><?php echo htmlspecialchars($siswa['pekerjaan_ortu'] ?? '-'); ?></td></tr>
                        <tr><td style="color:#777;text-align:left;">Penghasilan Ortu</td><td style="text-align:left;"><?php echo htmlspecialchars($siswa['penghasilan_ortu'] ?? '-'); ?></td></tr>
                        <tr><td style="color:#777;text-align:left;">Tanggungan</td><td style="text-align:left;"><?php echo $siswa['jumlah_tanggungan'] ?? '-'; ?> orang</td></tr>
                        <tr><td style="color:#777;text-align:left;">Kartu Kemiskinan</td><td style="text-align:left;"><?php echo htmlspecialchars($siswa['status_kartu_miskin'] ?? '-'); ?></td></tr>
                        <tr><td style="color:#777;text-align:left;">Nilai Akhir</td><td style="text-align:left;"><?php echo $siswa['nilai_akhir_semester'] ?? '-'; ?></td></tr>
                        <tr><td style="color:#777;text-align:left;">Hafalan Qur'an</td><td style="text-align:left;"><?php echo $siswa['hafalan_quran'] ?? 0; ?> Juz</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- PENGUMUMAN -->
        <div class="<?php echo ($siswa && $siswa['nis']) ? 'col-lg-5' : 'col-12'; ?>">
            <div class="card-custom">
                <h5 style="font-weight:bold;color:var(--text);margin-bottom:14px;"><i class="fa fa-bullhorn me-2" style="color:#ff9800;"></i>Pengumuman Terbaru</h5>
                <?php if (empty($pengumuman_list)): ?>
                    <p class="text-muted small text-center py-3">Belum ada pengumuman.</p>
                <?php else: ?>
                    <?php foreach ($pengumuman_list as $p): ?>
                    <div style="border-left:3px solid var(--primary);padding:10px 14px;margin-bottom:10px;background:#fafdf8;border-radius:0 8px 8px 0;">
                        <h6 style="font-size:13px;font-weight:bold;margin-bottom:3px;"><?php echo htmlspecialchars($p['judul']); ?></h6>
                        <p style="font-size:12px;color:#777;margin:0;"><?php echo mb_strimwidth(htmlspecialchars($p['isi']), 0, 120, '...'); ?></p>
                        <small class="text-muted"><?php echo date('d M Y', strtotime($p['created_at'])); ?></small>
                    </div>
                    <?php endforeach; ?>
                    <a href="pengumuman.php" class="d-block text-center mt-2" style="font-size:13px;color:var(--primary);font-weight:bold;">Lihat Semua →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>
</body>
</html>
