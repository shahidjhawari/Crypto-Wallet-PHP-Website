<?php
require('header.php');

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

// Calculate the claimable amount from reward points
$claimable_amount = floatval($user_rewards['reward_points']);

// Fetch the user's transaction status
$stmt = $conn->prepare("SELECT status FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transaction_status_row = $stmt->get_result()->fetch_assoc();
$transaction_status = $transaction_status_row['status'] ?? null;
$stmt->close();

// Check total deposits
$stmt = $conn->prepare("SELECT SUM(amount) AS total_deposited FROM deposits WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_deposited = $stmt->get_result()->fetch_assoc()['total_deposited'] ?? 0;
$stmt->close();

$level_one_locked = false; // Assuming level one is always unlocked
$level_two_locked = $total_deposited < 30;
$level_three_locked = $total_deposited < 50;

// Fetch referral rewards for the logged-in user
$stmt = $conn->prepare("
    SELECT rr.*, u.name AS referred_user,
           CASE
               WHEN rr.reward_percentage = 10 THEN 'Level 1'
               WHEN rr.reward_percentage = 5 THEN 'Level 2'
               WHEN rr.reward_percentage = 2 THEN 'Level 3'
           END AS reward_level
    FROM referral_rewards rr
    JOIN users u ON rr.referred_user_id = u.id
    WHERE rr.referrer_id = ?
    ORDER BY rr.reward_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
    p {
        padding: 15px;
        background: #070F2B;
        border-radius: 10px;
    }

    .locked {
        color: #ccc;
    }

    .unlocked {
        color: #28a745;
    }

    th,
    td {
        color: white;
    }
</style>

<div class="col-12 mb-4">
    <div class="card">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-12">
                    <h3 class="fs-5 mb-3">Referral Reward</h3>
                    <h2 class="display-5 mb-4" style="margin-top: -15px;">
                        $<?php echo htmlspecialchars($user_rewards['reward_points']) ?>.00
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <div class="col-12 mb-4">
    <div class="card">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-12">
                    <h3 class="fs-5 mb-3">User Daily Earning Reward</h3>
                    <h2 class="display-5 mb-4" style="margin-top: -15px;">
                        $<?php echo htmlspecialchars($user_rewards['reward_points']) ?>.00
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div> -->

<div class="col-12 mb-4">
    <div class="card">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-12">
                    <h3 class="fs-5 mb-3">Claim Reward</h3>
                    <h2 class="display-5 mb-4" style="margin-top: -15px;">
                        <?php if ($claimable_amount > 0) : ?>
                            <form action="claim_refer_rewards.php" method="post">
                                <input type="hidden" name="claim_amount" value="<?php echo htmlspecialchars($claimable_amount); ?>">
                                <button type="submit" class="btn btn-primary">Claim Rewards</button>
                            </form>
                        <?php else : ?>
                            <button type="submit" class="btn btn-primary" disabled>Claim Rewards</button>
                        <?php endif; ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="container mt-5">
    <h1>Referral Rewards</h1>
    <form id="claimAllForm" action="claim_user_reward.php" method="post">
        <button type="button" id="claimAllButton" class="btn btn-success mb-3" onclick="claimAllRewards()">Claim All Rewards</button>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <tr>
                    <th>Referred User</th>
                    <th>Daily Earning Amount ($)</th>
                    <th>Reward Percentage (%)</th>
                    <th>Reward Amount ($)</th>
                    <th>Reward Date</th>
                    <th>Level</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['referred_user']); ?></td>
                        <td><?php echo htmlspecialchars($row['daily_earning_amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['reward_percentage']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_10_percent_reward']); ?></td>
                        <td><?php echo htmlspecialchars($row['reward_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['reward_level']); ?></td>
                        <td>
                            <?php if (floatval($row['user_10_percent_reward']) > 0) : ?>
                                <form action="claim_user_reward.php" method="post">
                                    <input type="hidden" name="reward_ids[]" value="<?php echo htmlspecialchars($row['id']); ?>" disabled>
                                    <?php if (($row['reward_percentage'] == 10 && $level_one_locked) || ($row['reward_percentage'] == 5 && $level_two_locked) || ($row['reward_percentage'] == 2 && $level_three_locked)) : ?>
                                        <button type="submit" class="btn btn-success" disabled>Claim Now</button>
                                    <?php else : ?>
                                        <button type="submit" class="btn btn-success">Claim Now</button>
                                    <?php endif; ?>
                                </form>
                            <?php else : ?>
                                <span>Reward Claimed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </form>
</div>

<script>
    function claimAllRewards() {
        var form = document.getElementById('claimAllForm');
        var inputs = form.querySelectorAll('input[name="reward_ids[]"]');
        var hasEnabledRewards = false;

        inputs.forEach(function(input) {
            var button = input.closest('tr').querySelector('button[type="submit"]');
            if (button && button.disabled === false) {
                hasEnabledRewards = true;
                input.disabled = false;
            } else {
                input.disabled = true;
            }
        });

        if (hasEnabledRewards) {
            form.submit();
        } else {
            alert('No rewards available for claiming.');
        }
    }
</script>
