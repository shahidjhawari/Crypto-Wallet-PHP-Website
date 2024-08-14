<?php
require('top.inc.php');

// Fetch all users
$stmt = $con->prepare("SELECT * FROM users");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<div class="container mt-5">
    <h2 class="text-center">All Users</h2>

    <!-- Search input field -->
    <input type="text" id="searchUserId" placeholder="Search by User ID" class="form-control mb-3">

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Private Key</th>
                <th>Referrer ID</th>
                <th>Referrer Code</th>
            </tr>
        </thead>
        <tbody id="userTableBody">
            <?php while ($user = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['random_string']); ?></td>
                    <td><?php echo htmlspecialchars($user['referrer_id']); ?></td>
                    <td><?php echo htmlspecialchars($user['referral_code']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    document.getElementById('searchUserId').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#userTableBody tr');

        rows.forEach(function(row) {
            const userId = row.cells[0].textContent.toLowerCase();

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


<?php
require('footer.inc.php');
?>