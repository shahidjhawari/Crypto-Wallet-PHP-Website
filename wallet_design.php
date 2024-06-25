<?php
require('header.php');
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch user-specific data
$stmt = $conn->prepare("SELECT * FROM rewards WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_rewards = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch the user's referral code
$stmt = $conn->prepare("SELECT referral_code FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_referral = $stmt->get_result()->fetch_assoc();
$stmt->close();

$referral_code = $user_referral['referral_code'];
$referral_link = SITE_PATH . "/signup.php?referral=" . $referral_code;

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

// Handle form submission for new deposits
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amount'])) {
    $amount = $_POST['amount'];
    $screenshot = $_FILES['screenshot']['name'];
    move_uploaded_file($_FILES['screenshot']['tmp_name'], 'upload/' . $screenshot);

    $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, screenshot, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("ids", $user_id, $amount, $screenshot);
    $stmt->execute();
    $stmt->close();

    // Update transaction status to pending when a new deposit is made
    $transaction_status = 'pending';
}

?>

<div class="container-fluid py-4">
    <div class="row">


        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-12">
                            <!-- Wallet Balance -->
                            <p class="fs-5 mb-3">Wallet Balance</p>
                            <!-- Wallet Balance Amount (in larger size) -->
                            <h1 class="display-5 mb-4" style="margin-top: -15px;">$<?php echo htmlspecialchars(number_format($wallet_balance, 2)); ?></h1>
                            <!-- Your Account Has Been Activated message -->
                            <?php if ($transaction_status === 'accepted') : ?>
                                <p>Your account has been <span style="color: green;">Activated</span></p>
                            <?php endif; ?>
                            <!-- Deposit Button -->
                            <?php if ($transaction_status === 'accepted') : ?>
                                <p><a href="deposit.php" class="btn btn-info">Deposit</a></p>
                            <?php endif; ?>
                            <!-- Transaction Status and Resend Activation Request Button -->
                            <?php if ($transaction_status === 'rejected') : ?>
                                <p>Transaction Status: <?php echo htmlspecialchars($transaction_status); ?></p>
                                <!-- <p><a href="activate.php" class="btn btn-info">Resend Activation Request</a></p> -->
                            <?php elseif ($transaction_status === 'pending') : ?>
                                <p>Transaction Status: <?php echo htmlspecialchars($transaction_status); ?></p>
                            <?php elseif (!$transaction_status) : ?>
                                <!-- <p><a href="activate.php" class="btn btn-info">Activate Account</a></p> -->
                            <?php elseif ($transaction_status === 'accepted' && $deposit_status) : ?>
                                <p>Deposit Status: <?php echo htmlspecialchars($deposit_status); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require('footer.php'); ?>