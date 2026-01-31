# Panduan Deployment - Sistem Absensi & Payroll

Dokumen ini berisi langkah-langkah untuk melakukan deployment aplikasi ke server produksi.

## Persyaratan Server

- PHP 8.1 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi / MariaDB
- Ekstensi PHP: `intl`, `mbstring`, `mysqlnd`, `gd`, `curl`, `xml`
- Apache/Nginx dengan modul `mod_rewrite` diaktifkan

## Langkah-langkah Deployment

### A. Jika Menggunakan Terminal (VPS/Git)

1. **Persiapan File**: Upload semua file ke server atau lakukan `git clone`.
2. **Konfigurasi Environment**: Copy `.env.example` ke `.env` dan isi datanya.
3. **Generate Key**: `php spark key:generate`
4. **Setup Database**: `php spark migrate` dan (opsional) `php spark db:seed MainSeeder`

---

### B. Jika Menggunakan Shared Hosting (Tanpa Terminal / cPanel)

Karena Anda tidak memiliki akses terminal, lakukan langkah-langkah berikut:

#### 1. Persiapan Database (Lakukan di Laptop/Lokal XAMPP)

- Buka **phpMyAdmin** di laptop Anda.
- Pilih database `absesi`.
- Klik tab **Export (Ekspor)**, lalu klik **Go (Kirim)** untuk mendapatkan file `.sql`.
- Di **cPanel Hosting**, buka phpMyAdmin, buat database baru, lalu klik tab **Import (Impor)** dan pilih file `.sql` tadi.

#### 2. Konfigurasi Environment (.env)

Meskipun tidak ada terminal, Anda tetap bisa menggunakan file `.env`:

- Upload file `.env.example` ke server, lalu rename menjadi `.env`.
- **Tips cPanel**: Jika file `.env` tidak muncul setelah di-rename, klik tombol **Settings** di pojok kanan atas File Manager dan centang **"Show Hidden Files (dotfiles)"**.
- Edit file `.env` dan sesuaikan:
  - `CI_ENVIRONMENT = production`
  - `app.baseURL = 'https://alamat-web-anda.com/'`
  - Data database (`hostname`, `database`, `username`, `password`)
- **Encryption Key**: Karena tidak bisa menjalankan perintah `php spark key:generate` di server, silakan jalankan perintah tersebut di laptop (lokal) Anda, lalu copy kode yang dihasilkan (misal: `hex2bin:xxxx`) ke dalam file `.env` di hosting pada bagian `encryption.key`.

#### 3. Folder Structure (Cara Paling Aman)

Agar file aplikasi Anda aman (tidak bisa diakses publik), gunakan struktur ini di cPanel:

- Letakkan semua file aplikasi (folder `app`, `system`, `writable`, dll) ke folder baru di luar `public_html`, misal di folder `/home/username/absesi_app/`.
- Letakkan **isi** dari folder `public` ke dalam folder `public_html`.
- Buka `public_html/index.php`, cari baris ini:

  ```php
  require FCPATH . '../app/Config/Paths.php';
  ```

- Ubah menjadi path folder aplikasi tadi:

  ```php
  require FCPATH . '../absesi_app/app/Config/Paths.php';
  ```

#### 4. Pengaturan Izin (Permissions) manual

Di cPanel **File Manager**, klik kanan folder berikut dan pilih **Change Permissions**:

- Folder `writable/cache`, `logs`, `session`, `uploads` (beserta subfolder di dalamnya).
- Set menjadi **775** atau **777** jika 775 tidak berhasil.

---

## Tips Tambahan untuk Shared Hosting

- **PHP Version**: Pastikan di menu "Select PHP Version" pada cPanel, Anda sudah memilih versi **8.1** ke atas.
- **Ekstensi**: Pastikan ekstensi `intl` dan `gd` sudah dicentang/aktif di pengaturan PHP cPanel.
- **Simlink Uploads**: Jika file lampiran tidak muncul, buat folder `uploads` di dalam `public_html` (jika menggunakan metode pisah folder di atas).

---

## Catatan Keamanan

- Jangan pernah meng-upload file `.env` yang berisi password asli ke publik (Git).
- Selalu gunakan `CI_ENVIRONMENT = production` di server live.
- Pastikan folder `writable` tidak bisa diakses langsung via URL.
