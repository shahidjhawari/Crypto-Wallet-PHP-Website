<?php
session_start();
require('header.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$error_message = ''; // Initialize error message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $amount = $_POST['amount'];
  $transaction_id = $_POST['transaction_id'];
  $screenshot = $_FILES['screenshot']['name'];
  $target_dir = 'upload/'; // Use relative path to store the file

  // Ensure the upload directory exists
  if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
  }

  $target_file = $target_dir . basename($screenshot);

  // Check if the amount is at least 10
  if ($amount >= 10) {
    // Move uploaded file to the target directory
    if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $target_file)) {
      // Insert deposit details into the database with status 'Pending'
      $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, screenshot, transaction_id, status) VALUES (?, ?, ?, ?, 'Pending')");
      $stmt->bind_param("iiss", $user_id, $amount, $screenshot, $transaction_id);
      $stmt->execute();
      $stmt->close();
      echo "Deposit submitted successfully.";
    } else {
      $error_message = "Sorry, there was an error uploading your file.";
    }
  } else {
    $error_message = "Amount must be at least $10.";
  }
}
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header text-center">
          <h4>Make a Deposit</h4>
        </div>
        <div class="card-body">
          <form method="post" enctype="multipart/form-data">
            <div class="form-group">
              <label for="amount">Amount</label>
              <input type="number" class="form-control" id="amount" name="amount" min="10" required>
              <?php if ($error_message) : ?>
                <small class="text-danger"><?php echo htmlspecialchars($error_message); ?></small>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label for="transaction_id">Transaction ID</label>
              <input type="text" class="form-control" id="transaction_id" name="transaction_id" required>
            </div>
            <div class="form-group">
              <label for="screenshot">Screenshot</label>
              <input type="file" class="form-control-file" id="screenshot" name="screenshot" required>
            </div>
            <button type="submit" class="btn btn-info btn-block">Submit</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require('footer.php'); ?>