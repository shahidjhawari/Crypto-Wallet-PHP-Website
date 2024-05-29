<?php
// calculate_daily_earnings.php
ob_start();
session_start();
require('header.php');

// Fetch all active stakings
$stmt = $conn->prepare("SELECT * FROM stakings WHERE status = 'active' AND is_tripled = 0");
$stmt->execute();
$staking_records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$today = new DateTime();
$today_str = $today->format('Y-m-d');

foreach ($staking_records as $staking) {
    $user_id = $staking['user_id'];
    $staking_id = $staking['id'];
    $start_date = new DateTime($staking['created_at']);
    $interval = $start_date->diff($today)->days;

    // Ensure not to recalculate the first day's earnings
    if ($interval >= 0) {
        // Calculate the correct percentage based on the daily calculation count
        $daily_calculation_count = $staking['daily_calculation_count'];
        $daily_earning = calculate_daily_earning($daily_calculation_count, $staking['amount']);
        $staking['total_earning'] += $daily_earning;
        $staking['remaining_earning'] -= $daily_earning;

        // Insert daily earning record
        $stmt = $conn->prepare("INSERT INTO daily_earnings (user_id, staking_id, date, amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisd", $user_id, $staking_id, $today_str, $daily_earning);
        $stmt->execute();
        $stmt->close();

        // Update total earned, remaining earnings, and daily calculation count
        $is_tripled = (int)($staking['total_earning'] >= 3 * $staking['amount']);
        $stmt = $conn->prepare("UPDATE stakings SET total_earning = ?, remaining_earning = ?, is_tripled = ?, daily_calculation_count = daily_calculation_count + 1 WHERE id = ?");
        $stmt->bind_param("ddii", $staking['total_earning'], $staking['remaining_earning'], $is_tripled, $staking_id);
        $stmt->execute();
        $stmt->close();
    }
}

function calculate_daily_earning($count, $amount)
{
    $percentages = [0.0045, 0.0055, 0.0065];
    return $amount * $percentages[$count % 3];
}

echo "Daily earnings calculated successfully.";
?>
