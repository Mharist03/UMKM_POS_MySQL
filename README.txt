UMKM POS - PHP + MySQL

CARA MENJALANKAN
1. Extract folder UMKM-POS-MySQL ke:
   C:\xampp\htdocs\UMKM-POS-MySQL

2. Jalankan Apache dan MySQL dari XAMPP.

3. Buka:
   http://localhost/phpmyadmin

4. Pilih Import -> Choose File:
   database/umkm_pos.sql

5. Klik Import/Go.

6. Buka aplikasi:
   http://localhost/UMKM-POS-MySQL/

AKUN AWAL
Admin:
username: admin
password: admin123

Kasir:
username: kasir
password: kasir123

CATATAN
- Data produk tersimpan di tabel products.
- Data akun tersimpan di tabel users.
- Transaksi tersimpan di tabel transactions.
- Rincian barang transaksi tersimpan di tabel transaction_details.
- Stok berkurang otomatis ketika transaksi berhasil.
- Penghapusan produk yang sudah pernah masuk transaksi dibatasi agar riwayat laporan tetap konsisten.
- Jika MySQL Anda menggunakan password root, ubah config/database.php.


PERBAIKAN UNIQ-QWE:
- Tampilan kartu produk mendukung gambar.
- Admin dapat upload gambar produk (JPG/PNG/WEBP).
- Untuk database lama, import database/tambah_kolom_gambar.sql sekali saja. Data lama tidak dihapus.
- Gambar disimpan di assets/images/products/.
