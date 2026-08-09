<?php

require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$fullname = trim($_POST["fullname"]);
$email = trim($_POST["email"]);
$affiliation = trim($_POST["affiliation"]);
$research_interests = trim($_POST["research_interests"]);

$stmt = $conn->prepare("
UPDATE users
SET
fullname=?,
email=?,
affiliation=?,
research_interests=?
WHERE id=?
");

$stmt->bind_param(
"ssssi",
$fullname,
$email,
$affiliation,
$research_interests,
$user_id
);

$stmt->execute();

$_SESSION["fullname"] = $fullname;

header("Location: account-settings.php?success=1");
exit();

?>