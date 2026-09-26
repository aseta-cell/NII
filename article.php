<?php
require_once "config.php";

$id = (int)($_GET["id"] ?? 0);

$stmt = $conn->prepare("
    SELECT
        submissions.*,
        users.fullname AS author
    FROM submissions
    JOIN users
    ON submissions.user_id = users.id
    WHERE submissions.id = ?
    AND submissions.status = 'Published'
");
$stmt->bind_param("i", $id);
$stmt->execute();

$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    http_response_code(404);
    die("Article not found.");
}

// Count this view
$stmt = $conn->prepare("UPDATE submissions SET views = views + 1 WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$article["views"]++;

$pdf = !empty($article["filename"]) ? upload_url($article["filename"]) : "";
$download = "download.php?id=" . (int)$article["id"];
$is_pdf = strtolower(pathinfo((string)$article["filename"], PATHINFO_EXTENSION)) === "pdf";
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= e($article["title"]) ?></title>

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
            <?= e($article["title"]) ?>
        </h1>

        <div class="grid md:grid-cols-2 gap-6 text-lg">

            <div>

                <p class="mb-3">
                    <strong>Authors:</strong><br>
                    <?= e($article["author"]) ?>
                </p>

                <p class="mb-3">
                    <strong>Journal:</strong><br>
                    <?= e($article["journal"]) ?>
                </p>

                <p class="mb-3">

<strong>DOI:</strong><br>

<?php if (!empty($article["doi"])): ?>
<a
href="https://doi.org/<?= e($article["doi"]) ?>"
target="_blank"
class="text-blue-700 hover:underline">

<?= e($article["doi"]) ?>

</a>
<?php else: ?>
—
<?php endif; ?>

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
            <?= nl2br(e($article["abstract"])) ?>
        </p>

        <hr class="my-8">


<h2 class="text-3xl font-bold mb-4">

How to cite this article

</h2>


<div class="bg-gray-100 p-6 rounded-lg">


<p class="text-gray-700">

<?= e($article["author"]) ?>.

<?= e($article["title"]) ?>.

<i>
<?= e($article["journal"]) ?>
</i>.

<?= date("Y", strtotime($article["created_at"])) ?>.


<?php if (!empty($article["doi"])): ?>
DOI: <?= e($article["doi"]) ?>
<?php endif; ?>

</p>


</div>

        <?php if (!empty($article["keywords"])): ?>
        <h2 class="text-2xl font-bold mt-8 mb-3">
            Keywords
        </h2>

        <p class="text-gray-700">
            <?= e($article["keywords"]) ?>
        </p>
        <?php endif; ?>

        <hr class="my-8">

        <div class="flex gap-4 mb-8">

            <?php if ($pdf !== ""): ?>
            <a
                href="<?= e($download) ?>"
                target="_blank"
                class="bg-blue-700 text-white px-6 py-3 rounded-lg hover:bg-blue-800">

                Download PDF

            </a>
            <?php endif; ?>

            <a href="archive.php?journal=<?= urlencode($article["journal"]) ?>"
class="text-blue-600 hover:underline">

← Back to Archive

</a>
            

        </div>
<hr class="my-8">

<h2 class="text-3xl font-bold mb-6">
    Article Metrics
</h2>

<div class="grid md:grid-cols-2 gap-6 mb-10">

    <div class="bg-blue-50 p-6 rounded-xl text-center">
        <div class="text-4xl font-bold text-blue-700"><?= (int)$article["views"] ?></div>
        <p>Views</p>
    </div>

    <div class="bg-blue-50 p-6 rounded-xl text-center">
        <div class="text-4xl font-bold text-blue-700"><?= (int)$article["downloads"] ?></div>
        <p>Downloads</p>
    </div>

</div>

<?php if ($pdf !== "" && $is_pdf): ?>
        <h2 class="text-3xl font-bold mb-6">
            PDF Preview
        </h2>

        <iframe
            src="<?= e($pdf) ?>"
            width="100%"
            height="900"
            class="border rounded-lg">
        </iframe>
<?php endif; ?>

    </div>

</div>

</body>
</html>