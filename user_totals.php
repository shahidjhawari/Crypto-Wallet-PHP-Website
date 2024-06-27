<?php
session_start();
require('config.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the user's totals
$stmt = $conn->prepare("
    SELECT 
        ut.total_daily_earning, 
        ut.total_rewards_points 
    FROM 
        user_totals ut 
    WHERE 
        ut.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_totals = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Totals</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <h1>User Totals</h1>
    <table>
        <thead>
            <tr>
                <th>Total Daily Earning</th>
                <th>Total Rewards Points</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo number_format($user_totals['total_daily_earning'], 2); ?></td>
                <td><?php echo number_format($user_totals['total_rewards_points'], 2); ?></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
