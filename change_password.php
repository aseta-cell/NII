<?php

require_once "config.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("account-settings.php");
}

$current = $_POST["current_password"] ?? "";
$new = $_POST["new_password"] ?? "";
$repeat = $_POST["new_password2"] ?? "";

$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($current, $user["password"])) {
    redirect("account-settings.php?error=current");
}

if (strlen($new) < 6) {
    redirect("account-settings.php?error=short");
}

if ($new !== $repeat) {
    redirect("account-settings.php?error=mismatch");
}

$hash = password_hash($new, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $hash, $_SESSION["user_id"]);
$stmt->execute();

redirect("account-settings.php?password=1");
