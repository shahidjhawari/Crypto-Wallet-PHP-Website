<?php
session_start();
require('header.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $minimum_deposit = 10; // Minimum deposit amount
  $deposit_amount = $_POST['deposit_amount'];
  $deposit_screenshot = $_FILES['deposit_screenshot']['name'];
  $target_dir = PRODUCT_IMAGE_SERVER_PATH; // Define the target directory for deposit screenshots
  $target_file = $target_dir . basename($deposit_screenshot);

  // Validate deposit amount
  if ($deposit_amount < $minimum_deposit) {
    echo "Deposit amount must be at least $minimum_deposit.";
  } else {
    // Move uploaded file to the target directory
    if (move_uploaded_file($_FILES["deposit_screenshot"]["tmp_name"], $target_file)) {
      // Insert deposit details into the database
      $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, screenshot) VALUES (?, ?, ?)");
      $stmt->bind_param("ids", $user_id, $deposit_amount, $deposit_screenshot);
      $stmt->execute();
      $stmt->close();
      echo "Deposit submitted successfully.";
    } else {
      echo "Sorry, there was an error uploading your file.";
    }
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
              <label for="deposit_amount">Deposit Amount</label>
              <input type="number" class="form-control" id="deposit_amount" name="deposit_amount" min="10" required>
            </div>
            <div class="form-group">
              <label for="deposit_screenshot">Screenshot</label>
              <input type="file" class="form-control-file" id="deposit_screenshot" name="deposit_screenshot" required>
            </div>
            <button type="submit" class="btn btn-info btn-block">Submit Deposit</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require('footer.php'); ?>
