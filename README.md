# Room Studio — Beauty Salon Booking System ✨

Room Studio adalah sistem reservasi salon kecantikan berbasis web modern yang menggunakan arsitektur **Jamstack**. Proyek ini dirancang agar pelanggan dapat dengan mudah melakukan *booking* perawatan (Eyelash, Nail Art, Hair Service) secara daring, sementara admin dapat mengelola jadwal melalui *dashboard* interaktif secara *real-time*.

## 🚀 Teknologi yang Digunakan (Tech Stack)

Sistem ini tidak menggunakan bahasa *server-side* tradisional (seperti PHP), melainkan beroperasi secara penuh di *client-side* untuk performa maksimal.

* **Frontend:** HTML5, CSS3 murni (Vanilla), JavaScript (ES6+).
* **Backend & Database:** [Supabase](https://supabase.com/) (PostgreSQL & REST API).
* **Authentication:** Supabase Auth (Email & Password).
* **Hosting:** [Vercel](https://vercel.com/) (Serverless CDN).

## ✨ Fitur Utama

### Untuk Pelanggan (Frontend)
* **Katalog Dinamis:** Menampilkan kategori dan layanan salon yang ditarik secara langsung (API Fetch) dari database.
* **Cek Ketersediaan Otomatis:** Memeriksa apakah jadwal/waktu yang dipilih sudah dibooking oleh orang lain atau belum.
* **Form Reservasi:** Mengumpulkan data diri pelanggan (Nama, WhatsApp, Tanggal, Jam, Layanan).
* **Desain Responsif:** Tampilan elegan (UI/UX) yang dioptimalkan untuk perangkat mobile maupun desktop.

### Untuk Admin (Dashboard)
* **Login Aman:** Menggunakan sistem Autentikasi Supabase.
* **Manajemen Booking:** Mengubah status reservasi (Pending, Confirmed, Selesai, Batal).
* **Jadwal Mingguan:** Tampilan kalender khusus untuk melihat siapa saja yang memiliki jadwal dalam 7 hari ke depan.
* **Notifikasi Real-time:** Notifikasi pop-up akan langsung muncul di layar Admin ketika ada pelanggan yang melakukan *booking* (menggunakan Supabase Realtime).

## 📁 Struktur Folder

Proyek ini menerapkan prinsip *Separation of Concerns* (pemisahan logika, kerangka, dan desain):

```text
room-studio/
├── css/
│   └── style.css       # File utama untuk gaya/desain halaman
├── js/
│   ├── main.js         # Logika untuk halaman booking pelanggan (index.html)
│   ├── admin.js        # Logika dashboard admin (mengambil & mengubah data)
│   └── login.js        # Logika sistem login Supabase
├── image/              # Direktori aset gambar (Logo, Eyelash, Nail, dll.)
├── index.html          # Halaman utama (Landing Page & Form Booking)
├── login.html          # Halaman login untuk Admin
├── admin.html          # Halaman Dashboard Super-Admin
└── README.md           # Dokumentasi proyek ini