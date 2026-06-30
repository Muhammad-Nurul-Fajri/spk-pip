<?php
require_once '../../../config/koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Handle form submission
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

            // Check if penilaian exists
            $cek = mysqli_query($koneksi, "SELECT id FROM penilaian WHERE id_siswa=$id_siswa AND id_kriteria=$id_kriteria");
            if (mysqli_num_rows($cek) > 0) {
                $q = mysqli_query($koneksi, "UPDATE penilaian SET nilai=$val WHERE id_siswa=$id_siswa AND id_kriteria=$id_kriteria");
            } else {
                $q = mysqli_query($koneksi, "INSERT INTO penilaian (id_siswa, id_kriteria, nilai) VALUES ($id_siswa, $id_kriteria, $val)");
            }
            if (!$q) $berhasil = false;
        }
        if ($berhasil) {
            $pesan_sukses = 'Penilaian berhasil disimpan!';
        } else {
            $pesan_error = 'Beberapa penilaian gagal disimpan.';
        }
    }
}

// Fetch all siswa
$siswa_list = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY kode_alternatif ASC");
$jumlah_siswa = mysqli_num_rows($siswa_list);

// Fetch all kriteria
$kriteria_list = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode_kriteria ASC");
$kriteria_arr = [];
while ($k = mysqli_fetch_assoc($kriteria_list)) {
    $kriteria_arr[] = $k;
}
$jumlah_kriteria = count($kriteria_arr);

// Fetch all sub_kriteria grouped by kriteria
$sub_kriteria_map = [];
$sub_query = mysqli_query($koneksi, "SELECT * FROM sub_kriteria ORDER BY id_kriteria, nilai DESC");
while ($sk = mysqli_fetch_assoc($sub_query)) {
    $sub_kriteria_map[$sk['id_kriteria']][] = $sk;
}

