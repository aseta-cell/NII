<?php

// Account settings → "Special access": turns on free access
// when the user enters the special (family) password.

require_once "config.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("account-settings.php");
}

// Small pause makes guessing the password by brute force slow
sleep(1);

if (!is_free_access_code($_POST["special_code"] ?? "")) {
    redirect("account-settings.php?error=special");
}

grant_free_access($_SESSION["user_id"]);

// Articles already accepted and waiting for payment become free too
$stmt = $conn->prepare("
    SELECT id FROM submissions
    WHERE user_id = ? AND status IN ('Accepted', 'Payment Pending')
");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();

foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
    waive_fee($row["id"], $_SESSION["user_id"]);
}

redirect("account-settings.php?special=1");
