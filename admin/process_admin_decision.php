<?php
ob_start();
session_start();
require('top.inc.php');

// Ensure only admin can access this script
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

        // If rejected, credit the exact amount back to the user's deposit
        if ($status === 'Rejected') {
            // Start a transaction
            $con->begin_transaction();

            try {
                // Check the current deposit amount before updating
                $stmt = $con->prepare("SELECT amount FROM deposits WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $deposit = $result->fetch_assoc();
                $stmt->close();

                if ($deposit) {
                    // Update the deposit with the correct amount
                    $stmt = $con->prepare("UPDATE deposits SET amount = ? WHERE user_id = ?");
                    $new_amount = $deposit['amount'] + $amount;
                    $stmt->bind_param("di", $new_amount, $user_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // If there's no deposit record, insert a new one
                    $stmt = $con->prepare("INSERT INTO deposits (user_id, amount) VALUES (?, ?)");
                    $stmt->bind_param("id", $user_id, $amount);
                    $stmt->execute();
                    $stmt->close();
                }

                // Commit the transaction
                $con->commit();
            } catch (Exception $e) {
                // Rollback the transaction in case of error
                $con->rollback();
                echo "Error: " . $e->getMessage();
                exit();
            }
        }

        header("Location: admin_manage_payments.php");
    } else {
        echo "Error: Payment request not found.";
    }
} else {
    echo "Invalid request.";
}
?>
