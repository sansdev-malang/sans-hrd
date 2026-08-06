# 📐 Arsitektur & Spesifikasi Teknis SANS PKG (Penilaian Kinerja Guru)

Dokumen ini menjelaskan alur kerja, konsep dasar, arsitektur integrasi, skema database, dan spesifikasi API untuk modul baru **SANS PKG (Penilaian Kinerja Guru)**. Dokumen ini menjadi panduan kerja bersama agar seluruh proses pengembangan berjalan selaras.

---

## 📌 1. Gambaran Umum (Overview)

**SANS PKG** adalah aplikasi mandiri (*decoupled module*) yang dirancang khusus untuk memproses penilaian kinerja pendidik (guru) di lingkungan yayasan/sekolah SANS (SD, SMP, PAUD). 

Aplikasi ini memisahkan beban kerja kuisioner masif dari aplikasi utama **SANS HRD** (`admin.sans.sch.id`), namun tetap terintegrasi erat melalui RESTful API untuk sinkronisasi data guru, kehadiran, dan pengiriman nilai rapor akhir.

### 5 Dimensi Penilaian (Aspek PKG):
1.  **Pedagogik**: Penilaian kemampuan mengajar oleh Kepala Sekolah / Penilai.
2.  **Kepribadian**: Penilaian karakter dan keteladanan harian.
3.  **Sosial**: Penilaian komunikasi dan hubungan sosial.
4.  **Profesional**: Penilaian penguasaan materi dan kelengkapan dokumen ajar.
5.  **Kedisiplinan & Loyalitas**: Penilaian kehadiran harian (diambil dari SANS HRD) dan kepatuhan yayasan.

---

## 🔄 2. Alur Integrasi Data & Autentikasi (SSO)

SANS HRD Pusat bertindak sebagai **Gateway API** untuk menyederhanakan komunikasi. SANS PKG tidak perlu mengontak setiap unit (SD/SMP/PAUD) secara langsung.

### 2.1 Sinkronisasi Data Guru (Master Data)
```
[SANS PKG]  --- GET /api/employees --->  [SANS HRD Pusat]
                                               |
                                        (Loop ke Unit)
                                               |
                                     -> Tarik data SD
                                     -> Tarik data SMP
                                     -> Tarik data PAUD
                                               |
[SANS PKG]  <--- Gabungan Data Guru <-------------+
```

### 2.2 Autentikasi Menggunakan Akun Unit (SSO Gateway)
Guru masuk ke SANS PKG menggunakan Email & Password akun Unit mereka.
1.  Guru mengisi Form Login di **SANS PKG**.
2.  SANS PKG meneruskan kredensial ke **SANS HRD Pusat** via `POST /api/auth/verify-credential`.
3.  SANS HRD Pusat mendeteksi unit asal guru tersebut berdasarkan domain/email, lalu meneruskan request ke API Unit bersangkutan (contoh: `sd.sans.sch.id/api/auth/verify`).
4.  Jika API Unit menyatakan password valid, SANS HRD mengembalikan status sukses beserta profil guru ke SANS PKG.
5.  SANS PKG membuat sesi login aktif untuk guru tersebut.

---

## 🗄️ 3. Rancangan Skema Database (SANS PKG)

SANS PKG akan memiliki database-nya sendiri (`sans_pkg`). Berikut adalah struktur tabel utama yang diperlukan:

