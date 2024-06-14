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
    SELECT de.id AS daily_earning_id, de.amount, u.id AS user_id, u.referrer_id 
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
    $earning_amount = $earnings['amount'];

    // Fetch referrer chain (level 1, 2, 3)
    $stmt_referrer_chain = $con->prepare("
        SELECT u1.referrer_id AS level1, u2.referrer_id AS level2, u3.referrer_id AS level3
        FROM users u1
        LEFT JOIN users u2 ON u1.referrer_id = u2.id
        LEFT JOIN users u3 ON u2.referrer_id = u3.id
        WHERE u1.id = ?
    ");
    $stmt_referrer_chain->bind_param("i", $earnings['user_id']);
    $stmt_referrer_chain->execute();
    $referrer_chain = $stmt_referrer_chain->get_result()->fetch_assoc();
    $stmt_referrer_chain->close();

    // Rewards for level 1, 2, 3
    $reward_percentages = [
        'level1' => 0.1, // 10% for level 1
        'level2' => 0.05, // 5% for level 2
        'level3' => 0.02 // 2% for level 3
    ];

    foreach (['level1', 'level2', 'level3'] as $level) {
        if ($referrer_chain[$level]) {
            $referrer_id = $referrer_chain[$level];
            $referrer_bonus = $earning_amount * $reward_percentages[$level];

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
        }
    }

    // Mark the daily earning record as processed
    $stmt_mark_processed = $con->prepare("
        UPDATE daily_earnings 
        SET reward_processed = TRUE 
        WHERE id = ?
    ");
    $stmt_mark_processed->bind_param("i", $daily_earning_id);
    $stmt_mark_processed->execute();
    $stmt_mark_processed->close();
}

$stmt->close();
echo "Referral bonuses processed successfully.";
?>
