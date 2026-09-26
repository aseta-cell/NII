<?php

require_once "config.php";

require_login();

$user_id = $_SESSION["user_id"];
$journal = trim($_POST["journal"] ?? "");
$comment = trim($_POST["comment"] ?? "");

if ($journal === "" || $comment === "") {
    die("Invalid comment.");
}

$stmt = $conn->prepare("
    INSERT INTO journal_comments (journal, user_id, comment, status)
    VALUES (?, ?, ?, 'Published')
");
$stmt->bind_param("sis", $journal, $user_id, $comment);
$stmt->execute();

redirect_back("journals.html", "#comments");
