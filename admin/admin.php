<?php
// admin.php
ob_start();
session_start();
require('top.inc.php');


// Clear the earnings processed flag
if (isset($_SESSION['earnings_processed'])) {
    unset($_SESSION['earnings_processed']);
}
?>

<div class="container">
    <h2>Admin Panel: Manage Daily Earnings</h2>
    <form method="post" action="calculate_daily_earnings.php">
        <button type="submit" name="percentage" value="0.0035" class="btn btn-primary">Earn 0.45%</button>
        <button type="submit" name="percentage" value="0.0055" class="btn btn-primary">Earn 0.55%</button>
        <button type="submit" name="percentage" value="0.0065" class="btn btn-primary">Earn 0.65%</button>
    </form>
</div>

<?php require('footer.inc.php'); ?>
