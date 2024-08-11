<?php
ob_start();
session_start();
require('header.php'); // Include database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $payment_method = $_POST['payment_method'];
    $address = isset($_POST['address']) ? $_POST['address'] : null;
    $account_number = isset($_POST['account_number']) ? $_POST['account_number'] : null;
    $amount = $_POST['amount'];
    $random_string = $_POST['random_string'];

    // Ensure all required fields are filled
    if (
        empty($name) || empty($payment_method) || empty($amount) ||
        ($payment_method === 'Dollar' && empty($address)) ||
        ($payment_method !== 'Dollar' && empty($account_number)) || empty($random_string)
    ) {
        $_SESSION['error'] = "Error: All fields are required.";
        header("Location: user_payment.php");
        exit();
    }

    // Check if the provided random string matches the one in the users table
    $stmt = $conn->prepare("SELECT random_string FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stored_random_string = $stmt->get_result()->fetch_assoc()['random_string'] ?? '';
    $stmt->close();

    if ($random_string !== $stored_random_string) {
        $_SESSION['error'] = "Error: Invalid random string.";
        header("Location: user_payment.php");
        exit();
    }

    // Check if the user has enough balance
    $stmt = $conn->prepare("SELECT SUM(amount) AS wallet_balance FROM deposits WHERE user_id = ? AND status = 'Accepted'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $wallet_balance = $stmt->get_result()->fetch_assoc()['wallet_balance'] ?? 0;
    $stmt->close();

    // Calculate fee
    $fee = $payment_method === "Dollar" ? 0.03 * $amount : 0.05 * $amount; // 3% for Dollar, 5% for others
    $total_amount = $amount + $fee;

    if ($total_amount > $wallet_balance) {
        $_SESSION['error'] = "Error: Insufficient balance.";
        header("Location: user_payment.php");
        exit();
    }

    // Insert the payment request
    $stmt = $conn->prepare("INSERT INTO user_payments (user_id, name, account_number, payment_method, address, amount, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("issssd", $user_id, $name, $account_number, $payment_method, $address, $amount);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Payment request submitted successfully.";
    header("Location: user_payment.php?submitted=true");
    exit();
} else {
    echo "Invalid request.";
}
