<?php

require_once "config.php";

require_role("editor");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submission_id = (int)($_POST["submission_id"] ?? 0);
    $reviewer_id = (int)($_POST["reviewer_id"] ?? 0);

    $stmt = $conn->prepare("
        UPDATE submissions
        SET reviewer_id = ?, status = 'Under Review'
        WHERE id = ?
    ");
    $stmt->bind_param("ii", $reviewer_id, $submission_id);
    $stmt->execute();
}

redirect("editor-dashboard.php");
