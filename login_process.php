<?php
require_once "config/database.php";
require_once "config/auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";
$role = $_POST["role"] ?? "";

if ($username === "" || $password === "" || !in_array($role, ["admin", "kasir"], true)) {
    header("Location: index.php?error=Lengkapi+data+login");
    exit;
}

$stmt = $conn->prepare("SELECT id, nama, username, password, role FROM users WHERE username=? AND role=? LIMIT 1");
$stmt->bind_param("ss", $username, $role);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($password, $user["password"])) {
    header("Location: index.php?error=Username,+password,+atau+role+tidak+sesuai");
    exit;
}

unset($user["password"]);
$_SESSION["user"] = $user;

header("Location: " . ($role === "admin" ? "admin/dashboard.php" : "kasir/dashboard.php"));
exit;
?>