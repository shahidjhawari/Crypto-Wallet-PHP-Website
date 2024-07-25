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

    .no-underline {
        text-decoration: none !important;
    }
</style>

<div class="container mt-4">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-12">
                        <p>Referral Link: <span id="referral-link"><?php echo htmlspecialchars($referral_link); ?></span></p>
                        <button onclick="copyReferralLink()" class="btn btn-secondary">Copy Link</button>
                        <span id="copy-success" style="display:none; color: green; margin-left: 10px;">Copied!</span>
                        <hr>
                        <p>Your Reward Points: $<?php echo htmlspecialchars($user_rewards['reward_points']) ?>.00</p>
                        <p>Referral Count: <?php echo htmlspecialchars($user_rewards['referral_count']); ?></p>

                        <p>
                            <a href="#levelOneDetails" class="no-underline" data-toggle="collapse" aria-expanded="false" aria-controls="levelOneDetails">
                                Level 1 Count: <?php echo htmlspecialchars($user_rewards['level_one_count']); ?>
                                <i class="fa fa-chevron-down"></i>
                            </a>
                            <i class="fa fa-unlock unlocked"></i>
                        </p>
                        <div class="collapse" id="levelOneDetails">
                            <!-- Level One Users Details Here -->
                            <?php while ($row = $result->fetch_assoc()) : ?>
                                <p><?php echo htmlspecialchars($row['referred_user']); ?></p>
                            <?php endwhile; ?>
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
                            <!-- Level Two Users Details Here -->
                            <p>Level Two Users List or Details...</p>
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
                            <!-- Level Three Users Details Here -->
                            <p>Level Three Users List or Details...</p>
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