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
    $reward_id = intval($_POST['reward_id']);

    // Fetch the reward details
    $stmt = $conn->prepare("SELECT user_10_percent_reward FROM referral_rewards WHERE id = ? AND referrer_id = ?");
    $stmt->bind_param("ii", $reward_id, $user_id);
    $stmt->execute();
    $reward = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($reward && floatval($reward['user_10_percent_reward']) > 0) {
        $claim_amount = floatval($reward['user_10_percent_reward']);

        // Insert the claim amount into the deposits table with status 'Accepted'
        $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, status) VALUES (?, ?, 'Accepted')");
        $stmt->bind_param("id", $user_id, $claim_amount);
        $stmt->execute();
        $stmt->close();

        // Update the referral reward to set the user_10_percent_reward to 0
        $stmt = $conn->prepare("UPDATE referral_rewards SET user_10_percent_reward = 0 WHERE id = ?");
        $stmt->bind_param("i", $reward_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = "Reward claimed successfully!";
    } else {
        $_SESSION['error'] = "Invalid reward or reward already claimed.";
    }

    header("Location: team_earning.php");
    exit();
}
