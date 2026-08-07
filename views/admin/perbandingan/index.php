<?php
session_start();
require_once '../../../config/koneksi.php';
require_once '../../../app/helpers/AhpWpHelper.php';
require_role('admin');

// Redirect admin away from hidden AHP comparison page
header("Location: ../dashboard.php");
exit;

$pesan_sukses = '';
$pesan_error  = '';

$kriteria = AhpWpHelper::getKriteria($koneksi);
$n = count($kriteria);

// Handle POST save comparison
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pair_values = $_POST['pair'] ?? [];
    
    if (!empty($pair_values)) {
        mysqli_begin_transaction($koneksi);
        try {
            mysqli_query($koneksi, "DELETE FROM ahp_perbandingan");
            
            $stmt = mysqli_prepare($koneksi, "INSERT INTO ahp_perbandingan (id_kriteria_1, id_kriteria_2, nilai) VALUES (?, ?, ?)");
            
            foreach ($pair_values as $k1 => $targets) {
                foreach ($targets as $k2 => $val) {
                    $val_num = floatval($val);
                    if ($k1 != $k2 && $val_num > 0) {
                        mysqli_stmt_bind_param($stmt, "iid", $k1, $k2, $val_num);
                        mysqli_stmt_execute($stmt);
                    }
                }
            }
            mysqli_stmt_close($stmt);
            
            // Auto run AHP calculation and save results
            $ahp_res = AhpWpHelper::processAHP($koneksi);
            AhpWpHelper::saveAhpResults($koneksi, $ahp_res);
            
            mysqli_commit($koneksi);
            
            if ($ahp_res['is_consistent']) {
                $_SESSION['ahp_success'] = 'Matriks perbandingan berhasil disimpan dan HASIL KONSISTEN (CR = ' . number_format($ahp_res['cr'], 4) . ' ≤ 0.10)!';
            } else {
                $_SESSION['ahp_warning'] = 'Matriks perbandingan berhasil disimpan, tetapi TIDAK KONSISTEN (CR = ' . number_format($ahp_res['cr'], 4) . ' > 0.10). Silakan perbaiki nilai perbandingan!';
            }
            
            header("Location: ../ahp/index.php");
            exit;
            
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $pesan_error = 'Gagal menyimpan perbandingan: ' . $e->getMessage();
        }
    }
}

// Get existing data
$raw_data = AhpWpHelper::getPairwiseData($koneksi);
$matrix = AhpWpHelper::buildPairwiseMatrix($kriteria, $raw_data);

