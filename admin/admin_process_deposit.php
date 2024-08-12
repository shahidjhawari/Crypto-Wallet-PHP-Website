<?php
ob_start();
require('top.inc.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $deposit_id = $_POST['deposit_id'];
  $action = $_POST['action'];

  if ($action == 'Accept') {
    // Prepare the statement to accept the deposit
    $stmt = $con->prepare("UPDATE deposits SET status = 'Accepted' WHERE id = ?");
    $stmt->bind_param("i", $deposit_id);
  } elseif ($action == 'Reject') {
    $rejection_reason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : null;

    // Prepare the statement to reject the deposit with a reason
    $stmt = $con->prepare("UPDATE deposits SET status = 'Rejected', rejection_reason = ? WHERE id = ?");
    $stmt->bind_param("si", $rejection_reason, $deposit_id);
  }

  // Execute the prepared statement
  if ($stmt->execute()) {
    // Redirect back to the admin deposits page after updating
    header("Location: admin_deposits.php");
    exit();
  } else {
    echo "Error updating record: " . $con->error;
  }

  // Close the statement
  $stmt->close();
}
