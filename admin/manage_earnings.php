<?php
ob_start();
require('top.inc.php');

// Fetch current percentages from the database or use defaults if not set
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $percentage1 = $_POST['percentage1'];
    $percentage2 = $_POST['percentage2'];
    $percentage3 = $_POST['percentage3'];

    // Update the database with new values
    $stmt = $con->prepare("REPLACE INTO settings (key_name, key_value) VALUES 
        ('earning_percentage_1', ?), 
        ('earning_percentage_2', ?), 
        ('earning_percentage_3', ?)");
    $stmt->bind_param("sss", $percentage1, $percentage2, $percentage3);
    $stmt->execute();
    $stmt->close();

    // Redirect to the same page to show updated values
    header("Location: manage_earnings.php");
    exit();
}
?>

<div class="container">
    <h2>Admin Panel: Manage Earnings Percentages</h2>
    <form method="post">
        <div class="form-group">
            <label for="percentage1">Earnings Percentage 1:</label>
            <input type="text" class="form-control" id="percentage1" name="percentage1" value="<?php echo htmlspecialchars($percentages['earning_percentage_1']); ?>">
        </div>
        <div class="form-group">
            <label for="percentage2">Earnings Percentage 2:</label>
            <input type="text" class="form-control" id="percentage2" name="percentage2" value="<?php echo htmlspecialchars($percentages['earning_percentage_2']); ?>">
        </div>
        <div class="form-group">
            <label for="percentage3">Earnings Percentage 3:</label>
            <input type="text" class="form-control" id="percentage3" name="percentage3" value="<?php echo htmlspecialchars($percentages['earning_percentage_3']); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update Percentages</button>
    </form>
</div>

<?php require('footer.inc.php'); ?>