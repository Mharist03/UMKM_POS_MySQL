<?php
require_once "../config/database.php";
require_once "../config/auth.php";
require_role("kasir");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php"); exit;
}

$itemsJson = $_POST["items"] ?? "[]";
$items = json_decode($itemsJson, true);
$paid = (float)($_POST["paid"] ?? 0);

if (!is_array($items) || count($items) === 0) {
    exit("Keranjang kosong. <a href='dashboard.php'>Kembali</a>");
}

$conn->begin_transaction();
try {
    $total = 0;
    $validated = [];

    foreach ($items as $item) {
        $id = (int)($item["id"] ?? 0);
        $qty = (int)($item["qty"] ?? 0);
        if ($id <= 0 || $qty <= 0) throw new Exception("Data produk tidak valid.");

        $stmt = $conn->prepare("SELECT id,nama,harga,stok FROM products WHERE id=? FOR UPDATE");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) throw new Exception("Produk tidak ditemukan.");
        if ((int)$product["stok"] < $qty) throw new Exception("Stok ".$product["nama"]." tidak mencukupi.");

        $subtotal = (float)$product["harga"] * $qty;
        $total += $subtotal;
        $validated[] = [$product, $qty, $subtotal];
    }

    if ($paid < $total) throw new Exception("Uang pembayaran kurang.");

    $userId = (int)$_SESSION["user"]["id"];
    $change = $paid - $total;

    $stmt = $conn->prepare("INSERT INTO transactions (user_id,total,paid,change_amount) VALUES (?,?,?,?)");
    $stmt->bind_param("iddd",$userId,$total,$paid,$change);
    $stmt->execute();
    $transactionId = $conn->insert_id;

    foreach ($validated as [$product,$qty,$subtotal]) {
        $price = (float)$product["harga"];
        $productId = (int)$product["id"];

        $detail = $conn->prepare("INSERT INTO transaction_details (transaction_id,product_id,nama_produk,harga,quantity,subtotal) VALUES (?,?,?,?,?,?)");
        $detail->bind_param("iisdid",$transactionId,$productId,$product["nama"],$price,$qty,$subtotal);
        $detail->execute();

        $stock = $conn->prepare("UPDATE products SET stok=stok-? WHERE id=?");
        $stock->bind_param("ii",$qty,$productId);
        $stock->execute();
    }

    $conn->commit();
    header("Location: dashboard.php?success=1&change=".urlencode((string)$change));
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    $msg = urlencode($e->getMessage());
    header("Location: dashboard.php?error=".$msg);
    exit;
}
?>