<?php
session_start();
require('top.inc.php');

// Ensure only admin can access
// if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
//   header("Location: index.php");
//   exit();
// }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $message = $_POST['message'];
  $announcement_date = $_POST['announcement_date'];

  $stmt = $con->prepare("INSERT INTO announcements (message, announcement_date) VALUES (?, ?)");
  $stmt->bind_param("ss", $message, $announcement_date);
  $stmt->execute();
  $stmt->close();

  echo "Announcement added successfully.";
}
?>

<form method="post">
  <div class="form-group">
    <label for="message">Message</label>
    <textarea class="form-control" id="message" name="message" required></textarea>
  </div>
  <div class="form-group">
    <label for="announcement_date">Date</label>
    <input type="date" class="form-control" id="announcement_date" name="announcement_date" required>
  </div>
  <button type="submit" class="btn btn-primary">Add Announcement</button>
</form>

<?php require('footer.inc.php'); ?>
