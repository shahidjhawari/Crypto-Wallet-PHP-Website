<?php
ob_start();
session_start();
require('top.inc.php');

// Ensure only admin can access this script
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: index.php");
//     exit();
// }

// Fetch all stakings where the user is a referred user and staking is active
$stmt = $con->prepare("SELECT stakings.*, users.referrer_id 
                        FROM stakings 
                        JOIN users ON stakings.user_id = users.id 
                        WHERE users.referrer_id IS NOT NULL 
                        AND stakings.status = 'active'");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

while ($staking = $result->fetch_assoc()) {
    $referrer_id = $staking['referrer_id'];
    $staking_amount = $staking['amount'];
    $referrer_bonus = $staking_amount * 0.1; // 10% of staking amount

    // Update referrer's wallet balance
    $stmt = $con->prepare("UPDATE deposits SET amount = amount + ? WHERE user_id = ?");
    $stmt->bind_param("di", $referrer_bonus, $referrer_id);
    $stmt->execute();
    $stmt->close();

    // Update referrer's reward points
    $stmt = $con->prepare("UPDATE rewards SET reward_points = reward_points + ? WHERE user_id = ?");
    $stmt->bind_param("di", $referrer_bonus, $referrer_id);
    $stmt->execute();
    $stmt->close();
}

echo "Referral bonuses processed successfully.";
?>
