<?php
ob_start();
require('top.inc.php');

// Fetch current percentages from the database
$query = "SELECT key_name, key_value FROM settings WHERE key_name IN ('earning_percentage_1', 'earning_percentage_2', 'earning_percentage_3')";
$result = $con->query($query);

$percentages = [
    'earning_percentage_1' => '0.0035',
    'earning_percentage_2' => '0.0045',
    'earning_percentage_3' => '0.0055',
];

while ($row = $result->fetch_assoc()) {
    $percentages[$row['key_name']] = $row['key_value'];
}

?>

<div class="container">
    <h2>Admin Panel: Manage Daily Earnings</h2>
    <form method="post" action="calculate_daily_earnings.php">
        <button type="submit" name="percentage" value="<?php echo htmlspecialchars($percentages['earning_percentage_1']); ?>" class="btn btn-primary">
            Earn <?php echo htmlspecialchars($percentages['earning_percentage_1'] * 100); ?>%
        </button>
        <button type="submit" name="percentage" value="<?php echo htmlspecialchars($percentages['earning_percentage_2']); ?>" class="btn btn-primary">
            Earn <?php echo htmlspecialchars($percentages['earning_percentage_2'] * 100); ?>%
        </button>
        <button type="submit" name="percentage" value="<?php echo htmlspecialchars($percentages['earning_percentage_3']); ?>" class="btn btn-primary">
            Earn <?php echo htmlspecialchars($percentages['earning_percentage_3'] * 100); ?>%
        </button>
    </form>
</div>

<?php require('footer.inc.php'); ?>