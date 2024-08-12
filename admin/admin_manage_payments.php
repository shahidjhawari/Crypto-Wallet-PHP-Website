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
                            <form action="process_admin_decision.php" method="POST" id="admin-decision-form">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">

                                <select name="status" id="status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="Accepted">Accepted</option>
                                    <option value="Rejected">Rejected</option>
                                </select>

                                <textarea name="rejection_reason" id="rejection_reason" class="form-control mt-2" placeholder="Reason for rejection" style="display:none;"></textarea>

                                <button type="submit" class="btn btn-primary mt-2">Submit</button>
                            </form>
                            <script>
                                const statusSelect = document.getElementById('status');
                                const rejectionReason = document.getElementById('rejection_reason');

                                statusSelect.addEventListener('change', function() {
                                    if (this.value === 'Rejected') {
                                        rejectionReason.style.display = 'block';
                                        rejectionReason.setAttribute('required', 'required');
                                    } else {
                                        rejectionReason.style.display = 'none';
                                        rejectionReason.removeAttribute('required');
                                    }
                                });
                            </script>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require('footer.inc.php'); ?>