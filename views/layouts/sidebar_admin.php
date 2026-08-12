<?php
/**
 * Shared Admin Sidebar.
 * Set $active_menu before including (e.g. 'dashboard', 'kriteria', 'alternatif', etc.)
 * Set $logo_path to the relative path to logo.png
 */
$logo_path   = $logo_path ?? '../../../public/assets/img/logo.png';
$base_admin  = $base_admin ?? '../';
$base_root   = $base_root ?? '../../../';
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
        <li><a href="<?php echo $base_admin; ?>dashboard.php" <?php echo ($active_menu=='dashboard')?'class="active"':''; ?>><i class="fa fa-house"></i> Dashboard</a></li>
        <li><a href="<?php echo $base_admin; ?>kriteria/index.php" <?php echo ($active_menu=='kriteria')?'class="active"':''; ?>><i class="fa fa-list"></i> Data Kriteria</a></li>
        <li><a href="<?php echo $base_admin; ?>sub_kriteria/index_subkriteria.php" <?php echo ($active_menu=='sub_kriteria')?'class="active"':''; ?>><i class="fa fa-layer-group"></i> Data Sub Kriteria</a></li>
        <li><a href="<?php echo $base_admin; ?>alternatif/index.php" <?php echo ($active_menu=='alternatif')?'class="active"':''; ?>><i class="fa fa-user-graduate"></i> Data Alternatif</a></li>
        <li><a href="<?php echo $base_admin; ?>penilaian/index.php" <?php echo ($active_menu=='penilaian')?'class="active"':''; ?>><i class="fa fa-check-circle"></i> Data Penilaian</a></li>
        <li><a href="<?php echo $base_admin; ?>perhitungan/index.php" <?php echo ($active_menu=='perhitungan')?'class="active"':''; ?>><i class="fa fa-calculator"></i> Perhitungan WP</a></li>
        <li><a href="<?php echo $base_admin; ?>hasil/index.php" <?php echo ($active_menu=='hasil')?'class="active"':''; ?>><i class="fa fa-trophy"></i> Data Hasil Akhir</a></li>
        <li><a href="<?php echo $base_admin; ?>user/index.php" <?php echo ($active_menu=='user')?'class="active"':''; ?>><i class="fa fa-users"></i> Data User</a></li>
        <li><a href="<?php echo $base_admin; ?>pengumuman/index.php" <?php echo ($active_menu=='pengumuman')?'class="active"':''; ?>><i class="fa fa-bullhorn"></i> Pengumuman</a></li>
        <li><a href="<?php echo $base_root; ?>logout.php"><i class="fa fa-right-from-bracket"></i> Logout</a></li>
    </ul>
    <div class="sidebar-footer text-center no-print" style="padding: 15px 10px; font-size: 11px; opacity: 0.75; border-top: 1px solid rgba(255,255,255,0.12); margin-top: auto; font-weight: 500; letter-spacing: 0.3px; line-height: 1.5; color: rgba(255,255,255,0.9);">
        Rina Sopiana Hasibuan<br>NIM: 2217020006
    </div>
</div>
