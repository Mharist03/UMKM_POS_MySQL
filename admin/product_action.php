<?php
require_once "../config/database.php";
require_once "../config/auth.php";
require_role("admin");

function uploadProductImage(): ?string {
    if (empty($_FILES["gambar"]) || ($_FILES["gambar"]["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($_FILES["gambar"]["error"] !== UPLOAD_ERR_OK) return null;

    $allowed = ["image/jpeg"=>"jpg","image/png"=>"png","image/webp"=>"webp"];
    $mime = function_exists("mime_content_type") ? mime_content_type($_FILES["gambar"]["tmp_name"]) : ($_FILES["gambar"]["type"] ?? "");
    if (!isset($allowed[$mime])) return null;

    $dir = __DIR__ . "/../assets/images/products/";
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $name = "product_" . bin2hex(random_bytes(8)) . "." . $allowed[$mime];
    return move_uploaded_file($_FILES["gambar"]["tmp_name"], $dir . $name) ? $name : null;
}

$action = $_POST["action"] ?? "";

if ($action === "add" || $action === "edit") {
    $nama = trim($_POST["nama"] ?? "");
    $kategori = trim($_POST["kategori"] ?? "Lainnya");
    $harga = (float)($_POST["harga"] ?? -1);
    $stok = (int)($_POST["stok"] ?? -1);
    $gambar = uploadProductImage();

    if ($nama !== "" && $harga >= 0 && $stok >= 0) {
        if ($action === "add") {
            if ($gambar !== null) {
                $stmt = $conn->prepare("INSERT INTO products (nama,kategori,harga,stok,gambar) VALUES (?,?,?,?,?)");
                $stmt->bind_param("ssdis", $nama,$kategori,$harga,$stok,$gambar);
            } else {
                $stmt = $conn->prepare("INSERT INTO products (nama,kategori,harga,stok) VALUES (?,?,?,?)");
                $stmt->bind_param("ssdi", $nama,$kategori,$harga,$stok);
            }
        } else {
            $id = (int)($_POST["id"] ?? 0);
            if ($gambar !== null) {
                $old = $conn->prepare("SELECT gambar FROM products WHERE id=?");
                $old->bind_param("i",$id); $old->execute();
                $oldRow = $old->get_result()->fetch_assoc();
                $stmt = $conn->prepare("UPDATE products SET nama=?,kategori=?,harga=?,stok=?,gambar=? WHERE id=?");
                $stmt->bind_param("ssdisi", $nama,$kategori,$harga,$stok,$gambar,$id);
                if (!empty($oldRow["gambar"])) {
                    $oldFile = __DIR__ . "/../assets/images/products/" . basename($oldRow["gambar"]);
                    if (is_file($oldFile)) @unlink($oldFile);
                }
            } else {
                $stmt = $conn->prepare("UPDATE products SET nama=?,kategori=?,harga=?,stok=? WHERE id=?");
                $stmt->bind_param("ssdii", $nama,$kategori,$harga,$stok,$id);
            }
        }
        $stmt->execute();
    }
} elseif ($action === "delete") {
    $id = (int)($_POST["id"] ?? 0);
    $check = $conn->prepare("SELECT COUNT(*) c FROM transaction_details WHERE product_id=?");
    $check->bind_param("i",$id); $check->execute();
    $used = (int)$check->get_result()->fetch_assoc()["c"];
    if ($used === 0) {
        $old = $conn->prepare("SELECT gambar FROM products WHERE id=?");
        $old->bind_param("i",$id); $old->execute();
        $oldRow = $old->get_result()->fetch_assoc();
        $stmt=$conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i",$id); $stmt->execute();
        if (!empty($oldRow["gambar"])) {
            $oldFile = __DIR__ . "/../assets/images/products/" . basename($oldRow["gambar"]);
            if (is_file($oldFile)) @unlink($oldFile);
        }
    }
}
header("Location: dashboard.php#produk");
exit;
?>