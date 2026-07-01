<?php
/**
 * Shared <head> include.
 * Usage: set $page_title before including this file.
 */
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($page_title ?? 'SPK PIP'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?php
// Determine depth to public/assets/css/style.css based on $asset_depth
$asset_path = str_repeat('../', $asset_depth ?? 2) . 'public/assets/css/style.css';
$js_path    = str_repeat('../', $asset_depth ?? 2) . 'public/assets/js/app.js';
?>
<link rel="stylesheet" href="<?php echo $asset_path; ?>">
