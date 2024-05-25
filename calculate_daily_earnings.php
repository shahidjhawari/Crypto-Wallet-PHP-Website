<?php
require('connection.inc.php');

// Fetch all active stakings
$stmt = $conn->prepare("SELECT id, user_id, amount, created_at FROM stakings WHERE status = 'active'");
$stmt->execute();
$result = $stmt->get_result();

while ($staking = $result->fetch_assoc()) {
    $staking_id = $staking['id'];
    $user_id = $staking['user_id'];
    $amount = $staking['amount'];
    $created_at = new DateTime($staking['created_at']);

    // Calculate the total expected earning
    $total_expected_earning = $amount * 3;

    // Fetch total earned so far
    $stmt2 = $conn->prepare("SELECT SUM(earning) AS total_earned FROM daily_earnings WHERE staking_id = ?");
    $stmt2->bind_param("i", $staking_id);
    $stmt2->execute();
    $total_earned = $stmt2->get_result()->fetch_assoc()['total_earned'] ?? 0;
    $stmt2->close();

    // Calculate remaining earnings
    $remaining_earning = $total_expected_earning - $total_earned;

    // Continue if remaining earnings are zero or less
    if ($remaining_earning <= 0) {
        continue;
    }

    // Calculate today's earning based on the day pattern
    $day_of_week = (new DateTime())->format('N'); // 1 (for Monday) through 7 (for Sunday)
    $earning_percentage = 0;
    
    if ($day_of_week != 7) { // Skip Sunday
        $days_since_start = $created_at->diff(new DateTime())->days;
        $pattern_day = ($days_since_start % 3) + 1;

        switch ($pattern_day) {
            case 1:
                $earning_percentage = 0.45 / 100;
                break;
            case 2:
                $earning_percentage = 0.55 / 100;
                break;
            case 3:
                $earning_percentage = 0.65 / 100;
                break;
        }

        $today_earning = $amount * $earning_percentage;

        // Insert today's earning
        $stmt3 = $conn->prepare("INSERT INTO daily_earnings (staking_id, earning, date) VALUES (?, ?, ?)");
        $stmt3->bind_param("ids", $staking_id, $today_earning, (new DateTime())->format('Y-m-d'));
        $stmt3->execute();
        $stmt3->close();
    }
}
$stmt->close();
