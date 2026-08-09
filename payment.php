<?php

session_start();

require_once "config.php";


if(!isset($_SESSION["user_id"])){

    header("Location: signin.php");
    exit();

}


if(!isset($_GET["id"])){

    die("Article not found");

}


$id = (int)$_GET["id"];



$sql = "

SELECT *

FROM submissions

WHERE id=$id

";


$result=$conn->query($sql);


$article=$result->fetch_assoc();



?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<title>
Payment
</title>


<script src="https://cdn.tailwindcss.com"></script>


</head>


<body class="bg-gray-100">


<div class="max-w-5xl mx-auto py-12 px-6">


<div class="bg-white rounded-xl shadow-lg p-10">


<h1 class="text-4xl font-bold mb-6">

Article Processing Charge

</h1>



<div class="bg-blue-50 p-6 rounded-lg mb-8">


<h2 class="text-2xl font-bold">

<?= htmlspecialchars($article["title"]) ?>

</h2>


<p class="mt-3">

Journal:

<?= htmlspecialchars($article["journal"]) ?>

</p>


</div>





<h2 class="text-3xl font-bold mb-6">

Choose Payment Plan

</h2>



<div class="grid md:grid-cols-3 gap-6">



<div class="border rounded-xl p-6 text-center">


<h3 class="text-xl font-bold">

Standard

</h3>


<p class="text-4xl font-bold text-blue-700 mt-4">

$300

</p>


<button
class="mt-6 bg-blue-700 text-white px-6 py-3 rounded-lg">

Pay Now

</button>


</div>





<div class="border-2 border-blue-700 rounded-xl p-6 text-center">


<h3 class="text-xl font-bold">

Professional

</h3>


<p class="text-4xl font-bold text-blue-700 mt-4">

$500

</p>


<button
class="mt-6 bg-blue-700 text-white px-6 py-3 rounded-lg">

Pay Now

</button>


</div>





<div class="border rounded-xl p-6 text-center">


<h3 class="text-xl font-bold">

Institutional

</h3>


<p class="text-4xl font-bold text-blue-700 mt-4">

$800

</p>

<form action="process_payment.php" method="POST">


<input 
type="hidden"
name="article_id"
value="<?= $article["id"] ?>">


<input
type="hidden"
name="method"
value="Card">


<button
class="mt-6 bg-blue-700 text-white px-6 py-3 rounded-lg">

Pay Now

</button>


</form>


</div>



</div>


</div>


</div>


</body>

</html>