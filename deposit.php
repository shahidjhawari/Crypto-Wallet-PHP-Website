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

        // Check if payment method is USDT and give bonus
        if ($payment_method == 'USDT') {
          $bonus_amount = $amount * 0.05; // 5% bonus
          $stmt = $conn->prepare("INSERT INTO bonus_rewards (user_id, transaction_id, bonus_amount) VALUES (?, ?, ?)");
          $stmt->bind_param("isd", $user_id, $transaction_id, $bonus_amount);
          $stmt->execute();
          $stmt->close();
        }

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
$result = $stmt->get_result(); // Get the result set
$latestMessage = $result->fetch_assoc(); // Fetch associative array
$stmt->close();

$exchange_rate = isset($latestMessage['message']) ? floatval($latestMessage['message']) : 0.0;

// Fetch deposits for the logged-in user
$stmt = $conn->prepare("SELECT amount, transaction_id, status, payment_method, screenshot FROM deposits WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$deposits_result = $stmt->get_result();
$stmt->close();




// Fetch deposits for the logged-in user
$stmt = $conn->prepare("SELECT amount, transaction_id, created_at, status, payment_method, rejection_reason FROM deposits WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$deposits_result = $stmt->get_result();
$stmt->close();
?>

<style>
  td {
    color: white;
  }
</style>

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
              <label for="payment_method">Payment Method</label>
              <select class="form-control" id="payment_method" name="payment_method" onchange="toggleFields()" required>
                <!-- <option value="" disabled selected>Select a payment method</option>
                <option value="Easy Paisa">Easy Paisa</option>
                <option value="Valid Cash">Jazz Cash</option>
                <option value="Simple PA">Sada Pay</option> -->
                <option selected value="USDT">USDT</option>
              </select>
            </div>
            <div id="additional_fields"></div>
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
          <p id="converted-amount" class="mt-3"></p>
          <!-- <div class="alert alert-info mt-3">
            By selecting USDT, you will receive a 5% bonus on your deposit!
          </div> -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Responsive Table for Displaying Deposits -->
<div class="container mt-5">
  <h4 class="text-center">Your Deposits History</h4>
  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="thead-light">
        <tr>
        <th scope="col">Date</th>
          <th scope="col">Amount (USD)</th>
          <th scope="col">Transaction ID</th>
          <th scope="col">Status</th>
          <th scope="col">Payment Method</th>
          <th scope="col">Rejection Reason</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $deposits_result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            <td><?php echo htmlspecialchars($row['amount']); ?></td>
            <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
            <td><?php echo htmlspecialchars($row['status'] == 'Rejected' ? $row['rejection_reason'] : ''); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
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
    } else if (paymentMethod === "Simple PA") {
      additionalFields.innerHTML = `
            <div class="form-group">
                <label for="account_number">Account Number</label>
                <input type="text" class="form-control" id="account_number" name="account_number" value="029675456" readonly>
            </div>
            <div class="form-group">
                <label for="account_name">Account Name</label>
                <input type="text" class="form-control" id="account_name" name="account_name" value="Aman Ullah" readonly>
            </div>`;
    }
  }
</script>

<?php
require('footer.php');
?>