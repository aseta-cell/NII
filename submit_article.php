<?php

require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"]);
    $abstract = trim($_POST["abstract"]);
    $keywords = trim($_POST["keywords"]);

    $fileName = "";

    if (isset($_FILES["article_file"]) && $_FILES["article_file"]["error"] == 0) {

        $uploadDir = "uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["article_file"]["name"]);

        move_uploaded_file(
            $_FILES["article_file"]["tmp_name"],
            $uploadDir . $fileName
        );
    }

    $stmt = $conn->prepare("
        INSERT INTO articles
        (user_id,title,abstract,keywords,file_name)
        VALUES (?,?,?,?,?)
    ");

    $stmt->bind_param(
        "issss",
        $_SESSION["user_id"],
        $title,
        $abstract,
        $keywords,
        $fileName
    );

    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}

?>