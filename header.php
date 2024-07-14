<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require('connection.inc.php');

// Check if user_id and user_name are set in the session
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? null;

// Redirect to login page if user_id or user_name is not set
if (!$user_id || !$user_name) {
    // echo "hgf";
}

// Fetch user-specific data
$stmt = $conn->prepare("SELECT * FROM rewards WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_rewards = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch the user's transaction status
$stmt = $conn->prepare("SELECT status FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transaction_status_row = $stmt->get_result()->fetch_assoc();
$transaction_status = $transaction_status_row['status'] ?? null;
$stmt->close();

// Fetch the latest deposit status
$stmt = $conn->prepare("SELECT status FROM deposits WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$deposit_status_row = $stmt->get_result()->fetch_assoc();
$deposit_status = $deposit_status_row['status'] ?? null;
$stmt->close();

// Calculate the wallet balance (sum of accepted deposits)
$stmt = $conn->prepare("SELECT SUM(amount) AS wallet_balance FROM deposits WHERE user_id = ? AND status = 'accepted'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wallet_balance_row = $stmt->get_result()->fetch_assoc();
$wallet_balance = $wallet_balance_row['wallet_balance'] ?? 0;
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/own1.css" rel="stylesheet">
    <style>
        .navbar-nav .nav-link {
            text-decoration: none;
            color: inherit;
        }

        .navbar-nav .nav-link:hover {
            color: inherit;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary shadow-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><b>StakingHUB</b></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars" style="color: white;"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Wallet</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="transactionsDropdown" role="button" data-toggle="dropdown" aria-expanded="false">
                            Transactions
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="transactionsDropdown">
                            <?php if ($transaction_status === 'accepted') : ?>
                                <li><a class="dropdown-item" href="deposit.php" class="btn btn-info">Deposit</a></li>
                            <?php elseif ($transaction_status === 'rejected') : ?>
                                <li>
                                    <p>Transaction Status: <?php echo htmlspecialchars($transaction_status); ?></p>
                                </li>
                                <li><a href="activate.php" class="dropdown-item">Resend Activation Request</a></li>
                            <?php elseif ($transaction_status === 'pending') : ?>
                                <li>
                                    <p>Transaction Status: <?php echo htmlspecialchars($transaction_status); ?></p>
                                </li>
                            <?php elseif (!$transaction_status) : ?>
                                <li>
                                    <p><a href="activate.php" class="dropdown-item">Activate Account</a></p>
                                </li>
                            <?php endif; ?>
                            <?php if ($transaction_status === 'accepted' && $deposit_status) : ?>
                                <!-- <li><p>Deposit Status: <?php echo htmlspecialchars($deposit_status); ?></p></li> -->
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="user_payment.php">Withdrawal</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="stackingDropdown" role="button" data-toggle="dropdown" aria-expanded="false">
                            Stacking
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="stackingDropdown">
                            <li><a class="dropdown-item" href="staking.php">Stacking</a></li>
                            <li><a class="dropdown-item" href="#">P2P (Coming Soon)</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bonus_rewards.php">Bonus Reward</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_payment.php">Withdraw</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="announcements.php">Announcements</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>