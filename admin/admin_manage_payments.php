<?php
require('top.inc.php');

// Ensure only admin can access this script
// Uncomment these lines if you have role-based access control in your application
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: index.php");
//     exit();
// }

// Fetch all pending payment requests
$stmt = $con->prepare("SELECT up.*, u.name as user_name FROM user_payments up JOIN users u ON up.user_id = u.id WHERE up.status = 'Pending'");
$stmt->execute();
$payment_requests = $stmt->get_result();
$stmt->close();
?>

<div class="container mt-5">
        <h2>Manage Payment Requests</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>User Name</th>
                        <th>Payment Method</th>
                        <th>Account Number</th>
                        <th>Address</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $payment_requests->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                            <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['amount']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td>
                                <form action="process_admin_decision.php" method="post">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <select name="status" class="form-control" required>
                                        <option value="" disabled selected>Select status</option>
                                        <option value="Accepted">Accept</option>
                                        <option value="Rejected">Reject</option>
                                    </select>
                                    <input type="submit" value="Update" class="btn btn-primary btn-sm mt-2">
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require('footer.inc.php'); ?>