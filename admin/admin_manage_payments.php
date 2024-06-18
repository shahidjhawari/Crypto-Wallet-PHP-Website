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


    <h2>Manage Payment Requests</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Name</th>
            <th>Payment Method</th>
            <th>Account Number</th>
            <th>Address</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php
        $result = $con->query("SELECT * FROM user_payments WHERE status = 'Pending'");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['user_id']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['payment_method']}</td>";
            echo "<td>{$row['account_number']}</td>";
            echo "<td>{$row['address']}</td>";
            echo "<td>{$row['amount']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td>
                    <form action='process_admin_decision.php' method='post'>
                        <input type='hidden' name='id' value='{$row['id']}'>
                        <select name='status' required>
                            <option value='' disabled selected>Select status</option>
                            <option value='Accepted'>Accept</option>
                            <option value='Rejected'>Reject</option>
                        </select>
                        <input type='submit' value='Update'>
                    </form>
                  </td>";
            echo "</tr>";
        }
        ?>
    </table>



<?php require('footer.inc.php'); ?>