<?php
session_start();
require_once '../../../config/koneksi.php';
require_role('admin');

$filter = $_GET['filter'] ?? 'semua';

// Build SQL query based on filter
$sql = "SELECT h.*, s.nama, s.kode_alternatif, s.kelas, s.nis, s.nisn 
        FROM hasil_wp h 
        JOIN siswa s ON h.id_siswa = s.id";

if ($filter === 'layak') {
    $sql .= " WHERE h.status_verifikasi = 'layak'";
} elseif ($filter === 'tidak_layak') {
    $sql .= " WHERE h.status_verifikasi = 'tidak_layak'";
}

$sql .= " ORDER BY h.ranking ASC";
$query_ranking = mysqli_query($koneksi, $sql);
$jumlah_data = mysqli_num_rows($query_ranking);

$logo_path = '../../../public/assets/img/logo.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Seleksi PIP - <?php echo ucfirst($filter); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #fff;
            padding: 10px;
        }
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        .header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 4px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-logo {
            width: 80px;
            height: auto;
            margin-right: 20px;
        }
        .header-text {
            text-align: center;
        }
        .header-text h2 {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .header-text h3 {
            font-size: 16px;
            margin: 5px 0 0 0;
            text-transform: uppercase;
        }
        .header-text p {
            font-size: 12px;
            margin: 5px 0 0 0;
            font-style: italic;
        }
        .report-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title h4 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .report-title p {
            font-size: 13px;
            margin: 5px 0 0 0;
        }
        .table-custom {
            width: 100%;
            margin-bottom: 30px;
        }
        .table-custom th {
            background-color: #f2f2f2 !important;
            color: #000 !important;
            border: 1px solid #000 !important;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .table-custom td {
            border: 1px solid #000 !important;
            padding: 8px;
            font-size: 12px;
            text-align: center;
            background: transparent !important;
        }
        .table-custom tr:nth-child(even) td {
            background-color: transparent !important;
        }
        .signature-container {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            font-size: 13px;
            page-break-inside: avoid;
        }
        .signature-box {
            text-align: center;
            width: 250px;
        }
        .signature-box.right {
            margin-left: auto;
        }
        .signature-space {
            height: 75px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <!-- Print Trigger Bar -->
        <div class="row mb-3 no-print justify-content-end">
            <div class="col-auto">
                <button onclick="window.print()" class="btn btn-primary me-2"><i class="fa fa-print"></i> Cetak</button>
                <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
            </div>
        </div>

        <!-- Header -->
        <div class="header-container">
            <img src="<?php echo $logo_path; ?>" class="header-logo" alt="Logo Sekolah">
            <div class="header-text">
                <h2>Pondok Pesantren Haji Maqbul Hasibuan</h2>
                <h3>Sistem Pendukung Keputusan Penerima Bantuan PIP</h3>
                <p>Alamat: Sibuhuan, Kab. Padang Lawas, Sumatera Utara - Kode Pos: 22763</p>
            </div>
        </div>

        <!-- Title -->
        <div class="report-title">
            <h4>Laporan Hasil Seleksi Penerima Bantuan Program Indonesia Pintar (PIP)</h4>
            <p>Filter Status Kelayakan: <strong>
                <?php 
                if ($filter === 'semua') echo 'Semua Siswa';
                elseif ($filter === 'layak') echo 'Layak Menerima';
                else echo 'Tidak Layak Menerima';
                ?>
            </strong></p>
        </div>

        <!-- Table -->
        <table class="table table-custom">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="8%">Ranking</th>
                    <th width="10%">Kode</th>
                    <th width="12%">NIS / NISN</th>
                    <th>Nama Lengkap</th>
                    <th width="12%">Kelas</th>
                    <th width="12%">Nilai S</th>
                    <th width="12%">Nilai V</th>
                    <th width="15%">Status Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($jumlah_data > 0): ?>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($query_ranking)): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong><?php echo $row['ranking']; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['kode_alternatif']); ?></td>
                            <td><?php echo htmlspecialchars($row['nis']); ?> / <?php echo htmlspecialchars($row['nisn'] ?: '-'); ?></td>
                            <td class="text-start"><strong><?php echo htmlspecialchars($row['nama']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                            <td><?php echo number_format($row['nilai_s'], 5); ?></td>
                            <td><strong><?php echo number_format($row['nilai_v'], 5); ?></strong></td>
                            <td>
                                <?php
                                if ($row['status_verifikasi'] === 'menunggu_penilaian') {
                                    echo 'Menunggu Penilaian Ketua';
                                } elseif ($row['status_verifikasi'] === 'layak') {
                                    echo 'LAYAK';
                                } else {
                                    echo 'TIDAK LAYAK';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center py-4">Tidak ada data hasil seleksi.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Signature Area -->
        <div class="signature-container">
            <div class="signature-box">
                <p>Mengetahui,</p>
                <p class="fw-bold">Panitia Pelaksana PIP</p>
                <div class="signature-space"></div>
                <p class="fw-bold text-decoration-underline"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Administrator'); ?></p>
                <p class="text-muted" style="font-size: 11px; margin-top: -5px;">Staf Administrasi</p>
            </div>
            <div class="signature-box right">
                <p>Sibuhuan, <?php echo date('d F Y'); ?></p>
                <p class="fw-bold">Ketua Yayasan</p>
                <div class="signature-space"></div>
                <p class="fw-bold text-decoration-underline">H. Maqbul Hasibuan</p>
                <p class="text-muted" style="font-size: 11px; margin-top: -5px;">Ketua Yayasan PP Haji Maqbul</p>
            </div>
        </div>
    </div>

    <script>
        // Auto print window on load
        window.addEventListener('DOMContentLoaded', () => {
            // Check if loaded in iframe or directly
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
