-- MIGRASI AMAN UNTUK DATABASE YANG SUDAH ADA
-- Jalankan sekali di phpMyAdmin pada database yang dipakai aplikasi.
ALTER TABLE products ADD COLUMN gambar VARCHAR(255) DEFAULT NULL AFTER stok;
