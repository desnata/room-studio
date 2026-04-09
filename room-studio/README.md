# Room Studio — Sistem Booking Salon
> Sistem Informasi Booking Berbasis Web | PHP + MySQL + HTML/CSS/JS

---

## Struktur File

```
room-studio/
├── config.php      ← Konfigurasi & koneksi database (PDO)
├── api.php         ← Backend API (semua request AJAX)
├── index.php       ← Halaman booking pelanggan (publik)
├── admin.php       ← Panel admin (manajemen booking, jadwal, pelanggan)
└── database.sql    ← Skema database + seed data awal
```

---

## Cara Instalasi

### 1. Prasyarat
- PHP 7.4+ (disarankan PHP 8.x)
- MySQL 5.7+ atau MariaDB 10.3+
- Web server: Apache (XAMPP/LAMPP) atau Nginx

### 2. Buat Database
Buka **phpMyAdmin** atau MySQL CLI, lalu jalankan:
```sql
source /path/to/room-studio/database.sql
```
Atau copy-paste isi file `database.sql` ke phpMyAdmin → tab SQL → klik Go.

### 3. Konfigurasi Koneksi
Edit file `config.php` bagian berikut:
```php
define('DB_HOST', 'localhost');   // host MySQL Anda
define('DB_USER', 'root');        // username MySQL
define('DB_PASS', '');            // password MySQL
define('DB_NAME', 'room_studio'); // nama database
```

### 4. Upload / Taruh di Web Server
- **XAMPP**: Taruh folder `room-studio/` di `C:/xampp/htdocs/`
- **Akses**: `http://localhost/room-studio/index.php`

### 5. Akses Sistem
| Halaman | URL |
|---|---|
| Booking Pelanggan | `http://localhost/room-studio/index.php` |
| Panel Admin | `http://localhost/room-studio/admin.php` |

---

## Fitur Sistem

### Halaman Booking (index.php)
- Tampilkan semua layanan dari price list (Eyelash, Nail, Hair Service)
- Pilih layanan dengan klik card → otomatis isi form
- **Deteksi double booking real-time** saat memilih tanggal & jam
- Simpan data pelanggan otomatis (upsert by nomor telepon)
- Konfirmasi booking langsung tampil dengan kode unik (RS-0001, dst.)
- Price list lengkap di bagian bawah halaman

### Panel Admin (admin.php)
- **Dashboard**: statistik total booking, pending, hari ini, selesai, jumlah pelanggan
- **Semua Booking**: tabel dengan filter status & pencarian; tombol konfirmasi/batalkan/selesai
- **Jadwal Mingguan**: tampilan per hari dengan semua slot waktu — mudah lihat mana yang kosong/terisi
- **Data Pelanggan**: list semua pelanggan + riwayat lengkap setiap kunjungan

---

## Skema Database

```
kategori          layanan              pelanggan
─────────         ──────────           ──────────
id (PK)           id (PK)              id (PK)
nama              kategori_id (FK)     nama
urutan            nama                 telepon (UNIQUE)
                  harga_min            email
                  harga_max            catatan
                  satuan               dibuat
                  aktif

booking
──────────────────────
id (PK)
kode (UNIQUE)         ← format RS-0001
pelanggan_id (FK)
layanan_id (FK)
tanggal
jam
catatan
status (ENUM)         ← pending | confirmed | selesai | batal
dibuat
diperbarui
```

---

## Keamanan (Rekomendasi untuk Produksi)
1. Tambahkan halaman login di `admin.php` dengan session PHP
2. Ganti kredensial database dari `root` ke user terbatas
3. Tambahkan CSRF token pada form booking
4. Aktifkan HTTPS / SSL di web server
5. Buat file `.htaccess` untuk block akses langsung ke `config.php` dan `api.php`

```apache
# .htaccess — blokir akses langsung
<FilesMatch "^(config|api)\.php$">
  Order Deny,Allow
  Deny from all
  Allow from 127.0.0.1
</FilesMatch>
```
*(hapus rule ini setelah setup; api.php perlu diakses dari browser)*

---

## Daftar Layanan (dari Price List)
| Kategori | Jumlah Layanan |
|---|---|
| Eyelash | 8 layanan |
| Nail | 17 layanan |
| Hair Service | 11 layanan |
| **Total** | **36 layanan** |

---

*Dikembangkan untuk Room Studio · Desa Siangan, Gianyar, Bali*
