<?php
require('top.php');
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Function to sanitize user input
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $claim_amount = test_input($_POST["claim_amount"]);

    // Check if claim amount is valid
    $stmt = $conn->prepare("SELECT * FROM rewards WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_rewards = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Calculate total claimed amount
    $stmt = $conn->prepare("SELECT SUM(amount) AS total_claimed FROM deposits WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_claimed = $stmt->get_result()->fetch_assoc()['total_claimed'] ?? 0;
    $stmt->close();

    // Calculate total rewards
    $stmt = $conn->prepare("SELECT SUM(reward_amount) AS total_rewards FROM referral_rewards WHERE referrer_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_rewards = $stmt->get_result()->fetch_assoc()['total_rewards'] ?? 0;
    $stmt->close();

    $claimable_amount = $total_rewards - $total_claimed;
    $claimable_amount = max(0, $claimable_amount);

    // Calculate the actual claimable amount based on the user's referral levels and rules
    $level_one_rewards = $user_rewards['level_one_count'] * 5;
    $level_two_rewards = $user_rewards['level_two_count'] * 5;
    $level_three_rewards = 0; // No rewards for level three activation

    $total_earnings = $level_one_rewards + $level_two_rewards + $level_three_rewards;

    if ($claim_amount <= $claimable_amount && $claim_amount <= $total_earnings) {
        // Insert the claim into the deposits table
        $status = 'Accepted'; // Assuming reward claims are automatically accepted
        $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, status) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $claim_amount, $status);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: refer_page.php?claim_success=1");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Invalid claim amount.";
    }
}
?>
