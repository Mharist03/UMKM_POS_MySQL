<?php
session_start();
if (!empty($_SESSION["user"])) {
    header("Location: " . ($_SESSION["user"]["role"] === "admin" ? "admin/dashboard.php" : "kasir/dashboard.php"));
    exit;
}
$error = $_GET["error"] ?? "";
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>UNIQ-QWE</title>
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="page-wrap">
<section class="hero">
<div class="icon">🍘</div>
<h1>UMKM UNIQ QWE</h1>
<p>Cita Rasa Tradisional, Disajikan dengan Sentuhan Modern</p>
<div class="features">✓ Kelola Produk<br>✓ Transaksi Kasir<br>✓ Laporan Admin<br>✓ Data tersimpan di MySQL</div>
</section>
<main class="card">
<h2>Selamat Datang</h2>
<p>Masuk untuk melanjutkan.</p>
<form action="login_process.php" method="post">
<label>Username<input name="username" required autocomplete="username" placeholder="Masukkan username"></label>
<label>Password<div class="pw"><input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Masukkan password"><button id="toggle" type="button">👁</button></div></label>
<label>Role
<select name="role" required>
<option value="admin">Admin</option>
<option value="kasir">Kasir</option>
</select>
</label>
<?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<button class="primary" type="submit">Masuk</button>
</form>
<div class="hint"><b>Akun awal:</b><br>Admin → admin / admin123<br>Kasir → kasir / kasir123</div>
</main>
</div>
<script>
document.getElementById("toggle").onclick=()=>{const p=document.getElementById("password");p.type=p.type==="password"?"text":"password"};
</script>
</body>
</html>