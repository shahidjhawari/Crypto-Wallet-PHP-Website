<?php
ob_start();
require('top.inc.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $deposit_id = $_POST['deposit_id'];
  $action = $_POST['action'];
  $rejection_reason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : null;

  if ($action == 'Accept') {
    $stmt = $con->prepare("UPDATE deposits SET status = 'Accepted' WHERE id = ?");
  } elseif ($action == 'Reject') {
    $stmt = $con->prepare("UPDATE deposits SET status = 'Rejected', rejection_reason = ? WHERE id = ?");
    $stmt->bind_param("si", $rejection_reason, $deposit_id);
  }

  $stmt->execute();
  $stmt->close();

  header("Location: admin_deposits.php");
  exit();
}
