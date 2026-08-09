<?php
require_once "config.php";

if (!isset($_GET["id"])) {
    die("Article not found.");
}

$id = (int)$_GET["id"];

$sql = "
SELECT
    submissions.*,
    users.fullname AS author
FROM submissions
JOIN users
ON submissions.user_id = users.id
WHERE submissions.id = $id
AND submissions.status='Published'
";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Article not found.");
}

$article = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($article["title"]) ?></title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

<div class="max-w-6xl mx-auto py-12 px-6">

    <a href="archive.php"
       class="text-blue-600 hover:underline">
        ← Back to Archive
    </a>

    <div class="bg-white rounded-xl shadow-lg p-10 mt-6">

        <span class="bg-blue-700 text-white text-sm px-4 py-2 rounded">
            Published Article
        </span>

        <h1 class="text-5xl font-bold mt-6 mb-6">
            <?= htmlspecialchars($article["title"]) ?>
        </h1>

        <div class="grid md:grid-cols-2 gap-6 text-lg">

            <div>

                <p class="mb-3">
                    <strong>Authors:</strong><br>
                    <?= htmlspecialchars($article["author"]) ?>
                </p>

                <p class="mb-3">
                    <strong>Journal:</strong><br>
                    <?= htmlspecialchars($article["journal"]) ?>
                </p>

                <p class="mb-3">

<strong>DOI:</strong><br>

<a 
href="https://doi.org/<?= htmlspecialchars($article["doi"]) ?>"
target="_blank"
class="text-blue-700 hover:underline">

<?= htmlspecialchars($article["doi"]) ?>

</a>

</p>

            </div>

            <div>

                <p class="mb-3">
                    <strong>Published:</strong><br>
                    <?= date("d F Y", strtotime($article["created_at"])) ?>
                </p>

                <p class="mb-3">
                    <strong>Status:</strong><br>
                    Published
                </p>

            </div>

        </div>

        <hr class="my-8">

        <h2 class="text-3xl font-bold mb-4">
            Abstract
        </h2>

        <p class="leading-8 text-gray-700">
            <?= nl2br(htmlspecialchars($article["abstract"])) ?>
        </p>

        <hr class="my-8">

       <hr class="my-8">


<h2 class="text-3xl font-bold mb-4">

How to cite this article

</h2>


<div class="bg-gray-100 p-6 rounded-lg">


<p class="text-gray-700">

<?= htmlspecialchars($article["author"]) ?>.

<?= htmlspecialchars($article["title"]) ?>.

<i>
<?= htmlspecialchars($article["journal"]) ?>
</i>.

<?= date("Y", strtotime($article["created_at"])) ?>.


DOI:
<?= htmlspecialchars($article["doi"]) ?>

</p>


</div>

        <p class="text-gray-700">
            <?= htmlspecialchars($article["keywords"]) ?>
        </p>

        <hr class="my-8">

        <div class="flex gap-4 mb-8">

            <a
                href="<?= htmlspecialchars($article["pdf"]) ?>"
                target="_blank"
                class="bg-blue-700 text-white px-6 py-3 rounded-lg hover:bg-blue-800">

                Download PDF

            </a>

            <a href="archive.php?journal=<?= urlencode($article["journal"]) ?>"
class="text-blue-600 hover:underline">

← Back to Archive

</a>
            

        </div>
<hr class="my-8">


<h2 class="text-3xl font-bold mb-6">

Article Metrics

</h2>


<div class="grid md:grid-cols-3 gap-6">


<div class="bg-blue-50 p-6 rounded-xl text-center">

<div class="text-4xl font-bold text-blue-700">

245

</div>

<p>

Views

</p>

</div>



<div class="bg-blue-50 p-6 rounded-xl text-center">

<div class="text-4xl font-bold text-blue-700">

87

</div>

<p>

Downloads

</p>

</div>



<div class="bg-blue-50 p-6 rounded-xl text-center">

<div class="text-4xl font-bold text-blue-700">

12

</div>

<p>

Citations

</p>

</div>



</div>
        <h2 class="text-3xl font-bold mb-6">
            PDF Preview
        </h2>

        <iframe
            src="<?= htmlspecialchars($article["pdf"]) ?>"
            width="100%"
            height="900"
            class="border rounded-lg">
        </iframe>

    </div>

</div>

</body>
</html>