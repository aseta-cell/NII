<?php

require_once "config.php";

require_login();

$user_id = $_SESSION["user_id"];
$id = (int)($_GET["id"] ?? 0);

// The article must belong to the signed-in author
$stmt = $conn->prepare("SELECT * FROM submissions WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    http_response_code(404);
    die("Article not found.");
}

// Latest payment for this article that is still open or already paid
$stmt = $conn->prepare("
    SELECT *
    FROM payments
    WHERE submission_id = ?
    AND status IN ('Pending', 'Awaiting Confirmation', 'Paid')
    ORDER BY id DESC
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();

$payment = $stmt->get_result()->fetch_assoc();

$can_pay = in_array($article["status"], ["Accepted", "Payment Pending"], true);

// Special (family) account: publication is free
$stmt = $conn->prepare("SELECT free_access FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$is_free = (bool)$stmt->get_result()->fetch_assoc()["free_access"];

$errors = [
    "plan"    => "Please choose a payment plan.",
    "status"  => "This article cannot be paid for right now.",
    "receipt" => "Please upload the receipt as PDF, JPG or PNG (max 10 MB).",
];
$error = $errors[$_GET["error"] ?? ""] ?? "";

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Payment | <?= e($site_name) ?></title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

<div class="max-w-5xl mx-auto py-12 px-6">

<a href="dashboard.php" class="text-blue-600 hover:underline">
    ← Back to Dashboard
</a>

<div class="bg-white rounded-xl shadow-lg p-10 mt-6">

<h1 class="text-4xl font-bold mb-6">
    Article Processing Charge
</h1>

<div class="bg-blue-50 p-6 rounded-lg mb-8">

    <h2 class="text-2xl font-bold">
        <?= e($article["title"]) ?>
    </h2>

    <p class="mt-3">
        Journal: <?= e($article["journal"]) ?>
    </p>

    <p class="mt-1">
        Status: <strong><?= e($article["status"]) ?></strong>
    </p>

</div>

<?php if ($error !== ""): ?>
<p class="mb-6 p-4 rounded bg-red-100 text-red-800"><?= e($error) ?></p>
<?php endif; ?>


<?php if ($payment && $payment["status"] === "Paid"): ?>

    <!-- Already paid -->

    <div class="p-6 rounded-lg bg-green-100 text-green-800">
        <?php if ($payment["plan"] === "free"): ?>
            <p class="text-xl font-bold">Free publication</p>
            <p class="mt-2">
                Your account has special access, so there is nothing to pay.
                Your article will be published by the editorial office.
            </p>
        <?php else: ?>
            <p class="text-xl font-bold">Payment confirmed</p>
            <p class="mt-2">
                <?= e(number_format((float)$payment["amount"], 2) . " " . $payment["currency"]) ?>
                received<?= $payment["paid_at"] ? " on " . e(date("d M Y", strtotime($payment["paid_at"]))) : "" ?>.
                Your article will be published by the editorial office.
            </p>
        <?php endif; ?>
    </div>

<?php elseif ($can_pay && $is_free): ?>

    <!-- Special account: no fee -->

    <div class="p-6 rounded-lg bg-green-50 border border-green-300">

        <p class="text-xl font-bold text-green-800">Special access — free for you</p>

        <p class="mt-2 text-gray-700">
            Your account has special access, so the publication fee is waived.
        </p>

        <form action="process_payment.php" method="POST" class="mt-6">
            <input type="hidden" name="action" value="free">
            <input type="hidden" name="article_id" value="<?= (int)$article["id"] ?>">
            <button type="submit" class="bg-green-700 text-white px-6 py-3 rounded-lg hover:bg-green-800">
                Confirm free publication
            </button>
        </form>

    </div>

<?php elseif ($payment): ?>

    <!-- Payment created: show details and receipt upload -->

    <h2 class="text-3xl font-bold mb-4">
        Payment #<?= (int)$payment["id"] ?>
    </h2>

    <p class="text-lg mb-6">
        Plan: <strong><?= e($settings["plans"][$payment["plan"]]["name"] ?? $payment["plan"]) ?></strong> ·
        Amount: <strong><?= e(number_format((float)$payment["amount"], 2) . " " . $payment["currency"]) ?></strong>
    </p>

    <div class="border rounded-lg p-6 mb-8">
        <h3 class="text-xl font-bold mb-3">How to pay</h3>
        <p class="whitespace-pre-line text-gray-700"><?= e($settings["payment_instructions"]) ?></p>
        <p class="mt-4 text-gray-700">
            Payment comment: <strong>APC #<?= (int)$payment["id"] ?></strong>
        </p>
    </div>

    <?php if ($payment["status"] === "Awaiting Confirmation"): ?>

        <div class="p-4 rounded bg-yellow-100 text-yellow-800 mb-6">
            Receipt received. The editorial office will confirm your payment soon.
            You can upload a new receipt if needed.
        </div>

    <?php endif; ?>

    <form action="process_payment.php" method="POST" enctype="multipart/form-data" class="space-y-4">

        <input type="hidden" name="action" value="receipt">
        <input type="hidden" name="payment_id" value="<?= (int)$payment["id"] ?>">

        <label class="block font-semibold">
            Upload payment receipt (PDF, JPG or PNG)
        </label>

        <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png" required class="block w-full border rounded-lg p-3">

        <button type="submit" class="bg-blue-700 text-white px-6 py-3 rounded-lg hover:bg-blue-800">
            Send receipt
        </button>

    </form>

<?php elseif ($can_pay): ?>

    <!-- Choose plan -->

    <h2 class="text-3xl font-bold mb-6">
        Choose Payment Plan
    </h2>

    <div class="grid md:grid-cols-3 gap-6">

    <?php foreach ($settings["plans"] as $key => $plan): ?>

        <?php if ($plan["amount"] === null): ?>

        <div class="border rounded-xl p-6 text-center">

            <h3 class="text-xl font-bold">
                <?= e($plan["name"]) ?>
            </h3>

            <p class="text-4xl font-bold text-blue-700 mt-4">
                Contact
            </p>

            <a href="<?= e($plan["link"] ?: "about.html") ?>" class="inline-block mt-6 border border-blue-700 text-blue-700 px-6 py-3 rounded-lg">
                Contact us
            </a>

        </div>

        <?php continue; endif; ?>

        <form action="process_payment.php" method="POST"
              class="border rounded-xl p-6 text-center <?= $key === "professional" ? "border-2 border-blue-700" : "" ?>">

            <input type="hidden" name="action" value="create">
            <input type="hidden" name="article_id" value="<?= (int)$article["id"] ?>">
            <input type="hidden" name="plan" value="<?= e($key) ?>">

            <h3 class="text-xl font-bold">
                <?= e($plan["name"]) ?>
            </h3>

            <p class="text-4xl font-bold text-blue-700 mt-4">
                <?= e(number_format((float)$plan["amount"], 0, ".", " ") . " " . $settings["currency"]) ?>
            </p>

            <button type="submit" class="mt-6 bg-blue-700 text-white px-6 py-3 rounded-lg hover:bg-blue-800">
                Pay Now
            </button>

        </form>

    <?php endforeach; ?>

    </div>

<?php else: ?>

    <div class="p-6 rounded-lg bg-gray-100 text-gray-700">
        Payment becomes available after your article is accepted by the reviewers.
    </div>

<?php endif; ?>

</div>

</div>

</body>

</html>
