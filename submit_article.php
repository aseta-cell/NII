<?php

// Handles the manuscript form on publish.php

require_once "config.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("publish.php");
}

$title = trim($_POST["title"] ?? "");
$journal = trim($_POST["journal"] ?? "");
$abstract = trim($_POST["abstract"] ?? "");
$keywords = trim($_POST["keywords"] ?? "");

if ($title === "" || $journal === "" || $abstract === "") {
    redirect("publish.php?error=fields");
}

$filename = save_upload("article_file", ["pdf", "doc", "docx"]);

if ($filename === null) {
    redirect("publish.php?error=file");
}

$stmt = $conn->prepare("
    INSERT INTO submissions (user_id, title, journal, abstract, keywords, filename)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("isssss", $_SESSION["user_id"], $title, $journal, $abstract, $keywords, $filename);
$stmt->execute();

redirect("dashboard.php?submitted=1");
