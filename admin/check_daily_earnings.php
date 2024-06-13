<?php
session_start();
require('top.inc.php');

// Only admin users can access this page
// if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
//     header("Location: index.php");
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get current datetime
    $current_datetime = date('Y-m-d H:i:s');

    // Fetch users who have referrers and daily earnings that have not been rewarded yet
    $stmt = $con->prepare("SELECT u.id AS user_id, u.referrer_id, de.amount, de.id AS earning_id
                            FROM users u
                            JOIN daily_earnings de ON u.id = de.user_id
                            WHERE u.referrer_id IS NOT NULL AND de.rewarded = FALSE");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        $referrer_id = $row['referrer_id'];
        $daily_earning = $row['amount'];
        $reward = $daily_earning * 0.10;

        // Reward the referrer
        $stmt2 = $con->prepare("UPDATE rewards SET reward_points = reward_points + ? WHERE user_id = ?");
        $stmt2->bind_param("di", $reward, $referrer_id);
        $stmt2->execute();
        $stmt2->close();

        // Mark the daily earnings as rewarded
        $stmt3 = $con->prepare("UPDATE daily_earnings SET rewarded = TRUE WHERE id = ?");
        $stmt3->bind_param("i", $row['earning_id']);
        $stmt3->execute();
        $stmt3->close();
    }

    $stmt->close();

    echo '<div class="alert alert-success text-center" role="alert">Rewards have been distributed successfully!</div>';
}
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header text-center">
          <h4>Admin Panel: Check Daily Earnings and Reward Referrers</h4>
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
