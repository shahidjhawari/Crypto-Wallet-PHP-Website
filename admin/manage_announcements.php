<?php
require('top.inc.php');

// Ensure only admin can access
// if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
//   header("Location: index.php");
//   exit();
// }

// Fetch announcements
$stmt = $con->prepare("SELECT * FROM announcements ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$announcements = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<table class="table">
  <thead>
    <tr>
      <th>Message</th>
      <th>Date</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($announcements as $announcement): ?>
    <tr>
      <td><?php echo htmlspecialchars($announcement['message']); ?></td>
      <td><?php echo htmlspecialchars($announcement['announcement_date']); ?></td>
      <td>
        <a href="edit_announcement.php?id=<?php echo $announcement['id']; ?>" class="btn btn-warning">Edit</a>
        <a href="delete_announcement.php?id=<?php echo $announcement['id']; ?>" class="btn btn-danger">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require('footer.inc.php'); ?>
