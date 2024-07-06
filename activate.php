<?php
session_start();
require('header.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$fixed_amount_usd = 10.0; // Fixed amount in USD

// Fetch the latest exchange rate from the database
$stmt = $conn->prepare("SELECT * FROM admin_messages ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$latestMessage = $result->fetch_assoc();
$stmt->close();

$exchange_rate = isset($latestMessage['message']) ? floatval($latestMessage['message']) : 0.0;
$fixed_amount_pkr = $fixed_amount_usd * $exchange_rate;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $transaction_id = $_POST['transaction_id'];
  $payment_method = $_POST['payment_method'];
  $screenshot = $_FILES['screenshot']['name'];
  $target_dir = PRODUCT_IMAGE_SERVER_PATH; // Use server path to store the file
  $target_file = $target_dir . basename($screenshot);

  // Move uploaded file to the target directory
  if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $target_file)) {
    // Insert transaction details into the database
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, screenshot, transaction_id, status, payment_method) VALUES (?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param("idsss", $user_id, $fixed_amount_usd, $screenshot, $transaction_id, $payment_method); // Save just the file name in the database
    $stmt->execute();
    $stmt->close();

    // Check if payment method is USDT and give bonus
    if ($payment_method == 'USDT') {
      $bonus_amount = $fixed_amount_usd * 0.05; // 5% bonus
      $stmt = $conn->prepare("INSERT INTO bonus_rewards (user_id, transaction_id, bonus_amount) VALUES (?, ?, ?)");
      $stmt->bind_param("isd", $user_id, $transaction_id, $bonus_amount);
      $stmt->execute();
      $stmt->close();
    }

    echo "Transaction submitted successfully.";
  } else {
    echo "Sorry, there was an error uploading your file.";
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
          <h4>Submit Transaction</h4>
          <?php if ($latestMessage) : ?>
            <?php echo "Dollar Rate in PKR " . htmlspecialchars($latestMessage['message']); ?>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <form method="post" enctype="multipart/form-data">
            <div class="form-group">
              <label for="amount">Amount (USD)</label>
              <input type="text" class="form-control" id="amount" name="amount" value="$<?php echo $fixed_amount_usd; ?>" readonly>
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
          <p id="converted-amount" class="mt-3">Equivalent Amount in PKR: <?php echo number_format($fixed_amount_pkr, 2); ?></p>
          <div class="alert alert-info mt-3">
            By selecting USDT, you will receive a 5% bonus on your deposit!
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
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
