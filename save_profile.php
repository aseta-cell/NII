<?php

require_once "config.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("account-settings.php");
}

$user_id = $_SESSION["user_id"];

$fullname = trim($_POST["fullname"] ?? "");
$email = trim($_POST["email"] ?? "");
$affiliation = trim($_POST["affiliation"] ?? "");
$research_interests = trim($_POST["research_interests"] ?? "");

if ($fullname === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect("account-settings.php?error=invalid");
}

// Email must not belong to another account
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    redirect("account-settings.php?error=email");
}

$stmt = $conn->prepare("
    UPDATE users
    SET fullname = ?, email = ?, affiliation = ?, research_interests = ?
    WHERE id = ?
");
$stmt->bind_param("ssssi", $fullname, $email, $affiliation, $research_interests, $user_id);
$stmt->execute();

$_SESSION["fullname"] = $fullname;
$_SESSION["email"] = $email;

redirect("account-settings.php?success=1");
