<?php
require_once "config.php";

require_login();

$user_id = $_SESSION["user_id"];

// Articles that are waiting for payment
$stmt = $conn->prepare("
    SELECT id, title, journal, status
    FROM submissions
    WHERE user_id = ?
    AND status IN ('Accepted', 'Payment Pending')
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$to_pay = $stmt->get_result();

// Payment history
$stmt = $conn->prepare("
    SELECT payments.*, submissions.title
    FROM payments
    JOIN submissions ON payments.submission_id = submissions.id
    WHERE payments.user_id = ?
    ORDER BY payments.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history = $stmt->get_result();

$status_colors = [
    "Paid" => "bg-green-100 text-green-800",
    "Awaiting Confirmation" => "bg-yellow-100 text-yellow-800",
    "Pending" => "bg-blue-100 text-blue-800",
    "Rejected" => "bg-red-100 text-red-800",
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Billing | <?= e($site_name) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-5xl mx-auto py-12 px-6">

    <div class="flex flex-wrap gap-4 justify-between items-center">
        <a href="dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
        <a href="account-settings.php" class="text-blue-600 hover:underline">Account Settings</a>
    </div>

    <h1 class="text-4xl font-bold mt-6 mb-2">Billing & Payments</h1>
    <p class="text-gray-600 mb-8">Publication fees for your accepted articles and payment history.</p>

    <section class="bg-white rounded-xl shadow p-8 mb-8">

        <h2 class="text-2xl font-bold mb-4">Waiting for payment</h2>

        <?php if ($to_pay->num_rows === 0): ?>
            <p class="text-gray-500">Nothing to pay right now.</p>
        <?php endif; ?>

        <?php while ($row = $to_pay->fetch_assoc()): ?>
            <div class="flex flex-wrap justify-between items-center gap-4 border-b py-4">
                <div>
                    <p class="font-bold"><?= e($row["title"]) ?></p>
                    <p class="text-gray-500 text-sm"><?= e($row["journal"]) ?> · <?= e($row["status"]) ?></p>
                </div>
                <a href="payment.php?id=<?= (int)$row["id"] ?>" class="bg-blue-700 text-white px-5 py-2 rounded-lg">
                    <?= $row["status"] === "Payment Pending" ? "Continue payment" : "Pay now" ?>
                </a>
            </div>
        <?php endwhile; ?>

    </section>

    <section class="bg-white rounded-xl shadow p-8">

        <h2 class="text-2xl font-bold mb-4">Payment history</h2>

        <?php if ($history->num_rows === 0): ?>
            <p class="text-gray-500">No payments yet.</p>
        <?php else: ?>

        <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b text-gray-500 text-sm">
                    <th class="py-3 pr-4">#</th>
                    <th class="py-3 pr-4">Article</th>
                    <th class="py-3 pr-4">Amount</th>
                    <th class="py-3 pr-4">Status</th>
                    <th class="py-3">Date</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($p = $history->fetch_assoc()): ?>
                <tr class="border-b">
                    <td class="py-3 pr-4"><?= (int)$p["id"] ?></td>
                    <td class="py-3 pr-4">
                        <a href="payment.php?id=<?= (int)$p["submission_id"] ?>" class="text-blue-700 hover:underline"><?= e($p["title"]) ?></a>
                    </td>
                    <td class="py-3 pr-4 whitespace-nowrap"><?= $p["plan"] === "free" ? "Free" : e(number_format((float)$p["amount"], 2) . " " . $p["currency"]) ?></td>
                    <td class="py-3 pr-4">
                        <span class="px-3 py-1 rounded-full text-sm <?= $status_colors[$p["status"]] ?? "bg-gray-100" ?>"><?= e($p["status"]) ?></span>
                    </td>
                    <td class="py-3 whitespace-nowrap"><?= e(date("d.m.Y", strtotime($p["created_at"]))) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>

        <?php endif; ?>

    </section>

</div>

</body>
</html>
