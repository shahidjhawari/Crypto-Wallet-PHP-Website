<?php
session_start();
require('top.inc.php');



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $con->prepare("SELECT id, referrer_id FROM users WHERE referrer_id IS NOT NULL");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($user = $result->fetch_assoc()) {
        $user_id = $user['id'];
        $referrer_id = $user['referrer_id'];

        // Check transaction status
        $stmt2 = $con->prepare("SELECT status FROM transactions WHERE user_id = ? AND status = 'accepted'");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $stmt2->store_result();

        if ($stmt2->num_rows > 0) {
            $stmt2->close();

            // Reward referrer
            rewardReferrer($referrer_id, 10, 1);
        } else {
            $stmt2->close();
        }
    }
    $stmt->close();
}

function rewardReferrer($referrer_id, $points, $level)
{
    global $con;
    if ($level > 3) {
        return;
    }

    // Update rewards and level count for the current referrer
    if ($level == 1) {
        $stmt = $con->prepare("UPDATE rewards SET reward_points = reward_points + ?, referral_count = referral_count + 1, level_one_count = level_one_count + 1 WHERE user_id = ?");
    } elseif ($level == 2) {
        $stmt = $con->prepare("UPDATE rewards SET reward_points = reward_points + ?, level_two_count = level_two_count + 1 WHERE user_id = ?");
    } else {
        $stmt = $con->prepare("UPDATE rewards SET reward_points = reward_points + ?, level_three_count = level_three_count + 1 WHERE user_id = ?");
    }
    $stmt->bind_param("ii", $points, $referrer_id);
    $stmt->execute();
    $stmt->close();

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
            $next_points = ($level == 1) ? 5 : 2;
            rewardReferrer($next_referrer_id, $next_points, $level + 1);
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