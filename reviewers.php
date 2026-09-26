<?php
require_once "config.php";

$role = $_SESSION["role"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reviewers | <?= e($site_name) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<nav class="bg-white border-b">
    <div class="max-w-5xl mx-auto px-6 py-4 flex flex-wrap gap-6 items-center">
        <a href="about.html" class="font-bold text-lg"><?= e($site_name) ?></a>
        <a href="journals.html" class="text-gray-600 hover:text-black">Journals</a>
        <a href="publish.php" class="text-gray-600 hover:text-black">Publish</a>
        <a href="authors.html" class="text-gray-600 hover:text-black">Authors</a>
        <a href="reviewers.php" class="font-bold">Reviewers</a>
        <a href="archive.php" class="text-gray-600 hover:text-black">Archive</a>
    </div>
</nav>

<div class="max-w-5xl mx-auto py-12 px-6">

    <div class="bg-white rounded-xl shadow p-10">

        <h1 class="text-4xl font-bold mb-4">For Reviewers</h1>

        <p class="text-gray-700 leading-7">
            Our journals follow a peer-review process. Every manuscript is assigned by the editor
            to an expert reviewer, who reads the paper and recommends to accept it, ask for revision, or reject it.
        </p>

        <h2 class="text-2xl font-bold mt-8 mb-3">How it works</h2>

        <ol class="list-decimal pl-6 space-y-2 text-gray-700">
            <li>Register as a reviewer using the access code from the editorial office.</li>
            <li>The editor assigns manuscripts to you — they appear in your reviewer dashboard.</li>
            <li>Download the manuscript, write your comments and choose a decision.</li>
            <li>The author sees your comments; the editor makes the final decision.</li>
        </ol>

        <h2 class="text-2xl font-bold mt-8 mb-3">Reviewer guidelines</h2>

        <ul class="list-disc pl-6 space-y-2 text-gray-700">
            <li>Keep manuscripts confidential.</li>
            <li>Declare any conflict of interest to the editor.</li>
            <li>Give constructive, specific comments.</li>
            <li>Complete reviews on time.</li>
        </ul>

        <div class="mt-10 flex flex-wrap gap-4">
            <?php if ($role === "reviewer"): ?>
                <a href="reviewer-dashboard.php" class="bg-blue-700 text-white px-6 py-3 rounded-lg">Open Reviewer Dashboard</a>
            <?php else: ?>
                <a href="register.php" class="bg-blue-700 text-white px-6 py-3 rounded-lg">Become a Reviewer</a>
                <a href="signin.php" class="border border-blue-700 text-blue-700 px-6 py-3 rounded-lg">Sign In</a>
            <?php endif; ?>
        </div>

    </div>

</div>

</body>
</html>
