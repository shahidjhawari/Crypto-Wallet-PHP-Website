<?php
// calculate_daily_earnings.php
ob_start();
session_start();
require('top.inc.php');

if (!isset($_POST['percentage'])) {
    echo "No percentage specified.";
    exit();
}

if (isset($_SESSION['earnings_processed']) && $_SESSION['earnings_processed']) {
    header("Location: admin.php");
    exit();
}

$percentage = floatval($_POST['percentage']);

// Fetch all active stakings
$stmt = $con->prepare("SELECT * FROM stakings WHERE status = 'active' AND is_tripled = 0");
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
        // Calculate the correct percentage based on the provided percentage
        $daily_earning = $staking['amount'] * $percentage;
        $staking['total_earning'] += $daily_earning;
        $staking['remaining_earning'] -= $daily_earning;

        // Insert daily earning record
        $stmt = $con->prepare("INSERT INTO daily_earnings (user_id, staking_id, date, amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisd", $user_id, $staking_id, $today_str, $daily_earning);
        $stmt->execute();
        $stmt->close();

        // Update total earned, remaining earnings, and daily calculation count
        $is_tripled = (int)($staking['total_earning'] >= 3 * $staking['amount']);
        $stmt = $con->prepare("UPDATE stakings SET total_earning = ?, remaining_earning = ?, is_tripled = ?, daily_calculation_count = daily_calculation_count + 1 WHERE id = ?");
        $stmt->bind_param("ddii", $staking['total_earning'], $staking['remaining_earning'], $is_tripled, $staking_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Set session variable to indicate earnings have been processed
$_SESSION['earnings_processed'] = true;

// Redirect to prevent form resubmission
header("Location: admin.php");
exit();
?>
