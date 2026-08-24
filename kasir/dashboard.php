<?php
require_once "../config/database.php";
require_once "../config/auth.php";
require_role("kasir");
$user = $_SESSION["user"];
$products = $conn->query("SELECT * FROM products WHERE stok > 0 ORDER BY nama ASC");
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard Kasir</title><link rel="stylesheet" href="../assets/css/dashboard.css"></head>
<body>
<div class="app">
<aside><div class="brand">🍘 UNIQ-QWE</div><p class="role">KASIR • JAJANAN TRADISIONAL</p><a class="logout" href="../logout.php">Keluar</a></aside>
<main>
<header><div><h1>Menu UNIQ-QWE</h1><p>Jajanan tradisional pilihan, transaksi tersimpan langsung ke database.</p></div><div class="user">Halo, <?= h($user["nama"]) ?></div></header>
<form id="checkoutForm" action="checkout.php" method="post">
<div class="cashier-layout">
<section class="panel"><div class="toolbar"><div><h3>Menu Tradisional</h3><div class="category-note">Onde-onde • Lemper • Kue tradisional dan lainnya</div></div><input id="productSearch" type="search" placeholder="Cari menu"></div>
<div class="product-list" id="productList">
<?php while($p=$products->fetch_assoc()): ?>
<div class="product product-card" data-name="<?= h(strtolower($p["nama"])) ?>">
<div class="product-image">
<?php if (!empty($p["gambar"])): ?>
<img src="../assets/images/products/<?= h($p["gambar"]) ?>" alt="<?= h($p["nama"]) ?>">
<?php else: ?><span>🍘</span><?php endif; ?>
</div>
<div class="product-info"><h4><?= h($p["nama"]) ?></h4><small><?= h($p["kategori"]) ?></small><b>Rp<?= number_format($p["harga"],0,",",".") ?></b><small>Stok tersedia: <?= (int)$p["stok"] ?></small></div>
<button class="add" type="button" onclick='addCart(<?= json_encode(["id"=>(int)$p["id"],"nama"=>$p["nama"],"harga"=>(float)$p["harga"],"stok"=>(int)$p["stok"]], JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>+ Tambah</button>
</div>
<?php endwhile; ?>
</div></section>
<section class="panel">
<h3>Keranjang</h3><div id="cartItems"></div><div id="hiddenItems"></div>
<div class="total">Total <b id="cartTotal">Rp0</b></div>
<label>Uang Dibayar<input id="paid" name="paid" type="number" min="0" required></label>
<div class="change">Kembalian: <b id="change">Rp0</b></div>
<button class="primary" type="submit">Selesaikan Transaksi</button>
</section>
</div>
</form>
</main></div>
<script src="../assets/js/kasir-db.js"></script>
</body></html>