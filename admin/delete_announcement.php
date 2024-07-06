<?php
ob_start();
session_start();
require('top.inc.php');

// Ensure only admin can access
// if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
//   header("Location: index.php");
//   exit();
// }

$id = $_GET['id'];

$stmt = $con->prepare("DELETE FROM announcements WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: manage_announcements.php");
exit();
?>

<?php require('footer.inc.php'); ?>
