<?php
ob_start();
session_start();
require('top.inc.php');

// Ensure only admin can access this script
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: index.php");
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Validate input
    if (empty($id) || empty($status)) {
        echo "Error: All fields are required.";
        exit();
    }

    // Update the status of the payment request
    $stmt = $con->prepare("UPDATE user_payments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_manage_payments.php");
} else {
    echo "Invalid request.";
}
?>
