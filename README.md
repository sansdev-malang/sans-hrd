# SANS HRD (Pusat) - Human Resource Management System

SANS HRD Pusat adalah sistem manajemen sumber daya manusia terpusat yang dirancang khusus untuk mengelola kepegawaian, kehadiran, jadwal shift (roster), persetujuan cuti/izin, serta penggajian di berbagai unit sekolah (seperti SD, SMP, dan PAUD) di bawah satu naungan yayasan.

Sistem ini bertindak sebagai **Hub Pusat (Central Hub)** yang berkomunikasi secara aktif dengan database lokal masing-masing unit sekolah melalui API terintegrasi secara aman.

---

## 🚀 Fitur Utama

### 1. Manajemen Pegawai & UID Mesin
* Menghimpun data pegawai secara dinamis dari API unit sekolah aktif dengan sistem caching performa tinggi (24 jam).
* Sinkronisasi data pegawai instan (pembersihan cache pusat).
* Integrasi pembuatan ID Mesin Absensi (UID) yang diselaraskan dengan unit sekolah.

### 2. Manajemen Jadwal Shift & Roster Kerja
* Pembuatan skema jam kerja fleksibel (Shift, Non-Shift, Libur).
* Penugasan jadwal tetap maupun bergilir (Roster bulanan) secara kolektif per unit sekolah.
* Push otomatis data roster ke database unit sekolah target.
* Tombol sinkronisasi manual dinamis berdasarkan filter aktif (**"Sync Semua Jadwal"** / **"Sync Jadwal [Nama Unit]"**).

### 3. Penarikan Data Kehadiran (ZKTeco ADMS)
* Sinkronisasi otomatis log absensi mentah dari mesin ZKTeco pusat melalui protokol ADMS.
* Tombol **"Tarik Absensi Mesin"** untuk penarikan data mentah secara real-time.
* Kalkulasi otomatis status kehadiran harian (Hadir, Terlambat, Alfa, Sakit, Izin, Cuti, Dinas, Off, Libur, Pending) beserta durasi keterlambatan presisi dalam satuan menit.

### 4. Skema & Riwayat Laporan Bonus Kehadiran
* Pengaturan skema bonus keterlambatan masuk kerja berjenjang (multi-tier).
* Rekapitulasi laporan bonus bulanan pegawai berdasarkan data kehadiran riil dan cutoff payroll yang dikonfigurasi.

### 5. Pengumuman Pusat & Slip Gaji
* Membuat pengumuman massal di pusat dan menyebarkannya secara instan ke portal unit sekolah terpilih.
* Integrasi Slip Gaji bulanan dan pemicu pemberitahuan ke aplikasi unit sekolah.

### 6. Persetujuan Cuti & Izin Kehadiran
* Penarikan otomatis permohonan izin/cuti yang diajukan dari unit sekolah.
* Proses persetujuan (*Approve/Reject*) di pusat yang langsung memengaruhi status di unit dengan proteksi kegagalan koneksi (*rollback* jika unit offline).

---

## 🛠️ Arsitektur & Teknologi

* **Framework**: Laravel 11.x (PHP 8.2+)
* **Database**: MySQL / MariaDB
* **Styling**: Tailwind CSS & Vanilla CSS (Dark Mode Support)
* **Interaktivitas**: Alpine.js & Vanilla JS
* **Integrasi**: HTTP Client (Guzzle) dengan proteksi timeout (3-5 detik) untuk mencegah server hang saat unit sekolah offline.
* **Keamanan**: Token-based API Authentication per unit sekolah.

---

## ⚙️ Cara Instalasi & Menjalankan Proyek

### 1. Kloning Repositori
```bash
git clone https://github.com/sansdev-malang/sans-hrd.git
cd sans-hrd
```

### 2. Instalasi Dependensi
```bash
# Dependensi PHP
composer install

# Dependensi Frontend
npm install
npm run build
```

### 3. Konfigurasi Lingkungan (`.env`)
Salin file `.env.example` ke `.env` dan sesuaikan konfigurasi database Anda:
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Migrasi & Seed Database
```bash
php artisan migrate --seed
```

---

## 🖥️ Panduan Deployment di VPS

Setiap kali melakukan pembaruan kode dari repositori GitHub ke server VPS, jalankan perintah berikut secara berurutan:

```bash
# 1. Tarik kode terbaru dari GitHub
git pull origin main

# 2. Bersihkan & bangun ulang seluruh cache Laravel secara instan
php artisan optimize:clear
```

---

## 📡 Perintah Artisan Khusus

Sistem ini memiliki perintah latar belakang (*background job*) untuk menarik log mesin absensi secara otomatis:
```bash
# Menarik data absensi mentah dari mesin ZKTeco
php artisan zkteco:pull
```
*Disarankan untuk menjadwalkan perintah di atas di dalam task scheduler (Cron Job) setiap 5 atau 10 menit.*

---

## 📄 Lisensi
Sistem Manajemen SANS HRD ini dikembangkan secara privat untuk kebutuhan internal sekolah. Seluruh hak cipta dilindungi undang-undang.
