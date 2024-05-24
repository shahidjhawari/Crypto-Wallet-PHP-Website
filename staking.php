<?php
ob_start();
require('header.php');
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the user's deposit balance
$stmt = $conn->prepare("SELECT SUM(amount) AS deposit_balance FROM deposits WHERE user_id = ? AND status = 'accepted'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_deposit = $result->fetch_assoc();
$deposit_balance = $user_deposit['deposit_balance'] ?? 0;
$stmt->close();

// Handle staking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stake_amount = $_POST['stake_amount'];

    // Validate staking amount
    if ($stake_amount < 5) {
        $error = "Minimum staking amount is $5.";
    } elseif ($stake_amount > $deposit_balance) {
        $error = "You do not have enough balance to stake this amount.";
    } else {
        // Insert staking record
        $stmt = $conn->prepare("INSERT INTO staking (user_id, amount, status) VALUES (?, ?, 'active')");
        $stmt->bind_param("id", $user_id, $stake_amount);
        $stmt->execute();
        $stmt->close();

        // Update deposit balance
        $stmt = $conn->prepare("UPDATE deposits SET amount = amount - ? WHERE user_id = ? AND status = 'accepted' AND amount >= ? LIMIT 1");
        $stmt->bind_param("dii", $stake_amount, $user_id, $stake_amount);
        $stmt->execute();
        $stmt->close();

        $success = "You have successfully staked $$stake_amount.";

        // Refresh the deposit balance
        $stmt = $conn->prepare("SELECT SUM(amount) AS deposit_balance FROM deposits WHERE user_id = ? AND status = 'accepted'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_deposit = $result->fetch_assoc();
        $deposit_balance = $user_deposit['deposit_balance'] ?? 0;
        $stmt->close();
    }
}

// Fetch user's staking history
$stmt = $conn->prepare("SELECT * FROM staking WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$staking_history = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staking</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Staking</h2>
        <p>Available Balance: $<?php echo htmlspecialchars(number_format($deposit_balance, 2)); ?></p>
        <form method="post" action="staking.php">
            <div class="form-group">
                <label for="stake_amount">Amount to Stake</label>
                <input type="number" class="form-control" id="stake_amount" name="stake_amount" step="0.01" min="5" required>
            </div>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Stake</button>
        </form>
        <h3>Staking History</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($stake = $staking_history->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($stake['id']); ?></td>
                        <td><?php echo htmlspecialchars($stake['amount']); ?></td>
                        <td><?php echo htmlspecialchars($stake['status']); ?></td>
                        <td><?php echo htmlspecialchars($stake['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
