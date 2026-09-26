<?php
require_once "config.php";

require_login();
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment problem | <?= e($site_name) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-2xl mx-auto py-20 px-6">

    <div class="bg-white rounded-xl shadow-lg p-10 text-center">

        <div class="text-6xl mb-4">⚠️</div>

        <h1 class="text-3xl font-bold mb-4">Payment could not be processed</h1>

        <p class="text-gray-700">
            The payment was not found or is already closed. Please open your article from the dashboard and try again,
            or contact the editorial office.
        </p>

        <div class="mt-8 flex justify-center gap-4">
            <a href="dashboard.php" class="bg-blue-700 text-white px-6 py-3 rounded-lg">Go to Dashboard</a>
            <a href="billing.php" class="border border-blue-700 text-blue-700 px-6 py-3 rounded-lg">Billing</a>
        </div>

    </div>

</div>

</body>
</html>
