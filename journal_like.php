<?php

session_start();

require_once "config.php";


if (!isset($_SESSION["user_id"])) {

    header("Location: signin.php");
    exit();

}


$user_id = $_SESSION["user_id"];

$journal = $_POST["journal"] ?? "";


if ($journal === "") {

    die("Journal not found.");

}


// Проверяем существующий лайк

$stmt = $conn->prepare("
    SELECT id
    FROM journal_likes
    WHERE journal = ?
    AND user_id = ?
");

$stmt->bind_param("si", $journal, $user_id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows > 0) {

    // Удаляем лайк

    $stmt = $conn->prepare("
        DELETE FROM journal_likes
        WHERE journal = ?
        AND user_id = ?
    ");

    $stmt->bind_param("si", $journal, $user_id);

    $stmt->execute();

} else {

    // Добавляем лайк

    $stmt = $conn->prepare("
        INSERT INTO journal_likes
        (journal, user_id)
        VALUES (?, ?)
    ");

    $stmt->bind_param("si", $journal, $user_id);

    $stmt->execute();

}


header(
    "Location: journal.php"
);

exit();

?>