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