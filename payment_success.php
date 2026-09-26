<?php
require_once "config.php";

require_login();

$payment_id = (int)($_GET["payment"] ?? 0);

$stmt = $conn->prepare("
    SELECT payments.*, submissions.title
    FROM payments
    JOIN submissions ON payments.submission_id = submissions.id
    WHERE payments.id = ? AND payments.user_id = ?
");
$stmt->bind_param("ii", $payment_id, $_SESSION["user_id"]);
$stmt->execute();

$payment = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment received | <?= e($site_name) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-2xl mx-auto py-20 px-6">

    <div class="bg-white rounded-xl shadow-lg p-10 text-center">

        <div class="text-6xl mb-4">✅</div>

        <h1 class="text-3xl font-bold mb-4">Thank you!</h1>

        <?php if ($payment): ?>
            <p class="text-gray-700">
                We received your receipt for payment #<?= (int)$payment["id"] ?>
                (<?= e(number_format((float)$payment["amount"], 2) . " " . $payment["currency"]) ?>)
                for “<?= e($payment["title"]) ?>”.
            </p>
        <?php endif; ?>

        <p class="text-gray-700 mt-3">
            The editorial office will check it and confirm the payment. You will see the new status in your dashboard.
        </p>

        <div class="mt-8 flex justify-center gap-4">
            <a href="dashboard.php" class="bg-blue-700 text-white px-6 py-3 rounded-lg">Go to Dashboard</a>
            <a href="billing.php" class="border border-blue-700 text-blue-700 px-6 py-3 rounded-lg">Billing</a>
        </div>

    </div>

</div>

</body>
</html>
