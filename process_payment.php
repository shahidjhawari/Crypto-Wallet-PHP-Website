<?php
session_start();
require('header.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Assuming the user is logged in and user_id is stored in the session
    $name = $_POST['name'];
    $payment_method = $_POST['payment_method'];
    $address = isset($_POST['address']) ? $_POST['address'] : null;
    $account_number = isset($_POST['account_number']) ? $_POST['account_number'] : null;
    $amount = $_POST['amount'];
    $random_string = $_POST['random_string'];

    // Ensure all required fields are filled
    if (
        empty($name) || empty($payment_method) || empty($amount) ||
        ($payment_method == 'Dollar' && empty($address)) ||
        ($payment_method != 'Dollar' && empty($account_number)) || empty($random_string)
    ) {
        echo "Error: All fields are required.";
        exit();
    }

    // Check if the provided random string matches the one in the users table
    $stmt = $conn->prepare("SELECT random_string FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stored_random_string = $stmt->get_result()->fetch_assoc()['random_string'] ?? '';
    $stmt->close();

    if ($random_string !== $stored_random_string) {
        echo "Error: Invalid random string.";
        exit();
    }

    // Check if the user has enough balance
    $stmt = $conn->prepare("SELECT SUM(amount) AS wallet_balance FROM deposits WHERE user_id = ? AND status = 'Accepted'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $wallet_balance = $stmt->get_result()->fetch_assoc()['wallet_balance'] ?? 0;
    $stmt->close();

    // Calculate fee
    if ($payment_method === "USDTP" || $payment_method === "Dollar") {
        $fee = 0.01 * $amount;
    } else {
        $fee = 0.03 * $amount;
    }
    $total_amount = $amount + $fee;

    if ($total_amount > $wallet_balance) {
        echo "Error: Insufficient balance.";
        exit();
    }

    // Insert the payment request
    $stmt = $conn->prepare("INSERT INTO user_payments (user_id, name, account_number, payment_method, address, amount, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("issssd", $user_id, $name, $account_number, $payment_method, $address, $amount);
    $stmt->execute();
    $stmt->close();

    echo "Payment request submitted successfully.";
} else {
    echo "Invalid request.";
}
