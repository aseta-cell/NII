<?php

// Payment actions from payment.php:
//   action=create  — author picked a plan: create a Pending payment
//   action=receipt — author uploaded a transfer receipt
//   action=free    — special (family) account: fee waived, no payment needed

require_once "config.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("billing.php");
}

$user_id = $_SESSION["user_id"];
$action = $_POST["action"] ?? "";


if ($action === "create") {

    $article_id = (int)($_POST["article_id"] ?? 0);
    $plan = $_POST["plan"] ?? "";

    // Amount always comes from the server config, never from the form
    if (!isset($settings["plans"][$plan]) || $settings["plans"][$plan]["amount"] === null) {
        redirect("payment.php?id=" . $article_id . "&error=plan");
    }

    $stmt = $conn->prepare("SELECT status FROM submissions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $article_id, $user_id);
    $stmt->execute();
    $article = $stmt->get_result()->fetch_assoc();

    if (!$article) {
        redirect("payment_failed.php");
    }

    if (!in_array($article["status"], ["Accepted", "Payment Pending"], true)) {
        redirect("payment.php?id=" . $article_id . "&error=status");
    }

    // Do not create a second open payment for the same article
    $stmt = $conn->prepare("
        SELECT id FROM payments
        WHERE submission_id = ?
        AND status IN ('Pending', 'Awaiting Confirmation', 'Paid')
    ");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();

    if ($stmt->get_result()->num_rows === 0) {

        $amount = $settings["plans"][$plan]["amount"];
        $currency = $settings["currency"];
        $method = "Bank transfer";

        $stmt = $conn->prepare("
            INSERT INTO payments (user_id, submission_id, plan, amount, currency, method, status)
            VALUES (?, ?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt->bind_param("iisdss", $user_id, $article_id, $plan, $amount, $currency, $method);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE submissions SET status = 'Payment Pending' WHERE id = ?");
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
    }

    redirect("payment.php?id=" . $article_id);
}


if ($action === "free") {

    $article_id = (int)($_POST["article_id"] ?? 0);

    $stmt = $conn->prepare("SELECT free_access FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    if (!$stmt->get_result()->fetch_assoc()["free_access"]) {
        redirect("payment.php?id=" . $article_id . "&error=status");
    }

    $stmt = $conn->prepare("SELECT status FROM submissions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $article_id, $user_id);
    $stmt->execute();
    $article = $stmt->get_result()->fetch_assoc();

    if (!$article || !in_array($article["status"], ["Accepted", "Payment Pending"], true)) {
        redirect("payment.php?id=" . $article_id . "&error=status");
    }

    waive_fee($article_id, $user_id);

    redirect("payment.php?id=" . $article_id);
}


if ($action === "receipt") {

    $payment_id = (int)($_POST["payment_id"] ?? 0);

    $stmt = $conn->prepare("
        SELECT * FROM payments
        WHERE id = ? AND user_id = ?
        AND status IN ('Pending', 'Awaiting Confirmation')
    ");
    $stmt->bind_param("ii", $payment_id, $user_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();

    if (!$payment) {
        redirect("payment_failed.php");
    }

    $receipt = save_upload("receipt", ["pdf", "jpg", "jpeg", "png"], 10);

    if ($receipt === null) {
        redirect("payment.php?id=" . $payment["submission_id"] . "&error=receipt");
    }

    $stmt = $conn->prepare("
        UPDATE payments
        SET receipt_file = ?, status = 'Awaiting Confirmation'
        WHERE id = ?
    ");
    $stmt->bind_param("si", $receipt, $payment_id);
    $stmt->execute();

    redirect("payment_success.php?payment=" . $payment_id);
}


redirect("payment_failed.php");
