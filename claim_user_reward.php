<?php
ob_start();
require('header.php');
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch all eligible rewards for the user
    $stmt = $conn->prepare("SELECT id, user_10_percent_reward FROM referral_rewards WHERE referrer_id = ? AND user_10_percent_reward > 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $rewards = $stmt->get_result();
    $stmt->close();

    $total_claimed = 0;

    while ($reward = $rewards->fetch_assoc()) {
        $reward_id = $reward['id'];
        $claim_amount = floatval($reward['user_10_percent_reward']);

        if ($claim_amount > 0) {
            // Insert the claim amount into the deposits table with status 'Accepted'
            $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, referral_daily_reward, status) VALUES (?, ?, ?, 'Accepted')");
            $stmt->bind_param("idd", $user_id, $claim_amount, $claim_amount);
            $stmt->execute();
            $stmt->close();

            // Deduct the claimed amount from the remaining_earning in the stakings table
            $remaining_claim_amount = $claim_amount;

            // Fetch stakings with remaining earnings greater than 0
            $stmt = $conn->prepare("SELECT id, remaining_earning FROM stakings WHERE user_id = ? AND remaining_earning > 0 ORDER BY id ASC");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stakings_result = $stmt->get_result();
            $stmt->close();

            while ($remaining_claim_amount > 0 && $staking_row = $stakings_result->fetch_assoc()) {
                $staking_id = $staking_row['id'];
                $current_remaining_earning = floatval($staking_row['remaining_earning']);
                $deduction = min($current_remaining_earning, $remaining_claim_amount);

                // Update the remaining earning in stakings table
                $stmt = $conn->prepare("UPDATE stakings SET remaining_earning = remaining_earning - ? WHERE id = ?");
                $stmt->bind_param("di", $deduction, $staking_id);
                $stmt->execute();
                $stmt->close();

                $remaining_claim_amount -= $deduction;
            }

            // Update the referral reward to set the user_10_percent_reward to 0
            $stmt = $conn->prepare("UPDATE referral_rewards SET user_10_percent_reward = 0 WHERE id = ?");
            $stmt->bind_param("i", $reward_id);
            $stmt->execute();
            $stmt->close();

            $total_claimed += $claim_amount;
        }
    }

    if ($total_claimed > 0) {
        $_SESSION['message'] = "Total reward claimed: $total_claimed";
    } else {
        $_SESSION['error'] = "No rewards available for claiming.";
    }

    header("Location: team_earning.php");
    exit();
}
?>