### 3.1 Tabel `evaluation_periods` (Periode Penilaian)
Menyimpan informasi tahun ajaran dan semester aktif.
```sql
CREATE TABLE evaluation_periods (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    academic_year VARCHAR(9) NOT NULL, -- Contoh: "2025/2026"
    semester ENUM('1', '2') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 3.2 Tabel `evaluation_indicators` (Indikator Penilaian)
Menyimpan butir pertanyaan untuk 5 aspek penilaian.
```sql
CREATE TABLE evaluation_indicators (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    aspect ENUM('pedagogik', 'kepribadian', 'sosial', 'profesional', 'kedisiplinan_loyalitas') NOT NULL,
    code VARCHAR(10) UNIQUE NOT NULL, -- Contoh: "PED_01", "DIS_02"
    question TEXT NOT NULL,
    target_respondent ENUM('supervisor', 'peer', 'student') NOT NULL, -- Siapa yang mengisi
    weight INT DEFAULT 1, -- Bobot nilai indikator
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 3.3 Tabel `evaluations` (Transaksi Penilaian Utama)
Menyimpan header penilaian guru untuk periode tertentu.
```sql
CREATE TABLE evaluations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    evaluation_period_id BIGINT FOREIGN KEY REFERENCES evaluation_periods(id),
    employee_id BIGINT NOT NULL, -- ID Guru dari Unit
    unit_id BIGINT NOT NULL,     -- ID Unit (1=SD, 2=SMP, 3=PAUD)
    evaluator_id BIGINT NOT NULL, -- ID Supervisor/Kepsek yang menilai
    score_pedagogik DECIMAL(5,2) DEFAULT 0.00,
    score_kepribadian DECIMAL(5,2) DEFAULT 0.00,
    score_sosial DECIMAL(5,2) DEFAULT 0.00,
    score_profesional DECIMAL(5,2) DEFAULT 0.00,
    score_discipline DECIMAL(5,2) DEFAULT 0.00, -- Diambil dari absensi HRD
    final_score DECIMAL(5,2) DEFAULT 0.00,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 3.4 Tabel `evaluation_details` (Detail Skor Observasi Supervisor)
```sql
CREATE TABLE evaluation_details (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    evaluation_id BIGINT FOREIGN KEY REFERENCES evaluations(id),
    indicator_id BIGINT FOREIGN KEY REFERENCES evaluation_indicators(id),
    score INT NOT NULL, -- Nilai rubrik 1 - 4
    notes TEXT NULL
);
```

### 3.5 Tabel `peer_responses` (Feedback Kuesioner Teman Sejawat)
```sql
CREATE TABLE peer_responses (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    evaluation_period_id BIGINT FOREIGN KEY REFERENCES evaluation_periods(id),
    teacher_id BIGINT NOT NULL, -- Guru yang dinilai
    peer_id BIGINT NOT NULL,    -- Rekan guru yang menilai (untuk validasi pencegahan double-vote)
    indicator_id BIGINT FOREIGN KEY REFERENCES evaluation_indicators(id),
    score INT NOT NULL -- Skala likert 1 - 4
);
```

### 3.6 Tabel `student_responses` (Feedback Kuesioner Murid - Anonim)
```sql
CREATE TABLE student_responses (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    evaluation_period_id BIGINT FOREIGN KEY REFERENCES evaluation_periods(id),
    teacher_id BIGINT NOT NULL,
    token_used VARCHAR(50) NOT NULL, -- Token unik untuk mencegah isi berulang
    indicator_id BIGINT FOREIGN KEY REFERENCES evaluation_indicators(id),
    score INT NOT NULL -- Skala likert 1 - 4
);
```

---

## 🔌 4. Spesifikasi Kontrak API (API Contract)

### 4.1 Endpoint yang Disediakan SANS HRD (Untuk dikonsumsi SANS PKG)

#### a. Mengambil Data Seluruh Guru Terintegrasi
*   **Request**: `GET /api/employees`
*   **Headers**: `X-API-TOKEN: secret_token_pkg`
*   **Response**:
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 42,
                "name": "Eko Marfidhi Susetyo",
                "email": "vidijawa@sekolahnaksalon.sch.id",
                "unit_id": 3,
                "unit_name": "PAUD",
                "zkteco_uid": "104",
                "position": "Guru Kelas"
            }
        ]
    }
    ```

#### b. Mengambil Akumulasi Kehadiran Guru untuk Nilai Kedisiplinan
*   **Request**: `GET /api/attendances/summary?start_date=2026-01-01&end_date=2026-06-30`
*   **Response**:
    ```json
    {
        "success": true,
        "data": [
            {
                "employee_id": 42,
                "unit_id": 3,
                "total_work_days": 120,
                "present_days": 118,
                "late_minutes_total": 45,
                "attendance_rate": 98.33
            }
        ]
    }
    ```

#### c. Verifikasi Kredensial SSO
*   **Request**: `POST /api/auth/verify-credential`
*   **Body**:
    ```json
    {
        "email": "vidijawa@sekolahnaksalon.sch.id",
        "password": "user_input_password"
    }
    ```
*   **Response (Jika Sukses)**:
    ```json
    {
        "success": true,
        "message": "Authenticated",
        "user": {
            "id": 42,
            "name": "Eko Marfidhi Susetyo",
            "email": "vidijawa@sekolahnaksalon.sch.id",
            "unit_name": "PAUD",
            "role": "teacher"
        }
    }
    ```

---

### 4.2 Endpoint yang Disediakan SANS PKG (Kirim Nilai Akhir ke SANS HRD)

#### a. Mengirim Rapor Kinerja Akhir setelah Periode Penilaian Selesai
*   **Request**: `POST admin.sans.sch.id/api/performance-reports`
*   **Headers**: `X-API-TOKEN: secret_token_hrd`
*   **Body**:
    ```json
    {
        "academic_year": "2025/2026",
        "semester": "2",
        "employee_id": 42,
        "unit_id": 3,
        "scores": {
            "pedagogik": 85.50,
            "kepribadian": 90.00,
            "sosial": 88.00,
            "profesional": 82.50,
            "discipline": 98.33,
            "final": 88.87
        },
        "predicate": "Amat Baik",
        "recommendations": "Pertahankan kedisiplinan dan tingkatkan inovasi media ajar interaktif."
    }
    ```

---

## 🖥️ 5. Konsep Antarmuka (UI/UX) SANS PKG

Aplikasi **SANS PKG** akan memiliki 3 tipe halaman utama yang disesuaikan dengan pengisi penilaian:

1.  **Dashboard Utama (Yayasan & Pimpinan - View Only)**
    *   Hanya menampilkan bagan pie capaian nilai guru, grafik garis perkembangan dari semester ke semester, serta papan peringkat (*Leaderboard*) guru berprestasi per unit.
2.  **Panel Evaluator (Kepala Sekolah & Tim Penilai)**
    *   Halaman observasi kelas interaktif dengan form kuesioner indikator (Pedagogik & Profesional) berbasis radio button skala 1-4.
    *   Dilengkapi kolom catatan fakta kelas untuk masing-masing aspek yang diamati.
3.  **Halaman Publik Pengisian Kuesioner Anonim (Murid)**
    *   Siswa masuk hanya menggunakan **Token Acak** yang digenerate oleh sistem (misalnya: `PKG-SD-XYZ`).
    *   Tampilan dirancang sangat ramah seluler (*mobile-friendly*) menggunakan tombol pilihan bergambar emoji (sedih, biasa, senang, sangat senang) untuk memudahkan siswa PAUD/SD memberikan feedback objektif.

---

### 💡 Rencana Aksi Selanjutnya:
1.  Membuat repository baru bernama `sans-pkg` menggunakan framework PHP/Laravel.
2.  Membuat endpoint penerima data rapor di `sans-hrd`.
3.  Menyiapkan database migration di proyek `sans-pkg`.

---
*Dokumen ini dibuat dan disetujui pada tanggal 6 Agustus 2026 untuk dijadikan acuan baku pengembangan sistem.*
