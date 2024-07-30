<?php
ob_start();
require('header.php');
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $claim_amount = floatval($_POST['claim_amount']);

    // Fetch the current reward points
    $stmt = $conn->prepare("SELECT reward_points FROM rewards WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_rewards = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($claim_amount > 0 && $claim_amount <= $user_rewards['reward_points']) {
        // Insert the claim into the referral_earnings table
        $stmt = $conn->prepare("INSERT INTO referral_earnings (user_id, amount) VALUES (?, ?)");
        $stmt->bind_param("id", $user_id, $claim_amount);

        if ($stmt->execute()) {
            $stmt->close();

            // Deduct the claim amount from remaining_earning in the stakings table
            $remaining_claim_amount = $claim_amount;

            $stmt = $conn->prepare("SELECT id, remaining_earning FROM stakings WHERE user_id = ? AND remaining_earning > 0 ORDER BY id ASC");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stakings_result = $stmt->get_result();
            $stmt->close();

            while ($remaining_claim_amount > 0 && $staking_row = $stakings_result->fetch_assoc()) {
                $staking_id = $staking_row['id'];
                $current_remaining_earning = floatval($staking_row['remaining_earning']);
                $deduction = min($current_remaining_earning, $remaining_claim_amount);
                
                $stmt = $conn->prepare("UPDATE stakings SET remaining_earning = remaining_earning - ? WHERE id = ?");
                $stmt->bind_param("di", $deduction, $staking_id);
                $stmt->execute();
                $stmt->close();

                $remaining_claim_amount -= $deduction;
            }

            // Update the user's reward points
            $stmt = $conn->prepare("UPDATE rewards SET reward_points = reward_points - ? WHERE user_id = ?");
            $stmt->bind_param("di", $claim_amount, $user_id);
            $stmt->execute();
            $stmt->close();

            header("Location: team_design.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Invalid claim amount.";
    }
}
?>
