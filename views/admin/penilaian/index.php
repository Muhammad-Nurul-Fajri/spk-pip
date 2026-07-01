<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');

$pesan_sukses = '';
$pesan_error  = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_siswa = intval($_POST['id_siswa'] ?? 0);
    $nilai    = $_POST['nilai'] ?? [];
    if ($id_siswa > 0 && !empty($nilai)) {
        $berhasil = true;
        foreach ($nilai as $id_kriteria => $val) {
            $id_kriteria = intval($id_kriteria);
            $val = floatval($val);
            $cek = mysqli_prepare($koneksi, "SELECT id FROM penilaian WHERE id_siswa=? AND id_kriteria=?");
            mysqli_stmt_bind_param($cek, "ii", $id_siswa, $id_kriteria);
            mysqli_stmt_execute($cek);
            if (mysqli_num_rows(mysqli_stmt_get_result($cek)) > 0) {
                $u = mysqli_prepare($koneksi, "UPDATE penilaian SET nilai=? WHERE id_siswa=? AND id_kriteria=?");
                mysqli_stmt_bind_param($u, "dii", $val, $id_siswa, $id_kriteria);
                if (!mysqli_stmt_execute($u)) $berhasil = false;
                mysqli_stmt_close($u);
            } else {
                $ins = mysqli_prepare($koneksi, "INSERT INTO penilaian (id_siswa, id_kriteria, nilai) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($ins, "iid", $id_siswa, $id_kriteria, $val);
                if (!mysqli_stmt_execute($ins)) $berhasil = false;
                mysqli_stmt_close($ins);
            }
            mysqli_stmt_close($cek);
        }
        $pesan_sukses = $berhasil ? 'Penilaian berhasil disimpan!' : 'Beberapa penilaian gagal.';
    }
}

$siswa_list = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY kode_alternatif ASC");
$jumlah_siswa = mysqli_num_rows($siswa_list);
$kriteria_list = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode_kriteria ASC");
$kriteria_arr = [];
while ($k = mysqli_fetch_assoc($kriteria_list)) $kriteria_arr[] = $k;
$jumlah_kriteria = count($kriteria_arr);

$sub_kriteria_map = [];
$sub_query = mysqli_query($koneksi, "SELECT * FROM sub_kriteria ORDER BY id_kriteria, nilai DESC");
while ($sk = mysqli_fetch_assoc($sub_query)) $sub_kriteria_map[$sk['id_kriteria']][] = $sk;

$penilaian_map = [];
$pen_query = mysqli_query($koneksi, "SELECT * FROM penilaian");
while ($p = mysqli_fetch_assoc($pen_query)) $penilaian_map[$p['id_siswa']][$p['id_kriteria']] = $p['nilai'];

$page_title = 'Data Penilaian';
$active_menu = 'penilaian';
$asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include '../../layouts/head.php'; ?>
</head>
<body>
<?php include '../../layouts/sidebar_admin.php'; ?>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-check-circle"></i>
            <h4>Data Penilaian</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div>
        </div>
    </div>

    <?php if ($pesan_sukses): ?>
        <div class="alert alert-success" style="border-radius:10px;"><i class="fa fa-check-circle me-2"></i><?php echo $pesan_sukses; ?></div>
    <?php endif; ?>

    <div class="card-custom">
        <h5 class="mb-3 text-muted">Matriks Penilaian Alternatif</h5>
        <?php if ($jumlah_siswa > 0 && $jumlah_kriteria > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="8%">Kode</th>
                        <th>Nama Siswa</th>
                        <?php foreach ($kriteria_arr as $kr): ?>
                            <th><?php echo htmlspecialchars($kr['kode_kriteria']); ?></th>
                        <?php endforeach; ?>
                        <th width="8%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($siswa = mysqli_fetch_assoc($siswa_list)): $id_s = $siswa['id']; ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($siswa['kode_alternatif']); ?></td>
                        <td style="text-align:left;padding-left:15px;"><?php echo htmlspecialchars($siswa['nama']); ?></td>
                        <?php foreach ($kriteria_arr as $kr): ?>
                            <td><span class="badge-benefit"><?php echo isset($penilaian_map[$id_s][$kr['id']]) ? $penilaian_map[$id_s][$kr['id']] : '-'; ?></span></td>
                        <?php endforeach; ?>
                        <td>
                            <button class="btn-icon edit" data-bs-toggle="modal" data-bs-target="#modalNilai<?php echo $id_s; ?>" title="Edit"><i class="fa fa-edit"></i></button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted"><p>Pastikan data alternatif dan kriteria tersedia.</p></div>
        <?php endif; ?>
    </div>
</div>

<?php
mysqli_data_seek($siswa_list, 0);
while ($siswa = mysqli_fetch_assoc($siswa_list)): $id_s = $siswa['id'];
?>
<div class="modal fade" id="modalNilai<?php echo $id_s; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--primary),var(--primary-light));color:white;">
                <h5 class="modal-title"><i class="fa fa-edit me-2"></i><?php echo htmlspecialchars($siswa['nama']); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php" method="POST">
                <input type="hidden" name="id_siswa" value="<?php echo $id_s; ?>">
                <div class="modal-body">
                    <?php foreach ($kriteria_arr as $kr):
                        $current_val = $penilaian_map[$id_s][$kr['id']] ?? 1;
                        $has_sub = isset($sub_kriteria_map[$kr['id']]) && count($sub_kriteria_map[$kr['id']]) > 0;
                    ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold small"><?php echo htmlspecialchars($kr['kode_kriteria'].' - '.$kr['nama_kriteria']); ?> <span class="text-muted fw-normal">(<?php echo $kr['jenis']; ?>)</span></label>
                        <?php if ($has_sub): ?>
                        <select name="nilai[<?php echo $kr['id']; ?>]" class="form-select">
                            <?php foreach ($sub_kriteria_map[$kr['id']] as $sub): ?>
                            <option value="<?php echo $sub['nilai']; ?>" <?php echo ($current_val==$sub['nilai'])?'selected':''; ?>><?php echo htmlspecialchars($sub['nama_sub']); ?> (<?php echo $sub['nilai']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <?php else: ?>
                        <input type="number" name="nilai[<?php echo $kr['id']; ?>]" class="form-control" value="<?php echo $current_val; ?>" min="1" max="5">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:8px;">Batal</button>
                    <button type="submit" class="btn btn-success" style="border-radius:8px;"><i class="fa fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endwhile; ?>

<?php include '../../layouts/footer.php'; ?>
</body>
</html>
