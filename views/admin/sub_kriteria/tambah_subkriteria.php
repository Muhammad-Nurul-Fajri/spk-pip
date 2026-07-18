<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');

$kriteria_list = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode_kriteria ASC");
$pesan = '';

$tab = $_GET['tab'] ?? ($_POST['tab'] ?? 'c6');
$pre_id_kriteria = intval($_GET['id_kriteria'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kriteria = intval($_POST['id_kriteria'] ?? 0);
    $nama_sub = trim($_POST['nama_sub'] ?? '');
    $nilai = intval($_POST['nilai'] ?? 0);
    
    if ($id_kriteria <= 0 || empty($nama_sub) || $nilai <= 0) {
        $pesan = 'Semua field wajib diisi!';
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO sub_kriteria (id_kriteria, nama_sub, nilai) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isi", $id_kriteria, $nama_sub, $nilai);
        if (mysqli_stmt_execute($stmt)) { 
            mysqli_stmt_close($stmt);
            header("Location: index_subkriteria.php?tab=" . urlencode($tab)); 
            exit; 
        } else {
            $pesan = 'Gagal menyimpan data sub kriteria.';
        }
        mysqli_stmt_close($stmt);
    }
}

$page_title = 'Tambah Sub Kriteria';
$active_menu = 'sub_kriteria';
$asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../layouts/head.php'; ?>
</head>
<body>
<?php include '../../layouts/sidebar_admin.php'; ?>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-plus"></i>
            <h4>Tambah Sub Kriteria</h4>
        </div>
    </div>
    
    <div class="card-custom">
        <?php if ($pesan): ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-circle me-2"></i><?php echo $pesan; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
            
            <div class="mb-3">
                <label class="form-label">Kriteria Penilaian</label>
                <select name="id_kriteria" class="form-select" required>
                    <option value="">-- Pilih Kriteria --</option>
                    <?php while($k = mysqli_fetch_assoc($kriteria_list)): ?>
                        <option value="<?php echo $k['id']; ?>" <?php echo ($k['id'] == $pre_id_kriteria) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($k['kode_kriteria'] . ' - ' . $k['nama_kriteria']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Nama Sub Kriteria</label>
                <input type="text" name="nama_sub" class="form-control" placeholder="Contoh: Sangat Mampu, Buruh, dsb." required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Nilai Bobot Sub Kriteria (Scale 1-5)</label>
                <input type="number" name="nilai" class="form-control" min="1" max="5" placeholder="Masukkan nilai 1 sampai 5" required>
                <small class="text-muted">Nilai 5 menunjukkan prioritas tertinggi untuk penerimaan bantuan.</small>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn-simpan"><i class="fa fa-save me-1"></i> Simpan</button>
                <a href="index_subkriteria.php?tab=<?php echo urlencode($tab); ?>" class="btn-batal"><i class="fa fa-arrow-left me-1"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>

<?php include '../../layouts/footer.php'; ?>
</body>
</html>