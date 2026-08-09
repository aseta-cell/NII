<?php

session_start();

require_once "config.php";


// Проверяем пользователя

if(!isset($_SESSION["user_id"])){

    header("Location: signin.php");
    exit();

}


// Проверяем статью

if(!isset($_POST["article_id"])){

    die("Article not found");

}


$user_id = $_SESSION["user_id"];

$article_id = (int)$_POST["article_id"];

$amount = 300;

$method = $_POST["method"];


// Создаем платеж


$sql = "

INSERT INTO payments

(
user_id,
article_id,
amount,
method,
status
)

VALUES

(
'$user_id',
'$article_id',
'$amount',
'$method',
'Pending'
)

";


if($conn->query($sql)){



    // Меняем статус статьи


    $update = "

    UPDATE submissions

    SET status='Payment Pending'

    WHERE id=$article_id

    ";


    $conn->query($update);



    echo "

    <script>

    alert('Payment submitted successfully');

    window.location='article.php?id=$article_id';

    </script>

    ";



}else{


    echo "Payment error: ".$conn->error;


}


?>