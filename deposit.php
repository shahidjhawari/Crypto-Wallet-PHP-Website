<?php
session_start();
require('header.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
  echo "<script>window.location.href = 'index.php';</script>";
  exit();
}

$user_id = $_SESSION['user_id'];
$min_amount = 10; // Minimum deposit amount

$deposit_message = ""; // Variable to hold messages for the user

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $amount = $_POST['amount'];
  $transaction_id = $_POST['transaction_id'];
  $payment_method = $_POST['payment_method'];
  $screenshot = $_FILES['screenshot']['name'];
  $target_dir = PRODUCT_IMAGE_SERVER_PATH; // Use server path to store the file
  $target_file = $target_dir . basename($screenshot);

  // Validate amount
  if ($amount < $min_amount) {
    $deposit_message = "The deposit amount should be at least $$min_amount.";
  } else {
    // Check if transaction ID already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM deposits WHERE transaction_id = ?");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
      $deposit_message = "This transaction ID has already been used.";
    } else {
      // Move uploaded file to the target directory
      if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $target_file)) {
        // Insert deposit details into the database with status 'Pending'
        $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, screenshot, transaction_id, status, payment_method) VALUES (?, ?, ?, ?, 'Pending', ?)");
        $stmt->bind_param("iisss", $user_id, $amount, $screenshot, $transaction_id, $payment_method);
        $stmt->execute();
        $stmt->close();

        // JavaScript redirect to dashboard page
        echo "<script>alert('Deposit submitted successfully.'); window.location.href = 'dashboard.php';</script>";
        exit();
      } else {
        $deposit_message = "Sorry, there was an error uploading your file.";
      }
    }
  }
}

// Fetch the latest exchange rate from the database
$stmt = $conn->prepare("SELECT * FROM admin_messages ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$latestMessage = $result->fetch_assoc();
$stmt->close();

$exchange_rate = isset($latestMessage['message']) ? floatval($latestMessage['message']) : 0.0;
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header text-center">
          <h4>Make a Deposit</h4>
          <?php if ($latestMessage) : ?>
            <?php echo "Dollar Rate in PKR " . htmlspecialchars($latestMessage['message']); ?>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <?php if ($deposit_message) : ?>
            <div class="alert alert-danger" style="color: white;">
              <?php echo $deposit_message; ?>
            </div>
          <?php endif; ?>
          <form method="post" enctype="multipart/form-data">
            <div class="form-group">
              <label for="amount">Amount (USD)</label>
              <input type="number" class="form-control" id="amount" name="amount" required>
            </div>
            <div class="form-group">
              <label for="transaction_id">Transaction ID</label>
              <input type="text" class="form-control" id="transaction_id" name="transaction_id" required>
            </div>
            <div class="form-group">
              <label for="payment_method">Payment Method</label>
              <select class="form-control" id="payment_method" name="payment_method" onchange="toggleFields()" required>
                <option value="" disabled selected>Select a payment method</option>
                <option value="Easy Paisa">Easy Paisa</option>
                <option value="Valid Cash">Jazz Cash</option>
                <option value="Simple PA">Sada Pay</option>
                <option value="USDT">USDT</option>
              </select>
            </div>
            <div id="additional_fields"></div>
            <div class="form-group">
              <label for="screenshot">Screenshot</label>
              <input type="file" class="form-control-file" id="screenshot" name="screenshot" required>
            </div>
            <button type="submit" class="btn btn-info btn-block">Submit</button>
          </form>
          <p id="converted-amount" class="mt-3"></p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('amount').addEventListener('input', function() {
    const exchangeRate = <?php echo $exchange_rate; ?>;
    const usdAmount = parseFloat(this.value);
    if (!isNaN(usdAmount) && usdAmount > 0) {
      const pkrAmount = usdAmount * exchangeRate;
      document.getElementById('converted-amount').innerText = `Equivalent Amount in PKR: ${pkrAmount.toFixed(2)}`;
    } else {
      document.getElementById('converted-amount').innerText = '';
    }
  });

  function toggleFields() {
    var paymentMethod = document.getElementById("payment_method").value;
    var additionalFields = document.getElementById("additional_fields");
    additionalFields.innerHTML = ""; // Clear existing fields

    if (paymentMethod === "Easy Paisa") {
      additionalFields.innerHTML = `
      <div class="form-group">
        <label for="account_number">Account Number</label>
        <input type="text" class="form-control" id="account_number" name="account_number" value="03236645813" readonly>
      </div>
      <div class="form-group">
        <label for="account_name">Account Name</label>
        <input type="text" class="form-control" id="account_name" name="account_name" value="Aman Ullah" readonly>
      </div>`;
    } else if (paymentMethod === "Valid Cash") {
      additionalFields.innerHTML = `
      <div class="form-group">
        <label for="account_number">Account Number</label>
        <input type="text" class="form-control" id="account_number" name="account_number" value="03046978556" readonly>
      </div>
      <div class="form-group">
        <label for="account_name">Account Name</label>
        <input type="text" class="form-control" id="account_name" name="account_name" value="Aman Ullah" readonly>
      </div>`;
    } else if (paymentMethod === "USDT") {
      additionalFields.innerHTML = `
      <div class="form-group">
        <label for="network">Network</label>
        <input type="text" class="form-control" id="network" name="network" value="TRC 20" readonly>
      </div>
      <div class="form-group">
        <label for="address">Address</label>
        <input type="text" class="form-control" id="address" name="address" value="TChLhd7z7vPRT79dq1oCoiDPrWDG3tRA96" readonly>
      </div>`;
    } else if (paymentMethod === "Simple PA") {
      additionalFields.innerHTML = `
      <div class="form-group">
        <label for="account_number">Account Number</label>
        <input type="text" class="form-control" id="account_number" name="account_number" value="03046978556" readonly>
      </div>
      <div class="form-group">
        <label for="account_name">Account Name</label>
        <input type="text" class="form-control" id="account_name" name="account_name" value="Aman Ullah" readonly>
      </div>`;
    }
  }
</script>

<?php require('footer.php'); ?>