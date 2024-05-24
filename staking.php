<?php
require('top.php');
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the user's current balance
$stmt = $conn->prepare("SELECT SUM(amount) AS wallet_balance FROM deposits WHERE user_id = ? AND status = 'accepted'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wallet_balance = $stmt->get_result()->fetch_assoc()['wallet_balance'] ?? 0;
$stmt->close();

// Handle the staking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stake_amount = $_POST['stake_amount'];

    if ($stake_amount > 0 && $stake_amount <= $wallet_balance) {
        // Insert staking record
        $stmt = $conn->prepare("INSERT INTO stakings (user_id, amount) VALUES (?, ?)");
        $stmt->bind_param("id", $user_id, $stake_amount);
        $stmt->execute();
        $stmt->close();

        // Deduct the staked amount from the user's balance
        $stmt = $conn->prepare("UPDATE deposits SET amount = amount - ? WHERE user_id = ? AND status = 'accepted' AND amount >= ?");
        $stmt->bind_param("dii", $stake_amount, $user_id, $stake_amount);
        $stmt->execute();
        $stmt->close();

        // Recalculate the wallet balance
        $stmt = $conn->prepare("SELECT SUM(amount) AS wallet_balance FROM deposits WHERE user_id = ? AND status = 'accepted'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $wallet_balance = $stmt->get_result()->fetch_assoc()['wallet_balance'] ?? 0;
        $stmt->close();

        $success_message = "Successfully staked $" . htmlspecialchars(number_format($stake_amount, 2));
    } else {
        $error_message = "Invalid staking amount.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Staking</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h2>Staking</h2>
        <p>Wallet Balance: $<?php echo htmlspecialchars(number_format($wallet_balance, 2)); ?></p>

        <?php if (!empty($success_message)) : ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="staking.php">
            <div class="form-group">
                <label for="stake_amount">Amount to Stake:</label>
                <input type="number" class="form-control" id="stake_amount" name="stake_amount" step="0.01" min="5" max="<?php echo htmlspecialchars($wallet_balance); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Stake</button>
        </form>
    </div>
</body>

</html>