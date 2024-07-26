<?php
ob_start();
require('top.inc.php');

// Ensure only admin can access this page
// if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
//     header("Location: index.php");
//     exit();
// }

// Fetch accepted staking requests
$accepted_requests = [];
$stmt = $con->prepare("SELECT * FROM staking_requests WHERE status = 'accepted'");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $accepted_requests[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Panel - Accepted Staking Requests</title>
</head>

<body>
    <h2>Admin Panel - Accepted Staking Requests</h2>

    <table>
        <thead>
            <tr>
                <th>Request ID</th>
                <th>User ID</th>
                <th>Stake Amount</th>
                <th>Status</th>
                <th>Request Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accepted_requests as $request) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($request['id']); ?></td>
                    <td><?php echo htmlspecialchars($request['user_id']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($request['stake_amount'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($request['status']); ?></td>
                    <td><?php echo htmlspecialchars($request['request_date']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php require('footer.inc.php'); ?>
</body>

</html>
