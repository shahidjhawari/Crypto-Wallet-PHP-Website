<?php
require('top.php');
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

// Check if rewards have been claimed
$stmt = $conn->prepare("SELECT SUM(amount) AS total_claimed FROM deposits WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_claimed = $stmt->get_result()->fetch_assoc()['total_claimed'] ?? 0;
$stmt->close();

// Calculate the claimable amount
$claimable_amount = $user_rewards['reward_points'] - $total_claimed;


// Fetch the user's transaction status
$stmt = $conn->prepare("SELECT status FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transaction_status_row = $stmt->get_result()->fetch_assoc();
$transaction_status = $transaction_status_row['status'] ?? null;
$stmt->close();
?>

<p>Your Reward Points: <?php echo htmlspecialchars($user_rewards['reward_points']); ?></p>
<p>Referral Count: <?php echo htmlspecialchars($user_rewards['referral_count']); ?></p>
<p>Level One Count: <?php echo htmlspecialchars($user_rewards['level_one_count']); ?></p>
<p>Level Two Count: <?php echo htmlspecialchars($user_rewards['level_two_count']); ?></p>
<p>Level Three Count: <?php echo htmlspecialchars($user_rewards['level_three_count']); ?></p>
<p>Referral Link: <?php echo htmlspecialchars($referral_link); ?></p>
<p>Claimable Amount: $<?php echo htmlspecialchars(number_format($claimable_amount, 2)); ?></p>

<?php if ($claimable_amount > 0): ?>
    <form action="claim_refer_rewards.php" method="post">
        <input type="hidden" name="claim_amount" value="<?php echo htmlspecialchars($claimable_amount); ?>">

        <?php if($transaction_status === "accepted"){?>
        <button type="submit" class="btn btn-primary">Claim Rewards</button>
        <?php } else { ?>
            <button type="submit" class="btn btn-primary" disabled>Claim Rewards</button>
            <span>please activate account to claim rewar</span>
       <?php } ?>
    </form>
<?php else: ?>
    <p>No rewards to claim.</p>
<?php endif; ?>

<?php require('footer.php'); ?>
