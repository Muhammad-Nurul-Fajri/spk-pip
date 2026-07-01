<?php
session_start();
require_once '../../config/koneksi.php';
require_role('siswa');

// Get all announcements
$pengumuman_list = [];
$aq = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY created_at DESC");
while ($a = mysqli_fetch_assoc($aq)) $pengumuman_list[] = $a;

$page_title = 'Pengumuman';
$active_menu = 'pengumuman';
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
            <i class="fa fa-bullhorn"></i>
            <h4>Pengumuman</h4>
        </div>
        <div class="user-box">
            <div class="user-icon"><i class="fa fa-user"></i></div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? ''); ?></div>
        </div>
    </div>

    <?php if (empty($pengumuman_list)): ?>
        <div class="card-custom text-center py-5">
            <i class="fa fa-bullhorn text-muted mb-3" style="font-size:40px;"></i>
            <p class="text-muted">Belum ada pengumuman saat ini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($pengumuman_list as $p): ?>
        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                <h5 style="font-weight:bold;color:var(--text);margin:0;"><?php echo htmlspecialchars($p['judul']); ?></h5>
                <small class="text-muted"><i class="fa fa-calendar me-1"></i><?php echo date('d M Y, H:i', strtotime($p['created_at'])); ?></small>
            </div>
            <p style="font-size:14px;line-height:1.8;color:#555;"><?php echo nl2br(htmlspecialchars($p['isi'])); ?></p>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../layouts/footer.php'; ?>
</body>
</html>
