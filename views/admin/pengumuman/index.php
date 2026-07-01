<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');

// Handle delete
if (isset($_GET['hapus'])) {
    $hid = intval($_GET['hapus']);
    $stmt = mysqli_prepare($koneksi, "DELETE FROM pengumuman WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $hid); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
    header("Location: index.php"); exit;
}

// Handle add/edit
$pesan = ''; $edit_data = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $st = mysqli_prepare($koneksi, "SELECT * FROM pengumuman WHERE id=?");
    mysqli_stmt_bind_param($st, "i", $eid); mysqli_stmt_execute($st);
    $edit_data = mysqli_fetch_assoc(mysqli_stmt_get_result($st)); mysqli_stmt_close($st);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $edit_id = intval($_POST['edit_id'] ?? 0);
    if (empty($judul)||empty($isi)) $pesan = 'Judul dan isi wajib diisi!';
    else {
        if ($edit_id > 0) {
            $st = mysqli_prepare($koneksi, "UPDATE pengumuman SET judul=?,isi=? WHERE id=?");
            mysqli_stmt_bind_param($st, "ssi", $judul, $isi, $edit_id);
        } else {
            $uid = $_SESSION['id_user'];
            $st = mysqli_prepare($koneksi, "INSERT INTO pengumuman (judul,isi,created_by) VALUES (?,?,?)");
            mysqli_stmt_bind_param($st, "ssi", $judul, $isi, $uid);
        }
        if (mysqli_stmt_execute($st)) { header("Location: index.php"); exit; }
        else $pesan = 'Gagal menyimpan.';
        mysqli_stmt_close($st);
    }
}

$list = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY created_at DESC");
$page_title = 'Pengumuman'; $active_menu = 'pengumuman'; $asset_depth = 3;
?>
<!DOCTYPE html>
<html lang="en"><head><?php include '../../layouts/head.php'; ?></head>
<body><?php include '../../layouts/sidebar_admin.php'; ?>
<div class="content">
    <div class="navbar-custom">
        <div class="page-title"><i class="fa fa-bullhorn"></i><h4>Pengumuman</h4></div>
        <div class="user-box"><div class="user-icon"><i class="fa fa-user"></i></div><div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div></div>
    </div>

    <?php if ($pesan): ?><div class="alert alert-danger" style="border-radius:10px;"><?php echo $pesan; ?></div><?php endif; ?>

    <!-- FORM -->
    <div class="card-custom mb-3">
        <h6 style="font-weight:bold;margin-bottom:12px;"><i class="fa fa-<?php echo $edit_data?'edit':'plus'; ?> me-2" style="color:var(--primary);"></i><?php echo $edit_data?'Edit':'Tambah'; ?> Pengumuman</h6>
        <form method="POST">
            <input type="hidden" name="edit_id" value="<?php echo $edit_data['id'] ?? 0; ?>">
            <div class="mb-3"><label class="form-label fw-bold small">Judul</label><input type="text" name="judul" class="form-control" required value="<?php echo htmlspecialchars($edit_data['judul'] ?? ''); ?>"></div>
            <div class="mb-3"><label class="form-label fw-bold small">Isi Pengumuman</label><textarea name="isi" class="form-control" rows="4" required><?php echo htmlspecialchars($edit_data['isi'] ?? ''); ?></textarea></div>
            <button type="submit" class="btn-simpan"><i class="fa fa-save me-1"></i><?php echo $edit_data?'Update':'Simpan'; ?></button>
            <?php if ($edit_data): ?><a href="index.php" class="btn-batal ms-2">Batal</a><?php endif; ?>
        </form>
    </div>

    <!-- LIST -->
    <div class="card-custom">
        <h6 style="font-weight:bold;margin-bottom:12px;">Daftar Pengumuman</h6>
        <?php if (mysqli_num_rows($list) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($list)): ?>
            <div style="border-left:3px solid var(--primary);padding:12px 16px;margin-bottom:10px;background:#fafdf8;border-radius:0 10px 10px 0;">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h6 style="font-weight:bold;margin-bottom:4px;"><?php echo htmlspecialchars($row['judul']); ?></h6>
                        <p style="font-size:13px;color:#555;margin-bottom:4px;"><?php echo nl2br(htmlspecialchars($row['isi'])); ?></p>
                        <small class="text-muted"><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></small>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="index.php?edit=<?php echo $row['id']; ?>" class="btn-icon edit"><i class="fa fa-edit"></i></a>
                        <a href="index.php?hapus=<?php echo $row['id']; ?>" class="btn-icon delete" onclick="return confirm('Hapus?')"><i class="fa fa-trash"></i></a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted py-3">Belum ada pengumuman.</p>
        <?php endif; ?>
    </div>
</div>
<?php include '../../layouts/footer.php'; ?></body></html>
