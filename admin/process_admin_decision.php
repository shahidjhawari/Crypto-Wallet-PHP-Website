<?php
ob_start();
session_start();
require('top.inc.php');

// Ensure only admin can access this script
// Uncomment these lines if you have role-based access control in your application
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: index.php");
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Validate input
    if (empty($id) || empty($status)) {
        echo "Error: All fields are required.";
        exit();
    }

    // Get the payment request details
    $stmt = $con->prepare("SELECT user_id, amount FROM user_payments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    $stmt->close();

    if ($payment) {
        $user_id = $payment['user_id'];
        $amount = $payment['amount'];

        // Update the status of the payment request
        $stmt = $con->prepare("UPDATE user_payments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();

        if ($status === 'Accepted') {
            // Deduct the amount from the user's wallet
            $remaining_to_deduct = $amount;
            while ($remaining_to_deduct > 0) {
                $stmt = $con->prepare("SELECT id, amount FROM deposits WHERE user_id = ? AND status = 'Accepted' AND amount > 0 ORDER BY id ASC LIMIT 1");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $deposit = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($deposit) {
                    $deposit_id = $deposit['id'];
                    $deposit_amount = $deposit['amount'];

                    if ($deposit_amount >= $remaining_to_deduct) {
                        $stmt = $con->prepare("UPDATE deposits SET amount = amount - ? WHERE id = ?");
                        $stmt->bind_param("di", $remaining_to_deduct, $deposit_id);
                        $stmt->execute();
                        $stmt->close();
                        $remaining_to_deduct = 0;
                    } else {
                        $stmt = $con->prepare("UPDATE deposits SET amount = 0 WHERE id = ?");
                        $stmt->bind_param("i", $deposit_id);
                        $stmt->execute();
                        $stmt->close();
                        $remaining_to_deduct -= $deposit_amount;
                    }
                } else {
                    break;
                }
            }
        } elseif ($status === 'Rejected') {
            // No need to do anything if the request is rejected
        }

        header("Location: admin_manage_payments.php");
    } else {
        echo "Error: Payment request not found.";
    }
} else {
    echo "Invalid request.";
}
