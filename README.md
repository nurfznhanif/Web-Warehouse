# 📦 Sistem Manajemen Gudang Sederhana

Aplikasi **Sistem Manajemen Gudang Sederhana** yang dibangun menggunakan **CodeIgniter 4**. Tujuan aplikasi ini adalah untuk mencatat keluar-masuk barang dan memantau stok barang yang tersedia di gudang.

## ✨ Fitur Utama

### 🔐 Autentikasi dan Keamanan
- Sistem login dan filter akses
- Manajemen pengguna

### 📊 Manajemen Data
- CRUD Kategori Barang
- CRUD Data Barang dengan validasi stok tidak minus
- CRUD Pembelian (Purchase)

### 🔄 Transaksi
- Barang Masuk (dari pembelian, update stok otomatis)
- Barang Keluar (update stok otomatis)

### 📑 Laporan
- Laporan Barang Masuk berdasarkan rentang tanggal
- Laporan Barang Keluar berdasarkan rentang tanggal
- Laporan Stok Barang terkini

### 📌 Dashboard
- Ringkasan stok barang
- Jumlah transaksi hari ini

## ⚙️ Teknologi

- **Framework**: CodeIgniter 4
- **Database**: MySQL
- **Frontend**: Tailwind CSS
- **Bahasa Pemrograman**: PHP 7.4+

## 🚀 Petunjuk Instalasi & Setup

### 🔧 Prasyarat

- PHP versi 7.4+
- MySQL versi 5.7+
- Composer
- Web server (Apache/Nginx)

### 📝 Langkah-langkah Instalasi

1. **Clone atau download proyek**
   ```bash
   git clone <https://github.com/nurfznhanif/Web-Warehouse.git>
   cd Web-Warehouse
   ```

2. **Install dependencies menggunakan Composer**
   ```bash
   composer install
   ```

3. **Setup environment**
   ```bash
   cp env .env
   ```

4. **Edit file .env dan sesuaikan konfigurasi database**
   ```ini
   database.default.hostname = localhost
   database.default.database = nama_database_anda
   database.default.username = username_database_anda
   database.default.password = password_database_anda
   database.default.DBDriver = MySQLi
   ```

5. **Buat database**
   ```sql
   CREATE DATABASE nama_database_anda;
   ```

6. **Download dan import SQL dump**
   
   Download file SQL dump dari Google Drive:
   [📁 Download SQL Dump](https://drive.google.com/drive/folders/11nlgrY59WidBzfk-HkGrvpDZQcGkuGcD?hl=id)
   
   Import ke database:
   ```bash
   mysql -u username_database_anda -p nama_database_anda < path/to/dump.sql
   ```
   
   Atau melalui phpMyAdmin:
   - Buka phpMyAdmin
   - Pilih database yang sudah dibuat
   - Tab "Import" → pilih file SQL → "Go"

7. **Jalankan aplikasi**
   
   Untuk development, gunakan built-in server CodeIgniter:
   ```bash
   php spark serve
   ```
   
   Akses aplikasi di: http://localhost:8080

## 🔑 Login ke Aplikasi

- **Email**: admin@example.com
- **Password**: password

## 📘 Cara Penggunaan

1. Login menggunakan kredensial yang disediakan
2. Kelola **Kategori Barang** terlebih dahulu sebelum menambah barang
3. Tambah **Barang** melalui menu Barang
4. Buat **Pembelian** untuk mencatat pembelian barang dari vendor
5. Catat **Barang Masuk** berdasarkan pembelian yang sudah dibuat
6. Catat **Barang Keluar** untuk mengurangi stok
7. Lihat **Laporan** untuk memantau aktivitas gudang

## 🗄️ Struktur Database

| Tabel | Deskripsi |
|-------|-----------|
| `categories` | Menyimpan kategori barang |
| `products` | Menyimpan data barang |
| `purchases` | Menyimpan data pembelian |
| `purchase_items` | Menyimpan item dalam pembelian |
| `incoming_items` | Menyimpan transaksi barang masuk |
| `outgoing_items` | Menyimpan transaksi barang keluar |
| `users` | Menyimpan data pengguna |

## ⚡ Solusi Tantangan Teknis

### ✅ Validasi Stok Tidak Boleh Minus
**Tantangan**: Mencegah stok barang menjadi negatif saat transaksi barang keluar  
**Solusi**: Validasi server-side & client-side + transaction handling di database

### ✅ Integrasi Barang Masuk dengan Pembelian
**Tantangan**: Memastikan barang masuk sesuai jumlah pembelian  
**Solusi**: Relasi purchases ↔ incoming_items + validasi jumlah

### ✅ Laporan Berdasarkan Rentang Tanggal
**Tantangan**: Membuat filter efisien untuk rentang waktu tertentu  
**Solusi**: Query builder CodeIgniter dengan kondisi BETWEEN pada timestamp

### ✅ Keamanan & Autentikasi
**Tantangan**: Melindungi rute yang butuh login  
**Solusi**: Implementasi filters di CodeIgniter 4