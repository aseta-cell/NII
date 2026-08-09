<?php
require_once "config.php";

if(!isset($_GET["journal"])){
    die("Journal not found.");
}

$journal = $conn->real_escape_string($_GET["journal"]);

$sql = "
SELECT
    submissions.*,
    users.fullname AS author
FROM submissions
JOIN users
ON submissions.user_id = users.id
WHERE submissions.status='Published'
AND submissions.journal='$journal'
ORDER BY submissions.created_at DESC
";

$result = $conn->query($sql);

$description = "";
$editor = "";
$scope = "";

switch($journal){

    case "Engineering & Tech":

        $description = "The Journal of Engineering & Technology publishes original research in civil engineering, mining, artificial intelligence, robotics, materials science and industrial innovation.";

        $editor = "Prof. Gulshat Sarsenova";

        $scope = "
        • Civil Engineering<br>
        • Mechanical Engineering<br>
        • Mining Engineering<br>
        • Artificial Intelligence<br>
        • Robotics<br>
        • Materials Science
        ";

        break;

    case "Artificial Intelligence":

        $description = "International journal covering artificial intelligence, machine learning, computer vision and data science.";

        $editor = "Dr. Aiganym Aset";

        $scope = "
        • Machine Learning<br>
        • Deep Learning<br>
        • Computer Vision<br>
        • NLP<br>
        • Robotics
        ";

        break;

    case "Clinical Medicine":

        $description = "Peer-reviewed journal publishing medical research, healthcare innovation and clinical studies.";

        $editor = "Dr. Sarah Johnson";

        $scope = "
        • Internal Medicine<br>
        • Surgery<br>
        • Public Health<br>
        • Oncology
        ";

        break;

    default:

        $description = "International peer-reviewed scientific journal.";

        $editor = "Editorial Office";

        $scope = "All scientific disciplines.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title><?= htmlspecialchars($journal) ?></title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 font-sans">
<div class="max-w-7xl mx-auto py-10 px-6">

<div class="grid lg:grid-cols-3 gap-8">

    <!-- Левая часть -->
    <div class="lg:col-span-2">

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">

            <div class="bg-gradient-to-r from-blue-900 to-blue-700 p-10 text-white">

                <div class="flex justify-between items-start">

                    <div>

                        <h1 class="text-5xl font-bold">

                            <?= htmlspecialchars($journal) ?>

                        </h1>

                        <p class="mt-4 text-lg opacity-90">

                            International Peer-Reviewed Open Access Journal

                        </p>

                    </div>

                    <div>

                        <div class="w-36 h-48 bg-white rounded-lg flex items-center justify-center text-blue-900 font-bold text-center shadow">

                            Journal Cover

                        </div>

                    </div>

                </div>

            </div>

            <div class="p-8">

                <div class="grid md:grid-cols-4 gap-6 text-center">

                    <div>

                        <div class="text-gray-500">

                            Impact Factor

                        </div>

                        <div class="text-3xl font-bold text-blue-700">

                            5.8

                        </div>

                    </div>

                    <div>

                        <div class="text-gray-500">

                            CiteScore

                        </div>

                        <div class="text-3xl font-bold text-blue-700">

                            7.4

                        </div>

                    </div>

                    <div>

                        <div class="text-gray-500">

                            Quartile

                        </div>

                        <div class="text-3xl font-bold text-green-600">

                            Q1

                        </div>

                    </div>

                    <div>

                        <div class="text-gray-500">

                            ISSN

                        </div>

                        <div class="font-bold">

                            2754-1021

                        </div>

                    </div>

                </div>

                <div class="flex gap-4 mt-8">

                    <a href="submit.php"
                    class="bg-blue-700 text-white px-6 py-3 rounded-lg hover:bg-blue-800">

                        Submit Manuscript

                    </a>

                    <a href="archive.php?journal=<?= urlencode($journal) ?>"
                    class="border border-blue-700 text-blue-700 px-6 py-3 rounded-lg">

                        Archive

                    </a>

                </div>

            </div>

        </div>

    </div>

    <!-- Правая колонка -->

    <div>

        <div class="bg-white rounded-xl shadow-lg p-6">

            <h2 class="text-2xl font-bold mb-6">

                Journal Information

            </h2>

            <div class="space-y-4">

                <div>

                    <strong>Publisher</strong>

                    <br>

                    Scholarly Archive

                </div>

                <div>

                    <strong>Language</strong>

                    <br>

                    English

                </div>

                <div>

                    <strong>Publication Frequency</strong>

                    <br>

                    Quarterly

                </div>

                <div>

                    <strong>Open Access</strong>

                    <br>

                    Yes

                </div>

                <div>

                    <strong>Peer Review</strong>

                    <br>

                    Double Blind

                </div>

            </div>

        </div>

    </div>

</div>
<div class="bg-white rounded-lg shadow p-8 mb-8">

<h2 class="text-3xl font-bold mb-4">

About

</h2>

<p>

<?= $description ?>

</p>

</div>
<div class="bg-white rounded-lg shadow p-8 mb-8">

<h2 class="text-3xl font-bold mb-4">

Editorial Board

</h2>

<p>

<strong>Editor-in-Chief:</strong>

<?= $editor ?>

</p>

</div>
<div class="bg-white rounded-lg shadow p-8 mb-8">

<h2 class="text-3xl font-bold mb-4">

Aims & Scope

</h2>

<p>

<?= $scope ?>

</p>

</div>
<div class="bg-white rounded-lg shadow p-8">

<h2 class="text-3xl font-bold mb-6">

Published Articles

</h2>
<?php if($result->num_rows==0): ?>

<p>No published articles.</p>

<?php else: ?>
    <?php while($row=$result->fetch_assoc()): ?>

<div class="border rounded-lg p-6 mb-6">

<h3 class="text-2xl font-bold">

<?= htmlspecialchars($row["title"]) ?>

</h3>

<p class="mt-2">

<strong>Author:</strong>

<?= htmlspecialchars($row["author"]) ?>

</p>

<p>

<strong>DOI:</strong>

<?= htmlspecialchars($row["doi"]) ?>

</p>

<p>

<strong>Published:</strong>

<?= date("d M Y",strtotime($row["created_at"])) ?>

</p>

<p class="mt-4">

<?= htmlspecialchars(substr($row["abstract"],0,250)) ?>...

</p>

<div class="mt-5 flex gap-3">

<a
href="article.php?id=<?= $row["id"] ?>"
class="bg-blue-700 text-white px-5 py-2 rounded">

View Article

</a>

<a
href="<?= htmlspecialchars($row["pdf"]) ?>"
target="_blank"
class="border border-blue-700 text-blue-700 px-5 py-2 rounded">

PDF

</a>

</div>

</div>

<?php endwhile; ?>

<?php endif; ?>
</div>

</body>

</html>