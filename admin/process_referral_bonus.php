<?php
ob_start();
session_start();
require('top.inc.php');

// Ensure only admin can access this script
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: index.php");
//     exit();
// }

// Fetch all daily earnings where the user is a referred user, staking is active, and reward has not been processed
$stmt = $con->prepare("
    SELECT de.id AS daily_earning_id, de.amount, u.referrer_id 
    FROM daily_earnings de
    JOIN stakings s ON de.staking_id = s.id
    JOIN users u ON s.user_id = u.id 
    WHERE u.referrer_id IS NOT NULL 
    AND s.status = 'active'
    AND de.reward_processed = FALSE
");
$stmt->execute();
$result = $stmt->get_result();

while ($earnings = $result->fetch_assoc()) {
    $daily_earning_id = $earnings['daily_earning_id'];
    $referrer_id = $earnings['referrer_id'];
    $earning_amount = $earnings['amount'];
    $referrer_bonus = $earning_amount * 0.1; // 10% of earning amount

    // Check if referrer exists and get current deposit and reward points
    $stmt_check_referrer = $con->prepare("SELECT * FROM users WHERE id = ?");
    $stmt_check_referrer->bind_param("i", $referrer_id);
    $stmt_check_referrer->execute();
    $referrer = $stmt_check_referrer->get_result()->fetch_assoc();
    $stmt_check_referrer->close();

    if ($referrer) {
        // Update referrer's wallet balance
        $stmt_update_wallet = $con->prepare("
            UPDATE deposits 
            SET amount = amount + ? 
            WHERE user_id = ? 
            AND status = 'accepted'
            ORDER BY id ASC LIMIT 1
        ");
        $stmt_update_wallet->bind_param("di", $referrer_bonus, $referrer_id);
        $stmt_update_wallet->execute();
        $stmt_update_wallet->close();

        // Update referrer's reward points
        $stmt_update_rewards = $con->prepare("
            UPDATE rewards 
            SET reward_points = reward_points + ? 
            WHERE user_id = ?
        ");
        $stmt_update_rewards->bind_param("di", $referrer_bonus, $referrer_id);
        $stmt_update_rewards->execute();
        $stmt_update_rewards->close();

        // Mark the daily earning record as processed
        $stmt_mark_processed = $con->prepare("
            UPDATE daily_earnings 
            SET reward_processed = TRUE 
            WHERE id = ?
        ");
        $stmt_mark_processed->bind_param("i", $daily_earning_id);
        $stmt_mark_processed->execute();
        $stmt_mark_processed->close();
    } else {
        // Log or handle the error if referrer is not found
        echo "Referrer with ID $referrer_id not found.<br>";
    }
}

$stmt->close();
echo "Referral bonuses processed successfully.";
?>
