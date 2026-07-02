# 📚 SPK-PIP — Sistem Pendukung Keputusan Penerima PIP

> Sistem Pendukung Keputusan (SPK) untuk menentukan penerima bantuan **Program Indonesia Pintar (PIP)** menggunakan metode **Weighted Product (WP)**.

---

## 🎯 Tentang Aplikasi

SPK-PIP adalah aplikasi web berbasis PHP yang dirancang untuk membantu proses seleksi penerima bantuan PIP di lingkungan sekolah/yayasan. Aplikasi ini menggunakan metode **Weighted Product (WP)** untuk menghasilkan perankingan siswa berdasarkan kriteria yang telah ditentukan.

### Fitur Utama

| Fitur | Deskripsi |
|---|---|
| 🔐 **Multi-Role Authentication** | 3 level akses: Admin, Ketua Yayasan, Siswa |
| 📊 **Metode Weighted Product** | Perhitungan SPK otomatis dengan normalisasi bobot |
| 📝 **Pendaftaran Online** | Siswa dapat mendaftar & melengkapi data PIP secara mandiri |
| 📂 **Upload Dokumen** | Unggah berkas pendukung (KK, KTP Ortu, Raport, Kartu Bantuan) |
| 📢 **Pengumuman** | Sistem pengumuman dari admin untuk siswa |
| 📈 **Dashboard & Laporan** | Statistik, grafik, dan rekap penerima PIP per tahun ajaran |
| 🖨️ **Cetak Laporan** | Ketua Yayasan dapat mencetak hasil perankingan |

---

## 🛠️ Tech Stack

- **Backend:** PHP 8.x (native, tanpa framework)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML, CSS, JavaScript, Bootstrap 5.3
- **Library:** Chart.js (grafik dashboard), Font Awesome (ikon)
- **Server:** Laragon / XAMPP / PHP built-in server

---

## ⚙️ Instalasi

### Prasyarat

- PHP ≥ 8.0
- MySQL / MariaDB
- Laragon, XAMPP, atau PHP CLI

### Langkah Instalasi

1. **Clone repository** ke dalam direktori web server:

   ```bash
   cd C:\laragon\www
   git clone <repo-url> spk-pip
   ```

2. **Buat database** dan impor skema:

   ```sql
   -- Jalankan file SQL berikut secara berurutan:
   SOURCE database/spk_pip_wp.sql;
   SOURCE database/migration_v2.sql;
   ```

   Atau melalui terminal:

   ```bash
   mysql -u root < database/spk_pip_wp.sql
   mysql -u root spk_pip_wp < database/migration_v2.sql
   ```

3. **Konfigurasi database** — edit file `config/koneksi.php` jika diperlukan:

   ```php
   $koneksi = mysqli_connect("localhost", "root", "", "spk_pip_wp");
   ```

4. **Jalankan aplikasi:**

   - **Laragon:** Akses via `http://localhost/spk-pip`
   - **PHP built-in server:**

     ```bash
     cd spk-pip
     php -S localhost:8000
     ```

     Lalu buka `http://localhost:8000`

---

## 👥 Akun Default

Setelah menjalankan `migration_v2.sql`, tersedia 3 akun bawaan:

| Role | Username | Password | Akses |
|---|---|---|---|
| **Admin** | `admin` | `admin123` | Kelola seluruh data, kriteria, penilaian, perhitungan WP |
| **Ketua Yayasan** | `ketua` | `ketua123` | Lihat dashboard, hasil ranking, cetak laporan |
| **Siswa** | `siswa1` | `siswa123` | Dashboard, pendaftaran PIP, lihat pengumuman |

> ⚠️ Password disimpan dalam format **bcrypt hash**. Ubah password default setelah instalasi.

---

## 📁 Struktur Proyek

