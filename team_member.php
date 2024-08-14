<?php
ob_start();
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

$level_two_unlocked = $user_rewards['level_two_unlocked'];
$level_three_unlocked = $user_rewards['level_three_unlocked'];

// Check total deposits (to determine if new levels should be unlocked)
$stmt = $conn->prepare("SELECT SUM(amount) AS total_deposited FROM deposits WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_deposited = $stmt->get_result()->fetch_assoc()['total_deposited'] ?? 0;
$stmt->close();

// Unlock levels based on total deposit
if ($total_deposited >= 30 && !$level_two_unlocked) {
    $stmt = $conn->prepare("UPDATE rewards SET level_two_unlocked = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    $level_two_unlocked = 1;
}

if ($total_deposited >= 50 && !$level_three_unlocked) {
    $stmt = $conn->prepare("UPDATE rewards SET level_three_unlocked = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    $level_three_unlocked = 1;
}

// Fetch the user's referral code
$stmt = $conn->prepare("SELECT referral_code FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_referral = $stmt->get_result()->fetch_assoc();
$stmt->close();

$referral_code = $user_referral['referral_code'];
$referral_link = SITE_PATH . "/signup.php?referral=" . $referral_code;

// Fetch referred users' names by level
$levels = [1 => [], 2 => [], 3 => []];

// Fetch Level 1 referrals
$stmt = $conn->prepare("SELECT id, name FROM users WHERE referrer_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$level1_result = $stmt->get_result();
while ($row = $level1_result->fetch_assoc()) {
    $levels[1][] = htmlspecialchars($row['name']);

    // Fetch Level 2 referrals for each Level 1 user
    $stmt2 = $conn->prepare("SELECT id, name FROM users WHERE referrer_id = ?");
    $stmt2->bind_param("i", $row['id']);
    $stmt2->execute();
    $level2_result = $stmt2->get_result();
    while ($row2 = $level2_result->fetch_assoc()) {
        $levels[2][] = htmlspecialchars($row2['name']);

        // Fetch Level 3 referrals for each Level 2 user
        $stmt3 = $conn->prepare("SELECT name FROM users WHERE referrer_id = ?");
        $stmt3->bind_param("i", $row2['id']);
        $stmt3->execute();
        $level3_result = $stmt3->get_result();
        while ($row3 = $level3_result->fetch_assoc()) {
            $levels[3][] = htmlspecialchars($row3['name']);
        }
        $stmt3->close();
    }
    $stmt2->close();
}
$stmt->close();

// Display logic for locked/unlocked status
$level_two_locked = !$level_two_unlocked;
$level_three_locked = !$level_three_unlocked;


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
// Pagination logic


$referral_rewards_stmt = $conn->prepare("
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
    LIMIT ? OFFSET ?
");
$referral_rewards_stmt->bind_param("iii", $user_id, $records_per_page, $offset);
$referral_rewards_stmt->execute();
$result = $referral_rewards_stmt->get_result();
$referral_rewards_stmt->close();

// Fetch total referral earnings from ClaimedEarning table
$stmt = $conn->prepare("SELECT SUM(amount) AS total_referral_earnings FROM ClaimedEarning WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_referral_earnings_row = $stmt->get_result()->fetch_assoc();
$total_referral_earnings = $total_referral_earnings_row['total_referral_earnings'] ?? 0;
$stmt->close();

// Check if the user has at least one accepted staking request
$stmt = $conn->prepare("SELECT COUNT(*) AS accepted_requests FROM staking_requests WHERE user_id = ? AND status = 'accepted'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$accepted_requests_row = $stmt->get_result()->fetch_assoc();
$accepted_requests = $accepted_requests_row['accepted_requests'] > 0;
$stmt->close();

// Check total remaining earnings
$stmt = $conn->prepare("SELECT SUM(remaining_earning) AS total_remaining_earning FROM stakings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_remaining_earning_row = $stmt->get_result()->fetch_assoc();
$total_remaining_earning = $total_remaining_earning_row['total_remaining_earning'] ?? 0;
$stmt->close();

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

    .no-underline {
        text-decoration: none !important;
    }
</style>

<div class="container mt-4">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="fs-5 mb-4">Reward</h5>
                        <h2 class="display-5 mb-4" style="margin-top: -15px;">
                            $<?php echo htmlspecialchars($user_rewards['reward_points']) ?>.00
                        </h2>
                    </div>
                    <div class="col text-end">
                        <?php if ($claimable_amount > 0 && $accepted_requests && $total_remaining_earning >= $claimable_amount) : ?>
                            <form action="claim_refer_rewards.php" method="post">
                                <input type="hidden" name="claim_amount" value="<?php echo htmlspecialchars($claimable_amount); ?>">
                                <button type="submit" class="btn btn-primary">Claim</button>
                            </form>
                        <?php else : ?>
                            <button type="submit" class="btn btn-primary" disabled>Claim</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card-body p-3">
                <div class="row">
                    <div class="col-12">
                        <p>Referral Link: <span id="referral-link"><?php echo htmlspecialchars($referral_link); ?></span></p>
                        <button onclick="copyReferralLink()" class="btn btn-secondary">Copy Link</button>
                        <span id="copy-success" style="display:none; color: green; margin-left: 10px;">Copied!</span>
                        <hr>
                        <p>Total Referral Users: <?php echo htmlspecialchars($user_rewards['referral_count']); ?></p>

                        <p>
                            <a href="#levelOneDetails" class="no-underline" data-toggle="collapse" aria-expanded="false" aria-controls="levelOneDetails">
                                Level 1 Count: <?php echo htmlspecialchars($user_rewards['level_one_count']); ?>
                                <i class="fa fa-chevron-down"></i>
                            </a>
                            <i class="fa fa-unlock unlocked"></i>
                        </p>

                        <div class="collapse" id="levelOneDetails">
                            <?php foreach ($levels[1] as $user_name) : ?>
                                <p><?php echo $user_name; ?></p>
                            <?php endforeach; ?>
                        </div>

                        <p>
                            <a href="#levelTwoDetails" class="no-underline" data-toggle="collapse" aria-expanded="false" aria-controls="levelTwoDetails">
                                Level 2 Count: <?php echo htmlspecialchars($user_rewards['level_two_count']); ?>
                                <i class="fa fa-chevron-down"></i>
                            </a>
                            <?php if ($level_two_locked) : ?>
                                <i class="fa fa-lock locked"></i> <small>(Unlock with $30 deposit)</small>
                            <?php else : ?>
                                <i class="fa fa-unlock unlocked"></i>
                            <?php endif; ?>
                        </p>
                        <div class="collapse" id="levelTwoDetails">
                            <?php foreach ($levels[2] as $user_name) : ?>
                                <p><?php echo $user_name; ?></p>
                            <?php endforeach; ?>
                        </div>

                        <p>
                            <a href="#levelThreeDetails" class="no-underline" data-toggle="collapse" aria-expanded="false" aria-controls="levelThreeDetails">
                                Level 3 Count: <?php echo htmlspecialchars($user_rewards['level_three_count']); ?>
                                <i class="fa fa-chevron-down"></i>
                            </a>
                            <?php if ($level_three_locked) : ?>
                                <i class="fa fa-lock locked"></i> <small>(Unlock with $50 deposit)</small>
                            <?php else : ?>
                                <i class="fa fa-unlock unlocked"></i>
                            <?php endif; ?>
                        </p>
                        <div class="collapse" id="levelThreeDetails">
                            <?php foreach ($levels[3] as $user_name) : ?>
                                <p><?php echo $user_name; ?></p>
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyReferralLink() {
        var copyText = document.getElementById("referral-link").innerText;
        navigator.clipboard.writeText(copyText).then(function() {
            document.getElementById("copy-success").style.display = "inline";
            setTimeout(function() {
                document.getElementById("copy-success").style.display = "none";
            }, 2000);
        });
    }
</script>

<?php require('footer.php'); ?>