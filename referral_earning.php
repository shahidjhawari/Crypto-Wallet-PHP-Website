<?php
require('header.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch referral earnings for the user
$stmt = $conn->prepare("SELECT id, amount FROM referral_earnings WHERE user_id = ? AND amount > 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$referral_earnings = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Referral Earnings</title>
    <!-- Add your CSS styling here -->
</head>
<body>
    <h2>Referral Earnings</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_earnings = 0;
            while ($row = $referral_earnings->fetch_assoc()) {
                $total_earnings += $row['amount'];
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . number_format($row['amount'], 2) . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    <p>Total Earnings: <?php echo number_format($total_earnings, 2); ?></p>

    <form action="claim_referral_earnings.php" method="POST">
        <input type="hidden" name="total_earnings" value="<?php echo $total_earnings; ?>">
        <input type="submit" value="Claim">
    </form>
</body>
</html>
