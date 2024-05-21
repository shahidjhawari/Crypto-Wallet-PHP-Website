<?php
require('top.inc.php');

// Fetch all pending transactions
$stmt = $con->prepare("SELECT * FROM transactions WHERE status = 'pending'");
$stmt->execute();
$transactions = $stmt->get_result();
$stmt->close();
?>

<head>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <title>Transactions</title>
</head>

<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>User ID</th>
      <th>Amount</th>
      <th>Screenshot</th>
      <th>Transaction ID</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($transaction = $transactions->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($transaction['id']); ?></td>
        <td><?php echo htmlspecialchars($transaction['user_id']); ?></td>
        <td><?php echo htmlspecialchars($transaction['amount']); ?></td>
        <td><img src="<?php echo htmlspecialchars($transaction['screenshot']); ?>" width="100"></td>
        <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
        <td><?php echo htmlspecialchars($transaction['status']); ?></td>
        <td>
          <form method="post" action="admin_process.php">
            <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
            <input type="submit" name="action" value="Accept">
            <input type="submit" name="action" value="Reject">
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>


<?php require('footer.inc.php');
