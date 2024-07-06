<?php
session_start();
require('header.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the user's withdrawal requests
$stmt = $conn->prepare("SELECT id, amount, payment_method, status, created_at FROM user_payments WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Fetch accepted payments
$stmt_accepted = $conn->prepare("SELECT id, amount, payment_method, created_at FROM user_payments WHERE user_id = ? AND status = 'Accepted' ORDER BY created_at DESC");
$stmt_accepted->bind_param("i", $user_id);
$stmt_accepted->execute();
$result_accepted = $stmt_accepted->get_result();
$stmt_accepted->close();

// Calculate the total amount of accepted payments
$total_accepted_amount = 0;
while ($row_accepted = $result_accepted->fetch_assoc()) {
    $total_accepted_amount += $row_accepted['amount'];
    $accepted_payments[] = $row_accepted;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Withdrawal Status</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <h1>Withdrawal Requests</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['amount']; ?></td>
                    <td><?php echo $row['payment_method']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h1>Accepted Payments</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accepted_payments as $row_accepted) { ?>
                <tr>
                    <td><?php echo $row_accepted['id']; ?></td>
                    <td><?php echo $row_accepted['amount']; ?></td>
                    <td><?php echo $row_accepted['payment_method']; ?></td>
                    <td><?php echo $row_accepted['created_at']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <h2>Total Amount Received: <?php echo number_format($total_accepted_amount, 2); ?></h2>
</body>
</html>
