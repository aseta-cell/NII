<?php
require_once "config.php";

$reviewer = require_role("reviewer");

$id = (int)($_GET["id"] ?? 0);

// Only the reviewer assigned to this manuscript may open it
$stmt = $conn->prepare("SELECT * FROM submissions WHERE id = ? AND reviewer_id = ?");
$stmt->bind_param("ii", $id, $reviewer["id"]);
$stmt->execute();

$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    http_response_code(404);
    die("Article not found or not assigned to you.");
}

$decisions = [
    "Accepted" => "Accept",
    "Revision" => "Revision Required",
    "Rejected" => "Reject",
];

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $comments = trim($_POST["comments"] ?? "");
    $status = $_POST["status"] ?? "";

    if ($comments === "" || !isset($decisions[$status])) {

        $message = "Please choose a decision and write your comments.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO reviews (submission_id, reviewer_id, reviewer_name, status, comments)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisss", $id, $reviewer["id"], $reviewer["fullname"], $status, $comments);
        $stmt->execute();

        // The manuscript takes the reviewer's decision
        $stmt = $conn->prepare("UPDATE submissions SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();

        // Special (family) account: accepted article needs no payment
        if ($status === "Accepted") {

            $stmt = $conn->prepare("SELECT free_access FROM users WHERE id = ?");
            $stmt->bind_param("i", $article["user_id"]);
            $stmt->execute();

            if ($stmt->get_result()->fetch_assoc()["free_access"]) {
                waive_fee($id, $article["user_id"]);
            }
        }

        redirect("reviewer-dashboard.php?reviewed=1");
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Review: <?= e($article["title"]) ?></title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

<div class="max-w-4xl mx-auto py-12 px-6">

    <a href="reviewer-dashboard.php" class="text-blue-600 hover:underline">
        ← Back to Reviewer Dashboard
    </a>

    <div class="bg-white rounded-xl shadow-lg p-10 mt-6">

        <h1 class="text-3xl font-bold mb-4">
            <?= e($article["title"]) ?>
        </h1>

        <p class="mb-2">
            <strong>Journal:</strong>
            <?= e($article["journal"]) ?>
        </p>

        <p class="mb-2">
            <strong>Current status:</strong>
            <?= e($article["status"]) ?>
        </p>

        <?php if (!empty($article["keywords"])): ?>
        <p class="mb-2">
            <strong>Keywords:</strong>
            <?= e($article["keywords"]) ?>
        </p>
        <?php endif; ?>

        <h2 class="text-xl font-bold mt-6 mb-2">Abstract</h2>

        <p class="text-gray-700 leading-7">
            <?= nl2br(e($article["abstract"])) ?>
        </p>

        <?php if (!empty($article["filename"])): ?>
        <a
            href="<?= e(upload_url($article["filename"])) ?>"
            target="_blank"
            class="inline-block mt-6 bg-blue-700 text-white px-6 py-3 rounded-lg hover:bg-blue-800">
            Open Manuscript
        </a>
        <?php endif; ?>

        <hr class="my-8">

        <h2 class="text-2xl font-bold mb-4">
            Submit Review
        </h2>

        <?php if ($message !== ""): ?>
        <p class="mb-4 p-3 rounded bg-red-100 text-red-800"><?= e($message) ?></p>
        <?php endif; ?>

        <form method="POST" class="space-y-4">

            <textarea
                name="comments"
                rows="8"
                placeholder="Write your comments for the author and the editor..."
                class="w-full border rounded-lg p-4"
                required><?= e($_POST["comments"] ?? "") ?></textarea>

            <select name="status" class="w-full border rounded-lg p-3" required>

                <option value="">Choose decision</option>

                <?php foreach ($decisions as $value => $label): ?>
                <option value="<?= e($value) ?>"><?= e($label) ?></option>
                <?php endforeach; ?>

            </select>

            <button type="submit" class="bg-blue-700 text-white px-6 py-3 rounded-lg hover:bg-blue-800">
                Send Review
            </button>

        </form>

    </div>

</div>

</body>
</html>