// Fetch existing penilaian as map
$penilaian_map = [];
$pen_query = mysqli_query($koneksi, "SELECT * FROM penilaian");
while ($p = mysqli_fetch_assoc($pen_query)) {
    $penilaian_map[$p['id_siswa']][$p['id_kriteria']] = $p['nilai'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Penilaian</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
body { background: #f4f7f1; overflow-x: hidden; }
.sidebar { width: 270px; height: 100vh; position: fixed; left: 0; top: 0; background: linear-gradient(180deg, #4caf50, #c6d166); color: white; overflow-y: auto; }
.sidebar::-webkit-scrollbar { width: 0px; }
.logo { padding: 25px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.15); }
.logo img { width: 95px; height: 95px; object-fit: contain; margin-bottom: 12px; }
.logo h4 { font-size: 15px; font-weight: bold; line-height: 1.6; margin-bottom: 8px; }
.logo p { font-size: 12px; margin: 0; opacity: 0.95; }
.menu { padding: 18px 0; margin: 0; }
.menu li { list-style: none; }
.menu li a { display: flex; align-items: center; gap: 12px; padding: 14px 24px; color: white; text-decoration: none; font-size: 14px; transition: 0.3s; }
.menu li a:hover, .menu li a.active { background: rgba(255,255,255,0.15); }
.menu li a i { width: 22px; text-align: center; }
.content { margin-left: 270px; padding: 22px; min-height: 100vh; }
.navbar-custom { background: white; padding: 16px 22px; border-radius: 18px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 25px; }
.page-title { display: flex; align-items: center; gap: 12px; }
.page-title i { font-size: 22px; color: #4caf50; }
.page-title h4 { margin: 0; font-size: 23px; font-weight: bold; color: #333; }
.user-box { display: flex; align-items: center; gap: 12px; }
.user-icon { width: 38px; height: 38px; border-radius: 50%; background: #e8f5e9; color: #4caf50; display: flex; justify-content: center; align-items: center; font-size: 16px; border: 1px solid #c8e6c9; }
.user-name { font-size: 14px; font-weight: bold; color: #555; }
.card-custom { background: white; border-radius: 18px; border: none; box-shadow: 0 8px 24px rgba(0,0,0,0.04); padding: 24px; margin-bottom: 25px; }
.table thead th { background: #4caf50; color: white; text-align: center; vertical-align: middle; font-size: 13px; }
.table tbody td { text-align: center; vertical-align: middle; background: white; font-size: 13px; }
.table tbody tr:hover td { background: #e8f5e9; }
.btn-edit-nilai { background: #ffc107; color: white; border: none; padding: 5px 12px; border-radius: 6px; font-size: 12px; transition: 0.3s; }
.btn-edit-nilai:hover { background: #e0a800; color: white; }
.badge-nilai { background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }

/* Modal styles */
.modal-header { background: linear-gradient(135deg, #4caf50, #c6d166); color: white; }
.modal-header .btn-close { filter: brightness(0) invert(1); }
.form-select { height: 38px; border-radius: 8px; border: 1px solid #d6e4d3; font-size: 13px; }
.form-select:focus { border-color: #C6D166; box-shadow: 0 0 0 0.15rem rgba(76,175,80,0.15); }

@media(max-width: 900px){ .sidebar { width: 100%; height: auto; position: relative; } .content { margin-left: 0; } }
</style>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="../../../public/assets/img/logo.png">
        <h4>Sistem Pendukung Keputusan Seleksi Penerima Bantuan PIP</h4>
        <p>Pondok Pesantren Haji Maqbul Hasibuan</p>
    </div>
    <ul class="menu">
        <li><a href="../dashboard.php"><i class="fa fa-house"></i> Dashboard</a></li>
        <li><a href="../kriteria/index.php"><i class="fa fa-list"></i> Data Kriteria</a></li>
        <li><a href="../sub_kriteria/index_subkriteria.php"><i class="fa fa-layer-group"></i> Data Sub Kriteria</a></li>
        <li><a href="../alternatif/index.php"><i class="fa fa-user-graduate"></i> Data Alternatif</a></li>
        <li><a href="index.php" class="active"><i class="fa fa-check-circle"></i> Data Penilaian</a></li>
        <li><a href="../perhitungan/index.php"><i class="fa fa-calculator"></i> Data Perhitungan</a></li>
        <li><a href="../hasil/index.php"><i class="fa fa-trophy"></i> Data Hasil Akhir</a></li>
        <li><a href="../user/index.php"><i class="fa fa-users"></i> Data User</a></li>
        <li><a href="#"><i class="fa fa-window-maximize"></i> Kelola Halaman</a></li>
        <li><a href="../../../logout.php"><i class="fa fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<div class="content">
    <div class="navbar-custom">
        <div class="page-title">
            <i class="fa fa-check-circle"></i>
            <h4>Data Penilaian</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name">Admin</div>
        </div>
    </div>

    <?php if (!empty($pesan_sukses)): ?>
        <div class="alert alert-success" style="border-radius: 10px;">
            <i class="fa fa-check-circle me-2"></i><?php echo $pesan_sukses; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($pesan_error)): ?>
        <div class="alert alert-danger" style="border-radius: 10px;">
            <?php echo $pesan_error; ?>
        </div>
    <?php endif; ?>

    <!-- TABEL PENILAIAN -->
    <div class="card-custom">
        <h5 class="mb-3 text-muted">Matriks Penilaian Alternatif</h5>

        <?php if ($jumlah_siswa > 0 && $jumlah_kriteria > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Kode</th>
                        <th>Nama Siswa</th>
                        <?php foreach ($kriteria_arr as $kr): ?>
                            <th><?php echo htmlspecialchars($kr['kode_kriteria']); ?></th>
                        <?php endforeach; ?>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($siswa = mysqli_fetch_assoc($siswa_list)):
                        $id_s = $siswa['id'];
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($siswa['kode_alternatif']); ?></td>
                        <td style="text-align: left; padding-left: 15px;"><?php echo htmlspecialchars($siswa['nama']); ?></td>
                        <?php foreach ($kriteria_arr as $kr): ?>
                            <td>
                                <span class="badge-nilai">
                                    <?php echo isset($penilaian_map[$id_s][$kr['id']]) ? $penilaian_map[$id_s][$kr['id']] : '-'; ?>
                                </span>
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <button class="btn-edit-nilai" data-bs-toggle="modal" data-bs-target="#modalNilai<?php echo $id_s; ?>">
                                <i class="fa fa-edit"></i> Edit
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fa fa-clipboard-list mb-3" style="font-size: 40px; color: #ddd;"></i>
                <p>Pastikan data alternatif dan kriteria sudah tersedia sebelum melakukan penilaian.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODALS -->
<?php
// Reset pointer
mysqli_data_seek($siswa_list, 0);
while ($siswa = mysqli_fetch_assoc($siswa_list)):
    $id_s = $siswa['id'];
?>
<div class="modal fade" id="modalNilai<?php echo $id_s; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-edit me-2"></i>Edit Penilaian — <?php echo htmlspecialchars($siswa['nama']); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php" method="POST">
                <input type="hidden" name="id_siswa" value="<?php echo $id_s; ?>">
                <div class="modal-body">
                    <?php foreach ($kriteria_arr as $kr):
                        $current_val = $penilaian_map[$id_s][$kr['id']] ?? 1;
                        $has_sub = isset($sub_kriteria_map[$kr['id']]) && count($sub_kriteria_map[$kr['id']]) > 0;
                    ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">
                                <?php echo htmlspecialchars($kr['kode_kriteria'] . ' - ' . $kr['nama_kriteria']); ?>
                                <span class="text-muted fw-normal">(<?php echo $kr['jenis']; ?>)</span>
                            </label>
                            <?php if ($has_sub): ?>
                                <select name="nilai[<?php echo $kr['id']; ?>]" class="form-select">
                                    <?php foreach ($sub_kriteria_map[$kr['id']] as $sub): ?>
                                        <option value="<?php echo $sub['nilai']; ?>"
                                            <?php echo ($current_val == $sub['nilai']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sub['nama_sub']); ?> (Nilai: <?php echo $sub['nilai']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="number" name="nilai[<?php echo $kr['id']; ?>]" class="form-control"
                                       value="<?php echo $current_val; ?>" min="1" max="5" step="1">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
                    <button type="submit" class="btn btn-success" style="border-radius: 8px;">
                        <i class="fa fa-save me-1"></i> Simpan Penilaian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endwhile; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