$page_title = 'Perbandingan Berpasangan (AHP)';
$active_menu = 'perbandingan';
$asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layouts/head.php'; ?>
    <style>
        .saaty-scale-info {
            background: #e8f5e9;
            border-left: 4px solid var(--primary);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .saaty-scale-info table {
            margin-bottom: 0;
            font-size: 12px;
        }
        .matrix-table th, .matrix-table td {
            text-align: center;
            vertical-align: middle;
        }
        .matrix-table select {
            font-size: 13px;
            font-weight: 600;
        }
    </style>
</head>
<body>
<?php include '../../layouts/sidebar_admin.php'; ?>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-balance-scale"></i>
            <h4>Pairwise Comparison Matrix (AHP)</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div>
        </div>
    </div>

    <?php if ($pesan_error): ?>
        <div class="alert alert-danger" style="border-radius:10px;"><i class="fa fa-times-circle me-2"></i><?php echo $pesan_error; ?></div>
    <?php endif; ?>

    <!-- Saaty Scale Reference Card -->
    <div class="card-custom">
        <h5 class="fw-bold text-success mb-3"><i class="fa fa-info-circle me-2"></i>Skala Perbandingan Saaty (1 - 9)</h5>
        <div class="saaty-scale-info">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>1</strong></td><td>Sama penting (Equal Importance)</td></tr>
                        <tr><td><strong>3</strong></td><td>Sedikit lebih penting (Moderate Importance)</td></tr>
                        <tr><td><strong>5</strong></td><td>Jelas lebih penting (Strong Importance)</td></tr>
                        <tr><td><strong>7</strong></td><td>Sangat jelas lebih penting (Very Strong Importance)</td></tr>
                        <tr><td><strong>9</strong></td><td>Mutlak lebih penting (Extreme Importance)</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>2, 4, 6, 8</strong></td><td>Nilai-nilai antara (Intermediate values)</td></tr>
                        <tr><td><strong>Kebalikan (1/x)</strong></td><td>Jika C1 dibanding C2 nilainya X, maka C2 dibanding C1 nilainya 1/X</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <form action="" method="POST" id="formPairwise">
            <h6 class="fw-bold mb-3 text-muted">Matriks Perbandingan Berpasangan Kriteria:</h6>
            <div class="table-responsive">
                <table class="table table-bordered matrix-table">
                    <thead class="table-light">
                        <tr>
                            <th width="15%">Kriteria</th>
                            <?php foreach ($kriteria as $k): ?>
                                <th><?php echo htmlspecialchars($k['kode_kriteria']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $ids = array_column($kriteria, 'id');
                        foreach ($kriteria as $i_idx => $k1): 
                        ?>
                        <tr>
                            <td class="fw-bold bg-light text-start ps-3">
                                <?php echo htmlspecialchars($k1['kode_kriteria'] . ' - ' . $k1['nama_kriteria']); ?>
                            </td>
                            <?php foreach ($kriteria as $j_idx => $k2): ?>
                                <td>
                                    <?php if ($k1['id'] == $k2['id']): ?>
                                        <input type="text" class="form-control text-center bg-light fw-bold" value="1" readonly>
                                        <input type="hidden" name="pair[<?php echo $k1['id']; ?>][<?php echo $k2['id']; ?>]" value="1">
                                    <?php elseif ($i_idx < $j_idx): ?>
                                        <!-- Upper triangle: Select input -->
                                        <?php $current_val = $matrix[$k1['id']][$k2['id']] ?? 1; ?>
                                        <select name="pair[<?php echo $k1['id']; ?>][<?php echo $k2['id']; ?>]" 
                                                class="form-select form-select-sm pair-select" 
                                                data-row="<?php echo $k1['id']; ?>" 
                                                data-col="<?php echo $k2['id']; ?>">
                                            <option value="1" <?php echo ($current_val == 1) ? 'selected' : ''; ?>>1 - Sama penting</option>
                                            <option value="2" <?php echo (abs($current_val - 2) < 0.01) ? 'selected' : ''; ?>>2 - Antara 1 dan 3</option>
                                            <option value="3" <?php echo (abs($current_val - 3) < 0.01) ? 'selected' : ''; ?>>3 - Sedikit lebih penting</option>
                                            <option value="4" <?php echo (abs($current_val - 4) < 0.01) ? 'selected' : ''; ?>>4 - Antara 3 dan 5</option>
                                            <option value="5" <?php echo (abs($current_val - 5) < 0.01) ? 'selected' : ''; ?>>5 - Jelas lebih penting</option>
                                            <option value="6" <?php echo (abs($current_val - 6) < 0.01) ? 'selected' : ''; ?>>6 - Antara 5 dan 7</option>
                                            <option value="7" <?php echo (abs($current_val - 7) < 0.01) ? 'selected' : ''; ?>>7 - Sangat jelas penting</option>
                                            <option value="8" <?php echo (abs($current_val - 8) < 0.01) ? 'selected' : ''; ?>>8 - Antara 7 dan 9</option>
                                            <option value="9" <?php echo (abs($current_val - 9) < 0.01) ? 'selected' : ''; ?>>9 - Mutlak lebih penting</option>
                                            
                                            <!-- Reciprocal options if reciprocal was set -->
                                            <option value="0.5" <?php echo (abs($current_val - 0.5) < 0.01) ? 'selected' : ''; ?>>1/2 - Kebalikan 2</option>
                                            <option value="0.33333333333333" <?php echo (abs($current_val - 1/3) < 0.01) ? 'selected' : ''; ?>>1/3 - Kebalikan 3</option>
                                            <option value="0.25" <?php echo (abs($current_val - 0.25) < 0.01) ? 'selected' : ''; ?>>1/4 - Kebalikan 4</option>
                                            <option value="0.2" <?php echo (abs($current_val - 0.2) < 0.01) ? 'selected' : ''; ?>>1/5 - Kebalikan 5</option>
                                            <option value="0.16666666666667" <?php echo (abs($current_val - 1/6) < 0.01) ? 'selected' : ''; ?>>1/6 - Kebalikan 6</option>
                                            <option value="0.14285714285714" <?php echo (abs($current_val - 1/7) < 0.01) ? 'selected' : ''; ?>>1/7 - Kebalikan 7</option>
                                            <option value="0.125" <?php echo (abs($current_val - 0.125) < 0.01) ? 'selected' : ''; ?>>1/8 - Kebalikan 8</option>
                                            <option value="0.11111111111111" <?php echo (abs($current_val - 1/9) < 0.01) ? 'selected' : ''; ?>>1/9 - Kebalikan 9</option>
                                        </select>
                                    <?php else: ?>
                                        <!-- Lower triangle: Auto reciprocal display -->
                                        <?php $recip_val = $matrix[$k1['id']][$k2['id']] ?? 1; ?>
                                        <span class="badge bg-secondary recip-cell" id="recip_<?php echo $k1['id']; ?>_<?php echo $k2['id']; ?>">
                                            <?php echo (abs($recip_val - round($recip_val)) < 0.0001) ? round($recip_val) : number_format($recip_val, 4); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-success-custom px-4 py-2">
                    <i class="fa fa-calculator me-1"></i> Simpan &amp; Hitung AHP
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const pairSelects = document.querySelectorAll('.pair-select');
    
    pairSelects.forEach(select => {
        select.addEventListener('change', (e) => {
            const r = e.target.dataset.row;
            const c = e.target.dataset.col;
            const val = parseFloat(e.target.value);
            const recip = (val > 0) ? (1 / val) : 1;
            
            const recipCell = document.getElementById(`recip_${c}_${r}`);
            if (recipCell) {
                const displayVal = (Math.abs(recip - Math.round(recip)) < 0.0001) ? Math.round(recip) : recip.toFixed(4);
                recipCell.textContent = displayVal;
            }
        });
    });
});
</script>
</body>
</html>
