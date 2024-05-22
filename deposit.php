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
  $amount = 10; // Minimum amount
  $transaction_id = $_POST['transaction_id'];
  $screenshot = $_FILES['screenshot']['name'];
  $target_dir = PRODUCT_IMAGE_SERVER_PATH; // Use server path to store the file
  $target_file = $target_dir . basename($screenshot);

  // Move uploaded file to the target directory
  if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $target_file)) {
    // Insert deposit details into the database
    $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, screenshot, transaction_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $user_id, $amount, $screenshot, $transaction_id); // Save just the file name in the database
    $stmt->execute();
    $stmt->close();
    echo "Deposit submitted successfully.";
  } else {
    echo "Sorry, there was an error uploading your file.";
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
          <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="form-group">
              <label for="amount">Amount</label>
              <input type="number" class="form-control" id="amount" name="amount" required>
              <span id="amountError" style="color: red;"></span>
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


<script>
  function validateForm() {
    var amount = document.getElementById("amount").value;
    if (amount < 10) {
      document.getElementById("amountError").innerHTML = "Amount should be at least $10.";
      return false;
    }
    return true;
  }
</script>

<?php require('footer.php'); ?>