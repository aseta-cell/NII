<?php

require_once "config.php";

require_login();

$user_id = $_SESSION["user_id"];
$journal = trim($_POST["journal"] ?? "");

if ($journal === "") {
    die("Journal not found.");
}

// Toggle like: remove it if it exists, otherwise add it
$stmt = $conn->prepare("DELETE FROM journal_likes WHERE journal = ? AND user_id = ?");
$stmt->bind_param("si", $journal, $user_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO journal_likes (journal, user_id) VALUES (?, ?)");
    $stmt->bind_param("si", $journal, $user_id);
    $stmt->execute();
}

redirect_back("journals.html");
