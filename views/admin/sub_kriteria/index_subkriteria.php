<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');

$sub_list = mysqli_query($koneksi, "SELECT sk.*, k.kode_kriteria, k.nama_kriteria 
                                    FROM sub_kriteria sk 
                                    JOIN kriteria k ON sk.id_kriteria=k.id 
                                    ORDER BY k.kode_kriteria ASC, sk.nilai DESC");

$data_by_kriteria = [];
while ($row = mysqli_fetch_assoc($sub_list)) {
    $data_by_kriteria[$row['id_kriteria']][] = $row;
}

$page_title = 'Data Sub Kriteria';
$active_menu = 'sub_kriteria';
$asset_depth = 3;

// Detect active tab from URL query params
$active_tab = $_GET['tab'] ?? 'c6';
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
            <i class="fa fa-layer-group"></i>
            <h4>Data Sub Kriteria</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div>
        </div>
    </div>

    <div class="card-custom">
        <h5 class="mb-4 text-secondary"><i class="fa fa-list me-2 text-success"></i>Konfigurasi Kriteria Penilaian</h5>
        
        <!-- Tab Navigation (Task 4) -->
        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="subKriteriaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo ($active_tab === 'c6') ? 'active' : ''; ?>" id="tab-c6" data-bs-toggle="tab" data-bs-target="#panel-c6" type="button" role="tab" aria-controls="panel-c6" aria-selected="<?php echo ($active_tab === 'c6') ? 'true' : 'false'; ?>">
                    <i class="fa fa-book me-1"></i> Hafalan Qur'an
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo ($active_tab === 'c5') ? 'active' : ''; ?>" id="tab-c5" data-bs-toggle="tab" data-bs-target="#panel-c5" type="button" role="tab" aria-controls="panel-c5" aria-selected="<?php echo ($active_tab === 'c5') ? 'true' : 'false'; ?>">
                    <i class="fa fa-graduation-cap me-1"></i> Nilai Akhir Semester
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo ($active_tab === 'c4') ? 'active' : ''; ?>" id="tab-c4" data-bs-toggle="tab" data-bs-target="#panel-c4" type="button" role="tab" aria-controls="panel-c4" aria-selected="<?php echo ($active_tab === 'c4') ? 'true' : 'false'; ?>">
                    <i class="fa fa-id-card me-1"></i> Pemegang Kartu Miskin
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo ($active_tab === 'c3') ? 'active' : ''; ?>" id="tab-c3" data-bs-toggle="tab" data-bs-target="#panel-c3" type="button" role="tab" aria-controls="panel-c3" aria-selected="<?php echo ($active_tab === 'c3') ? 'true' : 'false'; ?>">
                    <i class="fa fa-users me-1"></i> Tanggungan Orang Tua
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo ($active_tab === 'c2') ? 'active' : ''; ?>" id="tab-c2" data-bs-toggle="tab" data-bs-target="#panel-c2" type="button" role="tab" aria-controls="panel-c2" aria-selected="<?php echo ($active_tab === 'c2') ? 'true' : 'false'; ?>">
                    <i class="fa fa-wallet me-1"></i> Penghasilan Orang Tua
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo ($active_tab === 'c1') ? 'active' : ''; ?>" id="tab-c1" data-bs-toggle="tab" data-bs-target="#panel-c1" type="button" role="tab" aria-controls="panel-c1" aria-selected="<?php echo ($active_tab === 'c1') ? 'true' : 'false'; ?>">
                    <i class="fa fa-briefcase me-1"></i> Pekerjaan Orang Tua
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="subKriteriaTabContent">
            <!-- C6: Hafalan Quran (ID 6) -->
            <div class="tab-pane fade <?php echo ($active_tab === 'c6') ? 'show active' : ''; ?>" id="panel-c6" role="tabpanel" aria-labelledby="tab-c6">
                <?php render_kriteria_tab_table(6, $data_by_kriteria, 'c6'); ?>
            </div>
            
            <!-- C5: Nilai Akhir Semester (ID 5) -->
            <div class="tab-pane fade <?php echo ($active_tab === 'c5') ? 'show active' : ''; ?>" id="panel-c5" role="tabpanel" aria-labelledby="tab-c5">
                <?php render_kriteria_tab_table(5, $data_by_kriteria, 'c5'); ?>
            </div>
            
            <!-- C4: Pemegang Kartu Miskin (ID 4) -->
            <div class="tab-pane fade <?php echo ($active_tab === 'c4') ? 'show active' : ''; ?>" id="panel-c4" role="tabpanel" aria-labelledby="tab-c4">
                <?php render_kriteria_tab_table(4, $data_by_kriteria, 'c4'); ?>
            </div>
            
            <!-- C3: Tanggungan Orang Tua (ID 3) -->
            <div class="tab-pane fade <?php echo ($active_tab === 'c3') ? 'show active' : ''; ?>" id="panel-c3" role="tabpanel" aria-labelledby="tab-c3">
                <?php render_kriteria_tab_table(3, $data_by_kriteria, 'c3'); ?>
            </div>
            
            <!-- C2: Penghasilan Orang Tua (ID 2) -->
            <div class="tab-pane fade <?php echo ($active_tab === 'c2') ? 'show active' : ''; ?>" id="panel-c2" role="tabpanel" aria-labelledby="tab-c2">
                <?php render_kriteria_tab_table(2, $data_by_kriteria, 'c2'); ?>
            </div>
            
            <!-- C1: Pekerjaan Orang Tua (ID 1) -->
            <div class="tab-pane fade <?php echo ($active_tab === 'c1') ? 'show active' : ''; ?>" id="panel-c1" role="tabpanel" aria-labelledby="tab-c1">
                <?php render_kriteria_tab_table(1, $data_by_kriteria, 'c1'); ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../layouts/footer.php'; ?>
</body>
</html>

<?php
// Table rendering helper function
function render_kriteria_tab_table($kriteria_id, $data_by_kriteria, $kriteria_code) {
    $items = $data_by_kriteria[$kriteria_id] ?? [];
    $no = 1;
    ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div class="search-input-wrapper">
            <i class="fa fa-search"></i>
            <input type="text" id="search-<?php echo $kriteria_code; ?>" class="form-control" placeholder="Cari sub kriteria...">
        </div>
        <a href="tambah_subkriteria.php?id_kriteria=<?php echo $kriteria_id; ?>&tab=<?php echo $kriteria_code; ?>" class="btn-add">
            <i class="fa fa-plus me-1"></i> Tambah Sub Kriteria
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered" id="table-<?php echo $kriteria_code; ?>">
            <thead>
                <tr>
                    <th width="8%">No</th>
                    <th>Nama Sub Kriteria</th>
                    <th width="15%">Nilai</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td class="text-start"><strong><?php echo htmlspecialchars($item['nama_sub']); ?></strong></td>
                            <td><span class="badge-benefit"><?php echo $item['nilai']; ?></span></td>
                            <td>
                                <a href="edit_subkriteria.php?id=<?php echo $item['id']; ?>&tab=<?php echo $kriteria_code; ?>" class="btn-icon edit me-1" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a href="hapus_subkriteria.php?id=<?php echo $item['id']; ?>&tab=<?php echo $kriteria_code; ?>" class="btn-icon delete" title="Hapus" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus sub kriteria ini?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Belum ada data sub kriteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initTableSearch('search-<?php echo $kriteria_code; ?>', 'table-<?php echo $kriteria_code; ?>', 1);
        });
    </script>
    <?php
}
?>