<?php
require('top.inc.php');

// Ensure only admin can access this script
// Uncomment these lines if you have role-based access control in your application
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: index.php");
//     exit();
// }

// Fetch all accepted payment requests
$stmt = $con->prepare("SELECT up.*, u.name as user_name FROM user_payments up JOIN users u ON up.user_id = u.id WHERE up.status = 'Accepted'");
$stmt->execute();
$accepted_payments = $stmt->get_result();
$stmt->close();
?>

<div class="container mt-5">
    <h2>Accepted Payment Requests</h2>

    <!-- Search input field -->
    <input type="text" id="searchUserId" placeholder="Search by User ID" class="form-control mb-3">

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
                </tr>
            </thead>
            <tbody id="paymentTableBody">
                <?php while ($row = $accepted_payments->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                        <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('searchUserId').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#paymentTableBody tr');

        rows.forEach(function(row) {
            const userId = row.cells[1].textContent.toLowerCase();

            if (userId.indexOf(searchValue) > -1) {
                row.style.display = '';
                // Move the matched row to the top of the table
                row.parentNode.prepend(row);
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>


<?php require('footer.inc.php'); ?>