```
spk-pip/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php        # Login & autentikasi
│   │   └── PendaftaranController.php  # Proses pendaftaran siswa
│   ├── helpers                        # Fungsi helper
│   └── models                         # Model data
├── config/
│   └── koneksi.php                    # Koneksi DB & fungsi require_role()
├── database/
│   ├── spk_pip_wp.sql                 # Skema awal (5 kriteria)
│   └── migration_v2.sql              # Migrasi v2 (6 kriteria, bcrypt, tabel baru)
├── public/
│   ├── assets/
│   │   ├── css/                       # Stylesheet
│   │   ├── img/                       # Gambar & logo
│   │   └── js/
│   │       └── app.js                 # JavaScript utama
│   └── uploads/                       # Dokumen upload siswa
├── views/
│   ├── admin/
│   │   ├── dashboard.php              # Dashboard admin
│   │   ├── alternatif/                # CRUD data siswa/alternatif
│   │   ├── kriteria/                  # CRUD kriteria
│   │   ├── sub_kriteria/              # CRUD sub-kriteria
│   │   ├── penilaian/                 # Input penilaian siswa
│   │   ├── perhitungan/               # Proses & hasil WP
│   │   ├── hasil/                     # Hasil ranking
│   │   ├── pengumuman/                # Kelola pengumuman
│   │   └── user/                      # Manajemen user
│   ├── ketua_yayasan/
│   │   ├── dashboard.php              # Dashboard ketua
│   │   └── laporan.php                # Cetak laporan ranking
│   ├── siswa/
│   │   ├── dashboard.php              # Dashboard siswa
│   │   ├── pendaftaran.php            # Form pendaftaran PIP
│   │   └── pengumuman.php             # Lihat pengumuman
│   └── layouts/
│       ├── head.php                   # <head> meta & CSS
│       ├── sidebar_admin.php          # Sidebar navigasi admin
│       ├── sidebar_ketua.php          # Sidebar navigasi ketua
│       ├── sidebar_siswa.php          # Sidebar navigasi siswa
│       └── footer.php                 # Footer & script
├── index.php                          # Entry point (redirect by role)
├── login.php                          # Halaman login
├── logout.php                         # Proses logout
└── register.php                       # Registrasi akun siswa baru
```

---

## 📐 Metode Weighted Product (WP)

### Kriteria Penilaian (6 Kriteria)

| Kode | Kriteria | Bobot | Jenis |
|---|---|---|---|
| C1 | Pekerjaan Orang Tua | 20 | Cost |
| C2 | Penghasilan Orang Tua | 25 | Cost |
| C3 | Jumlah Tanggungan | 15 | Benefit |
| C4 | Status Pemegang Kartu Kemiskinan | 15 | Benefit |
| C5 | Nilai Akhir Semester | 15 | Benefit |
| C6 | Hafalan Al-Quran | 10 | Benefit |

### Langkah Perhitungan WP

1. **Normalisasi Bobot (Wj)**

   ```
   Wj = Bobot_j / Σ Bobot
   ```

   Total bobot = 100, sehingga W1 = 0.20, W2 = 0.25, dst.

2. **Hitung Vektor S**

   ```
   Si = Π (Xij ^ Wj)
   ```

   - Untuk kriteria **benefit**: pangkat positif (+Wj)
   - Untuk kriteria **cost**: pangkat negatif (−Wj)

3. **Hitung Vektor V (Nilai Preferensi)**

   ```
   Vi = Si / Σ Si
   ```

4. **Ranking** — Siswa diurutkan berdasarkan nilai V tertinggi.

---

## 🔒 Keamanan

- ✅ Password di-hash menggunakan **bcrypt** (`password_hash` / `password_verify`)
- ✅ Query menggunakan **prepared statements** (anti SQL Injection)
- ✅ Session-based **role guard** pada setiap halaman
- ✅ Validasi input di sisi server

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan akademik / tugas akhir.

---

<p align="center">
  <b>SPK-PIP</b> — Sistem Pendukung Keputusan Penerima Program Indonesia Pintar<br>
  Metode Weighted Product (WP) · PHP · MySQL
</p>
