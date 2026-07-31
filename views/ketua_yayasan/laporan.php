<?php
session_start();
require_once '../../config/koneksi.php';
require_role('ketua_yayasan');

$ranking_data = [];
$rq = mysqli_query($koneksi, "SELECT h.*, s.nama, s.kode_alternatif, s.kelas, s.nis FROM hasil_wp h JOIN siswa s ON h.id_siswa=s.id ORDER BY h.ranking ASC");
while ($r = mysqli_fetch_assoc($rq)) $ranking_data[] = $r;

$page_title = 'Laporan Hasil PIP';
$active_menu = 'laporan';
$asset_depth = 2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include '../layouts/head.php'; ?>
<style>
@media print {
    .print-header { display: block !important; }
    body { background: white; }
}
.print-header { display: none; text-align: center; margin-bottom: 20px; border-bottom: 3px double #333; padding-bottom: 15px; }
.print-header h3 { font-size: 16px; margin: 0; }
.print-header p { font-size: 12px; margin: 2px 0; color: #555; }
</style>
</head>
<body>
<?php include '../layouts/sidebar_ketua.php'; ?>

<div class="content">
    <div class="navbar-custom no-print">
        <div class="page-title">
            <i class="fa fa-file-lines"></i>
            <h4>Laporan Hasil Seleksi PIP</h4>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <form action="cetak_pdf.php" method="GET" target="_blank" class="d-flex align-items-center gap-2">
                <select name="filter" class="form-select" style="width: auto; height: 42px;">
                    <option value="semua">Semua Status</option>
                    <option value="layak">Layak</option>
                    <option value="tidak_layak">Tidak Layak</option>
                </select>
                <button type="submit" class="btn-add"><i class="fa fa-file-pdf me-1"></i> Cetak PDF</button>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['verifikasi_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: <?php echo json_encode($_SESSION['verifikasi_success']); ?>,
                    confirmButtonColor: '#2e7d32'
                });
            });
        </script>
        <?php unset($_SESSION['verifikasi_success']); ?>
    <?php endif; ?>

    <div class="print-header">
        <h3>PONDOK PESANTREN H. MAQBUL HASIBUAN</h3>
        <p>Sibuhuan, Padang Lawas — Sumatera Utara</p>
        <h3 style="margin-top:10px;">LAPORAN HASIL SELEKSI PENERIMA BANTUAN PIP</h3>
        <p>Tahun Ajaran 2025-2026 — Metode AHP &amp; Weighted Product (WP)</p>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Ranking</th>
                        <th>Kode</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Nilai S</th>
                        <th>Nilai V</th>
                        <th>Status Kelayakan</th>
                        <th class="no-print" width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranking_data as $row): ?>
                    <tr>
                        <td><?php echo $row['ranking']; ?></td>
                        <td><?php echo htmlspecialchars($row['kode_alternatif']); ?></td>
                        <td><?php echo htmlspecialchars($row['nis'] ?? '-'); ?></td>
                        <td style="text-align:left;padding-left:12px;"><strong><?php echo htmlspecialchars($row['nama']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                        <td><?php echo number_format($row['nilai_s'], 5); ?></td>
                        <td><?php echo number_format($row['nilai_v'], 5); ?></td>
                        <td>
                            <?php
                            if ($row['status_verifikasi'] === 'menunggu_penilaian') {
                                echo '<span class="badge-menunggu"><i class="bi bi-hourglass-split me-1"></i>Menunggu</span>';
                            } elseif ($row['status_verifikasi'] === 'layak') {
                                echo '<span class="badge-layak"><i class="bi bi-check-circle-fill me-1"></i>Layak</span>';
                            } else {
                                echo '<span class="badge-tidak-layak"><i class="bi bi-x-circle-fill me-1"></i>Tidak Layak</span>';
                            }
                            ?>
                        </td>
                        <td class="no-print">
                            <a href="review.php?id=<?php echo $row['id_siswa']; ?>" class="btn btn-sm btn-info-custom py-1 px-2" style="font-size: 11px; min-height: auto;">
                                <i class="fa fa-clipboard-check"></i> Review
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row mt-4" style="font-size:13px;">
            <div class="col-md-6">
                <p class="text-muted">Dicetak pada: <?php echo date('d F Y, H:i'); ?> WIB</p>
            </div>
            <div class="col-md-6 text-end">
                <p style="margin-bottom:60px;">Sibuhuan, <?php echo date('d F Y'); ?></p>
                <p style="font-weight:bold;">Ketua Yayasan</p>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>
</body>
</html>
