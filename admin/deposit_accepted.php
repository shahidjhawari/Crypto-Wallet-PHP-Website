<?php
require('top.inc.php');

// Fetch all rejected transactions
$stmt = $con->prepare("SELECT * FROM deposits WHERE status = 'Accepted'");
$stmt->execute();
$rejected_transactions = $stmt->get_result();
$stmt->close();
?>


<div class="container">
    <h2>Accepted Account Activation</h2>

    <!-- Search input field -->
    <input type="text" id="searchUserId" placeholder="Search by User ID" class="form-control mb-3">

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody id="transactionTableBody">
            <?php while ($transaction = $rejected_transactions->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['new_amount']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                    <td>
                        <img src="<?php echo PRODUCT_IMAGE_SITE_PATH . $transaction['screenshot']; ?>" class="img-fluid" alt="Screenshot" width="100">
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    document.getElementById('searchUserId').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#transactionTableBody tr');

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