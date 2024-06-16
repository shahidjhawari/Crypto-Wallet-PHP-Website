<?php
ob_start();
session_start();
require('header.php');

//  
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch referral rewards for the logged-in user
$stmt = $conn->prepare("
    SELECT rr.*, u.name AS referred_user
    FROM referral_rewards rr
    JOIN users u ON rr.referred_user_id = u.id
    WHERE rr.referrer_id = ?
    ORDER BY rr.reward_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Referral Rewards</title>
    <!-- Add your CSS styling here -->
</head>
<body>
    <h1>Referral Rewards</h1>
    <table border="1">
        <tr>
            <th>Referred User</th>
            <th>Daily Earning Amount ($)</th>
            <th>Reward Percentage (%)</th>
            <th>Reward Amount ($)</th>
            <th>User 10% Reward ($)</th>
            <th>User 10% Reward ($)</th>
            <th>Reward Date</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['referred_user']); ?></td>
            <td><?php echo htmlspecialchars($row['daily_earning_amount']); ?></td>
            <td><?php echo htmlspecialchars($row['reward_percentage']); ?></td>
            <td><?php echo htmlspecialchars($row['reward_amount']); ?></td>
            <td><?php echo htmlspecialchars($row['user_10_percent_reward']); ?></td>
            <td><?php echo htmlspecialchars($row['reward_date']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php
require('footer.php');
?>
