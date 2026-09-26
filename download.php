<?php

// Download link for a published article's file: counts the download.

require_once "config.php";

$id = (int)($_GET["id"] ?? 0);

$stmt = $conn->prepare("
    SELECT filename FROM submissions
    WHERE id = ? AND status = 'Published' AND filename IS NOT NULL
");
$stmt->bind_param("i", $id);
$stmt->execute();

$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    http_response_code(404);
    die("File not found.");
}

$stmt = $conn->prepare("UPDATE submissions SET downloads = downloads + 1 WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

redirect(upload_url($article["filename"]));
