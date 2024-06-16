<?php
session_start();
require('top.inc.php');

// Ensure only admin can access this script
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: index.php");
//     exit();
// }

// Fetch all payment requests
$stmt = $con->prepare("SELECT up.*, u.name as user_name FROM user_payments up JOIN users u ON up.user_id = u.id WHERE up.status = 'Pending'");
$stmt->execute();
$payment_requests = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Payment Requests</title>
</head>
<body>
<h2>Manage Payment Requests</h2>

<table border="1">
    <thead>
        <tr>
            <th>User</th>
            <th>Name</th>
            <th>Account Number</th>
            <th>Payment Method</th>
            <th>Address</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($request = $payment_requests->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($request['user_name']); ?></td>
                <td><?php echo htmlspecialchars($request['name']); ?></td>
                <td><?php echo htmlspecialchars($request['account_number']); ?></td>
                <td><?php echo htmlspecialchars($request['payment_method']); ?></td>
                <td><?php echo htmlspecialchars($request['address']); ?></td>
                <td><?php echo htmlspecialchars($request['amount']); ?></td>
                <td><?php echo htmlspecialchars($request['status']); ?></td>
                <td>
                    <form action="update_payment_status.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                        <select name="status">
                            <option value="Accepted">Accept</option>
                            <option value="Rejected">Reject</option>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>


<?php require('footer.inc.php'); ?>