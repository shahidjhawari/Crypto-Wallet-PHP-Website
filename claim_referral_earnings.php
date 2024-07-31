<?php
ob_start();
require('header.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $total_earnings = floatval($_POST['total_earnings']);

    if ($total_earnings > 0) {
        // Transfer to deposits table
        $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, status) VALUES (?, ?, 'Accepted')");
        $stmt->bind_param("id", $user_id, $total_earnings);
        if ($stmt->execute()) {
            $stmt->close();

            // Clear the claimed earnings in referral_earnings table
            $stmt = $conn->prepare("UPDATE referral_earnings SET amount = 0 WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Earnings successfully claimed.";
        } else {
            $_SESSION['error'] = "Error claiming earnings: " . $stmt->error;
        }
    } else {
        $_SESSION['error'] = "No earnings to claim.";
    }

    header("Location: referral_earning.php");
    exit();
}
