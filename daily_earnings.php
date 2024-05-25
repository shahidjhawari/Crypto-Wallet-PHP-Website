<?php
session_start();
require('header.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch staking records and their daily earnings
$stmt = $conn->prepare("
    SELECT s.id AS staking_id, s.amount AS staked_amount, s.created_at AS staked_date, 
           de.earning, de.date AS earning_date, 
           (SELECT SUM(earning) FROM daily_earnings WHERE staking_id = s.id) AS total_earned,
           (s.amount * 3) AS expected_total_earning
    FROM stakings s
    LEFT JOIN daily_earnings de ON s.id = de.staking_id
    WHERE s.user_id = ?
    ORDER BY de.date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$earnings = [];
while ($row = $result->fetch_assoc()) {
    $earnings[] = $row;
}
$stmt->close();
?>

<div class="container">
    <h2>Daily Earnings</h2>

    <table class="table">
        <thead>
            <tr>
                <th>Staking ID</th>
                <th>Staked Amount</th>
                <th>Staked Date</th>
                <th>Earning</th>
                <th>Earning Date</th>
                <th>Total Earned</th>
                <th>Expected Total Earning</th>
                <th>Remaining Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($earnings)) : ?>
                <tr>
                    <td colspan="8">No daily earnings found.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($earnings as $earning) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($earning['staking_id']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($earning['staked_amount'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($earning['staked_date']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($earning['earning'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($earning['earning_date']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($earning['total_earned'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($earning['expected_total_earning'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(number_format($earning['expected_total_earning'] - $earning['total_earned'], 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>