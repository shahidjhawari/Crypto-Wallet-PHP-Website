<?php
session_start();
require('top.inc.php');

// Ensure only admin can access
// if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
//   header("Location: index.php");
//   exit();
// }

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $message = $_POST['message'];
  $announcement_date = $_POST['announcement_date'];

  $stmt = $con->prepare("UPDATE announcements SET message = ?, announcement_date = ? WHERE id = ?");
  $stmt->bind_param("ssi", $message, $announcement_date, $id);
  $stmt->execute();
  $stmt->close();

  echo "Announcement updated successfully.";
} else {
  $stmt = $con->prepare("SELECT * FROM announcements WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $announcement = $result->fetch_assoc();
  $stmt->close();
}
?>

<form method="post">
  <div class="form-group">
    <label for="message">Message</label>
    <textarea class="form-control" id="message" name="message" required><?php echo htmlspecialchars($announcement['message']); ?></textarea>
  </div>
  <div class="form-group">
    <label for="announcement_date">Date</label>
    <input type="date" class="form-control" id="announcement_date" name="announcement_date" value="<?php echo htmlspecialchars($announcement['announcement_date']); ?>" required>
  </div>
  <button type="submit" class="btn btn-primary">Update Announcement</button>
</form>

<?php require('footer.inc.php'); ?>
