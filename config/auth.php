<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_role(string $role): void {
    if (empty($_SESSION["user"]) || ($_SESSION["user"]["role"] ?? "") !== $role) {
        header("Location: ../index.php");
        exit;
    }
}

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>