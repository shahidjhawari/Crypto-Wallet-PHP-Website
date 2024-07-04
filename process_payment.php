<?php
session_start();
require('header.php'); // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Assuming the user is logged in and user_id is stored in the session
    $name = $_POST['name'];
    $payment_method = $_POST['payment_method'];
    $address = isset($_POST['address']) ? $_POST['address'] : null;
    $account_number = isset($_POST['account_number']) ? $_POST['account_number'] : null;
    $amount = $_POST['amount'];

    // Ensure all required fields are filled
    if (empty($name) || empty($payment_method) || empty($amount) || 
        ($payment_method == 'Dollar' && empty($address)) || 
        ($payment_method != 'Dollar' && empty($account_number))) {
        echo "Error: All fields are required.";
        exit();
    }

    // Check if the user has enough balance
    $stmt = $conn->prepare("SELECT SUM(amount) AS wallet_balance FROM deposits WHERE user_id = ? AND status = 'Accepted'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $wallet_balance = $stmt->get_result()->fetch_assoc()['wallet_balance'] ?? 0;
    $stmt->close();

    if ($amount > $wallet_balance) {
        echo "Error: Insufficient balance.";
        exit();
    }

    // Insert the payment request
    $stmt = $conn->prepare("INSERT INTO user_payments (user_id, name, account_number, payment_method, address, amount, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("issssd", $user_id, $name, $account_number, $payment_method, $address, $amount);
    $stmt->execute();
    $stmt->close();

    // Deduct the amount from the user's wallet
    $remaining_to_deduct = $amount;
    while ($remaining_to_deduct > 0) {
        $stmt = $conn->prepare("SELECT id, amount FROM deposits WHERE user_id = ? AND status = 'Accepted' AND amount > 0 ORDER BY id ASC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $deposit = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($deposit) {
            $deposit_id = $deposit['id'];
            $deposit_amount = $deposit['amount'];

            if ($deposit_amount >= $remaining_to_deduct) {
                $stmt = $conn->prepare("UPDATE deposits SET amount = amount - ? WHERE id = ?");
                $stmt->bind_param("di", $remaining_to_deduct, $deposit_id);
                $stmt->execute();
                $stmt->close();
                $remaining_to_deduct = 0;
            } else {
                $stmt = $conn->prepare("UPDATE deposits SET amount = 0 WHERE id = ?");
                $stmt->bind_param("i", $deposit_id);
                $stmt->execute();
                $stmt->close();
                $remaining_to_deduct -= $deposit_amount;
            }
        } else {
            break;
        }
    }

    echo "Payment request submitted successfully.";
} else {
    echo "Invalid request.";
}
?>
