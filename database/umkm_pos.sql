CREATE DATABASE IF NOT EXISTS umkm_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE umkm_pos;

DROP TABLE IF EXISTS transaction_details;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','kasir') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  kategori VARCHAR(80) NOT NULL,
  harga DECIMAL(15,2) NOT NULL DEFAULT 0,
  stok INT NOT NULL DEFAULT 0,
  gambar VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE transactions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  total DECIMAL(15,2) NOT NULL,
  paid DECIMAL(15,2) NOT NULL,
  change_amount DECIMAL(15,2) NOT NULL,
  CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE transaction_details (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  transaction_id BIGINT NOT NULL,
  product_id INT NOT NULL,
  nama_produk VARCHAR(150) NOT NULL,
  harga DECIMAL(15,2) NOT NULL,
  quantity INT NOT NULL,
  subtotal DECIMAL(15,2) NOT NULL,
  CONSTRAINT fk_details_transaction FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
  CONSTRAINT fk_details_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- Password hash untuk password: admin123 dan kasir123
INSERT INTO users (nama, username, password, role) VALUES
('Administrator', 'admin', '$2y$12$6zFtJTHfizKBI0OAMD69Uu5iUW9EN7.9YkTfpbBACiEemiiEW0HFa', 'admin'),
('Kasir', 'kasir', '$2y$12$I16WJUjkMz0RIJHGbdaZfOZA7VhhgzBYARj25vBWsvZ/eSNyAk2Wy', 'kasir');

INSERT INTO products (nama,kategori,harga,stok,gambar) VALUES
('Onde-onde','Jajanan Pasar',5000,25,NULL),
('Lemper','Jajanan Pasar',7000,20,NULL),
('Kue Lapis','Kue Tradisional',5000,18,NULL),
('Keripik','Snack',8000,5,NULL);
