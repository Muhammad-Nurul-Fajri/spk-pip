<?php
/**
 * Shared Siswa Sidebar.
 * Set $active_menu before including (e.g. 'dashboard', 'pendaftaran', 'pengumuman')
 */
$logo_path  = $logo_path ?? '../../public/assets/img/logo.png';
$base_siswa = $base_siswa ?? '';
$base_root  = $base_root ?? '../../';
?>
<button class="sidebar-toggle" aria-label="Toggle Menu"><i class="fa fa-bars"></i></button>
<div class="sidebar-overlay"></div>

<div class="sidebar">
    <div class="logo">
        <img src="<?php echo $logo_path; ?>" alt="Logo">
        <h4>Sistem Pendukung Keputusan Seleksi Penerima Bantuan PIP</h4>
        <p>Pondok Pesantren H. Maqbul Hasibuan</p>
    </div>
    <ul class="menu">
        <li><a href="<?php echo $base_siswa; ?>dashboard.php" <?php echo ($active_menu=='dashboard')?'class="active"':''; ?>><i class="fa fa-house"></i> Dashboard</a></li>
        <li><a href="<?php echo $base_siswa; ?>pendaftaran.php" <?php echo ($active_menu=='pendaftaran')?'class="active"':''; ?>><i class="fa fa-file-pen"></i> Pendaftaran PIP</a></li>
        <li><a href="<?php echo $base_siswa; ?>pengumuman.php" <?php echo ($active_menu=='pengumuman')?'class="active"':''; ?>><i class="fa fa-bullhorn"></i> Pengumuman</a></li>
        <li><a href="<?php echo $base_root; ?>logout.php"><i class="fa fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>
