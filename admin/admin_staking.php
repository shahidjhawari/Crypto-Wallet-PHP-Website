<?php
ob_start();
require('top.inc.php');

// Ensure only admin can access this page
// if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
//     header("Location: index.php");
//     exit();
// }

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    // Fetch staking request details
    $stmt = $con->prepare("SELECT * FROM staking_requests WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($request && $request['status'] === 'pending') {
        if ($action === 'accept') {
            $user_id = $request['user_id'];
            $stake_amount = $request['stake_amount'];
            $estimated_earning = 3 * $stake_amount;
            $remaining_earning = $estimated_earning;

            // Insert staking record
            $stmt = $con->prepare("INSERT INTO stakings (user_id, amount, estimated_earning, remaining_earning, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->bind_param("iddd", $user_id, $stake_amount, $estimated_earning, $remaining_earning);
            $stmt->execute();
            $stmt->close();

            // Deduct the staked amount from the user's deposits
            $remaining_to_deduct = $stake_amount;
            while ($remaining_to_deduct > 0) {
                $stmt = $con->prepare("SELECT id, amount FROM deposits WHERE user_id = ? AND status = 'accepted' AND amount > 0 ORDER BY id ASC LIMIT 1");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $deposit = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($deposit) {
                    $deposit_id = $deposit['id'];
                    $deposit_amount = $deposit['amount'];

                    if ($deposit_amount >= $remaining_to_deduct) {
                        $stmt = $con->prepare("UPDATE deposits SET amount = amount - ? WHERE id = ?");
                        $stmt->bind_param("di", $remaining_to_deduct, $deposit_id);
                        $stmt->execute();
                        $stmt->close();
                        $remaining_to_deduct = 0;
                    } else {
                        $stmt = $con->prepare("UPDATE deposits SET amount = 0 WHERE id = ?");
                        $stmt->bind_param("i", $deposit_id);
                        $stmt->execute();
                        $stmt->close();
                        $remaining_to_deduct -= $deposit_amount;
                    }
                } else {
                    break; // No more deposits to deduct from
                }
            }

            // Update request status
            $stmt = $con->prepare("UPDATE staking_requests SET status = 'accepted' WHERE id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'reject') {
            // Update request status
            $stmt = $con->prepare("UPDATE staking_requests SET status = 'rejected' WHERE id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: admin_staking.php");
    exit();
}

// Fetch pending staking requests
$pending_requests = [];
$stmt = $con->prepare("SELECT * FROM staking_requests WHERE status = 'pending'");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pending_requests[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Panel</title>
</head>

<body>
    <h2>Admin Panel</h2>

    <h3>Pending Staking Requests</h3>
    <table>
        <thead>
            <tr>
                <th>Request ID</th>
                <th>User ID</th>
                <th>Stake Amount</th>
                <th>Status</th>
                <th>Request Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending_requests as $request) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($request['id']); ?></td>
                    <td><?php echo htmlspecialchars($request['user_id']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($request['stake_amount'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($request['status']); ?></td>
                    <td><?php echo htmlspecialchars($request['request_date']); ?></td>
                    <td>
                        <form method="post" action="admin_staking.php">
                            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                            <button type="submit" name="action" value="accept">Accept</button>
                            <button type="submit" name="action" value="reject">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php require('footer.inc.php'); ?>
</body>

</html>