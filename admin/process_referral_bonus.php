<?php
ob_start();
session_start();
require('top.inc.php');

// Ensure only admin can access this script
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: index.php");
//     exit();
// }

// Fetch all daily earnings where the user is a referred user and staking is active
$stmt = $con->prepare("SELECT daily_earnings.*, users.referrer_id 
                        FROM daily_earnings 
                        JOIN stakings ON daily_earnings.staking_id = stakings.id
                        JOIN users ON stakings.user_id = users.id 
                        WHERE users.referrer_id IS NOT NULL 
                        AND stakings.status = 'active'");
$stmt->execute();
$result = $stmt->get_result();

while ($earnings = $result->fetch_assoc()) {
    $referrer_id = $earnings['referrer_id'];
    $earning_amount = $earnings['amount'];
    $referrer_bonus = $earning_amount * 0.1; // 10% of earning amount

    // Update referrer's wallet balance
    $stmt_update_wallet = $con->prepare("UPDATE deposits SET amount = amount + ? WHERE user_id = ?");
    $stmt_update_wallet->bind_param("di", $referrer_bonus, $referrer_id);
    $stmt_update_wallet->execute();
    $stmt_update_wallet->close();

    // Update referrer's reward points
    $stmt_update_rewards = $con->prepare("UPDATE rewards SET reward_points = reward_points + ? WHERE user_id = ?");
    $stmt_update_rewards->bind_param("di", $referrer_bonus, $referrer_id);
    $stmt_update_rewards->execute();
    $stmt_update_rewards->close();
}

$stmt->close();

echo "bonus reward successfull done";

?>
