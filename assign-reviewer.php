<?php

session_start();
require_once "config.php";

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $submission_id = (int)$_POST["submission_id"];
    $reviewer_id = (int)$_POST["reviewer_id"];

    $stmt = $conn->prepare("
        UPDATE submissions
        SET reviewer_id=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "ii",
        $reviewer_id,
        $submission_id
    );

    $stmt->execute();
}

header("Location: editor-dashboard.php");
exit();