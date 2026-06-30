<?php

require_once '../../../config/koneksi.php';

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil ID dari parameter URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validasi ID
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Cek apakah data ada
$cek = mysqli_query($koneksi, "SELECT nama_kriteria FROM kriteria WHERE id = $id");

if (!$cek) {
    die("Error query: " . mysqli_error($koneksi));
}

if (mysqli_num_rows($cek) == 0) {
    header('Location: index.php');
    exit;
}

// Hapus data
$hapus = mysqli_query($koneksi, "DELETE FROM kriteria WHERE id = $id");

if ($hapus) {
    header('Location: index.php');
    exit;
} else {
    $error = "Gagal menghapus data: " . mysqli_error($koneksi);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error - SPK PIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { 
            background: #f4f7f1; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
        }
        .error-box { 
            background: white; 
            padding: 40px; 
            border-radius: 18px; 
            text-align: center; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
            max-width: 400px;
        }
        .error-box i { 
            font-size: 60px; 
            color: #dc3545; 
            margin-bottom: 20px; 
        }
        .error-box h4 { 
            color: #333; 
            margin-bottom: 15px; 
        }
        .error-box p { 
            color: #777; 
            margin-bottom: 25px; 
        }
        .btn-kembali { 
            background: #4caf50; 
            color: white; 
            padding: 10px 25px; 
            border-radius: 10px; 
            text-decoration: none; 
            display: inline-block;
        }
        .btn-kembali:hover {
            background: #43a047;
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-box">
        <i class="fa fa-exclamation-triangle"></i>
        <h4>Gagal Menghapus Data</h4>
        <p><?php echo htmlspecialchars($error); ?></p>
        <a href="index.php" class="btn-kembali">Kembali</a>
    </div>
</body>
</html>