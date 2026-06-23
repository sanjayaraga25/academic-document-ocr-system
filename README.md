# Academic OCR

Sistem OCR untuk pemrosesan transkrip akademik dan ijazah. Menggunakan EasyOCR untuk mengekstrak teks dari dokumen scan, kemudian mem-parsing data akademik seperti nama mahasiswa, NIM, IPK, mata kuliah, dan nilai.

## Fitur

| Fitur | Deskripsi |
|---|---|
| **Upload Dokumen** | Upload PDF/gambar via drag-drop |
| **OCR Processing** | Ekstraksi teks otomatis via Python microservice |
| **Ekstraksi Data Akademik** | Parse teks OCR ke data terstruktur (nama, NIM, IPK, dll) |
| **Verifikasi Dokumen** | Approve/reject dokumen dengan catatan |
| **Kualifikasi** | Tandai kelayakan (beasiswa, dll) secara massal |
| **Dashboard** | Statistik, tren upload, peringatan dokumen stuck |
| **Pencarian & Filter** | Cari berdasarkan nama, NIM, filename, status, IPK |
| **Export** | Download data ke Excel, CSV, atau PDF |

## Tech Stack

| Layer | Teknologi |
|---|---|
| **Backend** | PHP 8.2+, Laravel 12 |
| **Database** | MySQL |
| **Frontend** | Blade, Tailwind CSS, Vite, Alpine.js |
| **OCR Engine** | Python 3 + FastAPI + EasyOCR |
| **Queue** | Database queue |
| **Export** | Laravel Excel, DomPDF |

## Requirements

- PHP ^8.2
- Composer
- Node.js & npm
- MySQL
- Python 3.8+ (untuk OCR service)

## Instalasi

### 1. Clone & Install PHP Dependencies

```bash
git clone <repo-url> ocr
cd ocr
composer install
```

### 2. Environment

```bash
cp .env.example .env
# edit .env sesuai database dan konfigurasi Anda
```

### 3. Generate Key & Migrate

```bash
php artisan key:generate
php artisan migrate
```

### 4. Frontend Assets

```bash
npm install
npm run build
```

### 5. Python OCR Service

```bash
cd python-service
pip install -r requirements.txt
uvicorn main:app --host 0.0.0.0 --port 8001
```

### 6. Jalankan Aplikasi

```bash
# Terminal 1 - Laravel
php artisan serve

# Terminal 2 - Queue Worker
php artisan queue:work
```

Akses di **http://localhost:8000**

## Struktur Data

```
User → hasMany Document (uploader)
     → hasMany Verification (verifier)

Document → hasOne OcrResult
         → hasOne AcademicRecord
         → hasMany Verification

AcademicRecord → hasMany AcademicCourse
```

**Status dokumen:** `pending → processing → ready → verified / rejected`

## Arsitektur

```
┌─────────────────────┐     ┌──────────────────────┐
│   Laravel (:8000)    │     │  Python OCR (:8001)  │
│                      │────>│                      │
│  Controllers/Services│     │  FastAPI + EasyOCR   │
│  Queue (ProcessOcr)  │<────│  /ocr, /health       │
└─────────────────────┘     └──────────────────────┘
```

## API Routes

| Method | Route | Deskripsi |
|---|---|---|
| GET | `/login` | Halaman login |
| GET | `/dashboard` | Dashboard statistik |
| GET | `/documents` | Daftar dokumen |
| POST | `/documents` | Upload dokumen |
| POST | `/documents/{id}/ocr` | Proses OCR |
| POST | `/documents/{id}/verify` | Verifikasi dokumen |
| GET | `/export/excel` | Export Excel |
| GET | `/export/pdf` | Export PDF |
| GET | `/ocr/health` | Cek status OCR service |

## Lisensi

Hak cipta dilindungi undang-undang.
