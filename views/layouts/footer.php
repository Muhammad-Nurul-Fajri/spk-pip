<?php
/**
 * Shared footer include — Bootstrap JS + app.js
 * Set $asset_depth before including (default 2 = views/role/)
 */
$js_bs_path  = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js';
$js_app_path = str_repeat('../', $asset_depth ?? 2) . 'public/assets/js/app.js';
?>
<script src="<?php echo $js_bs_path; ?>"></script>
<script src="<?php echo $js_app_path; ?>"></script>
