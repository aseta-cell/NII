<?php

session_start();
require_once "config.php";

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $submission = (int)$_POST["submission_id"];
    $decision = $_POST["decision"];

    $stmt = $conn->prepare("
        UPDATE submissions
        SET status=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "si",
        $decision,
        $submission
    );

    $stmt->execute();

}

header("Location: editor-dashboard.php");
exit();