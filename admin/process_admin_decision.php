<?php
ob_start();
require('top.inc.php');

// Ensure only admin can access this script
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];  // Make sure this line is correct
    $status = $_POST['status'];
    $rejection_reason = $_POST['rejection_reason'] ?? null;

    // Validate input
    if (empty($id) || empty($status)) {
        echo "Error: All fields are required.";
        exit();
    }

    // Get the payment request details
    $stmt = $con->prepare("SELECT user_id, amount, payment_method FROM user_payments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    $stmt->close();

    if ($payment) {
        $user_id = $payment['user_id'];
        $amount = $payment['amount'];
        $payment_method = $payment['payment_method'];

        // Calculate the fee based on the payment method
        if ($payment_method === "USDTP" || $payment_method === "Dollar") {
            $fee = 0.01 * $amount;
        } else {
            $fee = 0.03 * $amount;
        }
        $total_amount = $amount + $fee;

        // Update the status of the payment request
        $stmt = $con->prepare("UPDATE user_payments SET status = ?, rejection_reason = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $rejection_reason, $id);

        if ($stmt->execute()) {
            $stmt->close();

            if ($status === 'Accepted') {
                // Deduct the total amount (amount + fee) from the user's wallet
                $remaining_to_deduct = $total_amount;
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
                            if ($stmt->execute()) {
                                $remaining_to_deduct = 0;
                            } else {
                                error_log("Error updating deposit: " . $stmt->error);
                            }
                            $stmt->close();
                        } else {
                            $stmt = $con->prepare("UPDATE deposits SET amount = 0 WHERE id = ?");
                            $stmt->bind_param("i", $deposit_id);
                            if ($stmt->execute()) {
                                $remaining_to_deduct -= $deposit_amount;
                            } else {
                                error_log("Error updating deposit: " . $stmt->error);
                            }
                            $stmt->close();
                        }
                    } else {
                        break;
                    }
                }
            } elseif ($status === 'Rejected') {
                // No additional actions needed for rejected requests
            }

            header("Location: admin_manage_payments.php");
            exit();
        } else {
            error_log("Error updating payment status: " . $stmt->error);
            echo "Error updating payment status. Please try again later.";
        }
    } else {
        echo "Error: Payment request not found.";
    }
} else {
    echo "Invalid request.";
}

require('footer.inc.php');
