<?php

require_once "config.php";

require_role("editor");

$allowed = ["Submitted", "Under Review", "Accepted", "Revision", "Rejected", "Published"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submission = (int)($_POST["submission_id"] ?? 0);
    $decision = $_POST["decision"] ?? "";

    if (in_array($decision, $allowed, true)) {
        $stmt = $conn->prepare("UPDATE submissions SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $decision, $submission);
        $stmt->execute();
    }
}

redirect("editor-dashboard.php");
