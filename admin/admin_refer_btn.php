<?php
require('top.inc.php');

// Only admin users can access this page
// if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
//     header("Location: index.php");
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $con->prepare("SELECT id, referrer_id FROM users WHERE referrer_id IS NOT NULL");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($user = $result->fetch_assoc()) {
        $user_id = $user['id'];
        $referrer_id = $user['referrer_id'];

        // Check transaction status and if reward has already been given
        $stmt2 = $con->prepare("SELECT id FROM transactions WHERE user_id = ? AND status = 'accepted' AND rewarded = FALSE");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $stmt2->store_result();

        if ($stmt2->num_rows > 0) {
            $stmt2->bind_result($transaction_id);
            while ($stmt2->fetch()) {
                // Update referral counts for all levels and reward points for Level 1
                updateReferralCountsAndReward($referrer_id, 5, 1);

                // Mark the transaction as rewarded
                $stmt3 = $con->prepare("UPDATE transactions SET rewarded = TRUE WHERE id = ?");
                $stmt3->bind_param("i", $transaction_id);
                $stmt3->execute();
                $stmt3->close();
            }
        }
        $stmt2->close();
    }
    $stmt->close();
}

function updateReferralCountsAndReward($referrer_id, $points, $level)
{
    global $con;

    // Update referral counts for all levels
    if ($level == 1) {
        $stmt = $con->prepare("UPDATE rewards SET referral_count = referral_count + 1, level_one_count = level_one_count + 1 WHERE user_id = ?");
    } elseif ($level == 2) {
        $stmt = $con->prepare("UPDATE rewards SET level_two_count = level_two_count + 1 WHERE user_id = ?");
    } else {
        $stmt = $con->prepare("UPDATE rewards SET level_three_count = level_three_count + 1 WHERE user_id = ?");
    }
    $stmt->bind_param("i", $referrer_id);
    $stmt->execute();
    $stmt->close();

    // Reward points only for Level 1
    if ($level == 1) {
        $stmt = $con->prepare("UPDATE rewards SET reward_points = reward_points + ? WHERE user_id = ?");
        $stmt->bind_param("ii", $points, $referrer_id);
        $stmt->execute();
        $stmt->close();
    }

    if ($level < 3) {
        // Get the next level referrer
        $stmt = $con->prepare("SELECT referrer_id FROM users WHERE id = ?");
        $stmt->bind_param("i", $referrer_id);
        $stmt->execute();
        $stmt->bind_result($next_referrer_id);
        $stmt->fetch();
        $stmt->close();

        if ($next_referrer_id !== null) {
            // Determine points for the next level
            $next_points = ($level == 1) ? 5 : 0; // No points for levels 2 and 3
            updateReferralCountsAndReward($next_referrer_id, $next_points, $level + 1);
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h4>Admin Panel: Reward Referrers</h4>
                </div>
                <div class="card-body">
                    <form method="post">
                        <button type="submit" class="btn btn-info btn-block">Check and Reward Referrers</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require('footer.inc.php'); ?>