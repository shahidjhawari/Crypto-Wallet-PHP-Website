<?php
ob_start();
session_start();
require('header.php'); // Include database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Display error or success messages
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

if (isset($_GET['submitted']) && $_GET['submitted'] == 'true') {
    echo '<div class="alert alert-success" role="alert">Your form has been submitted successfully.</div>';
}

// Fetch the user's withdrawal requests
$stmt = $conn->prepare("SELECT id, amount, payment_method, status, created_at FROM user_payments WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();



$stmt = $conn->prepare("SELECT id, amount, payment_method, status, created_at, rejection_reason FROM user_payments WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

?>

<script>
    function toggleFields() {
        var paymentMethod = document.getElementById("payment_method").value;
        var addressField = document.getElementById("address_field");
        var accountNumberField = document.getElementById("account_number_field");
        var feeInfo = document.getElementById("fee_info");
        var amount = document.getElementById("amount").value;
        var fee = 0;

        if (paymentMethod === "USDTP") {
            addressField.style.display = "none";
            accountNumberField.style.display = "block";
            fee = 0.03 * amount; // 3% fee for USDTP
            feeInfo.textContent = "Note: A 3% fee will be deducted.";
        } else if (paymentMethod === "Dollar") {
            addressField.style.display = "block";
            accountNumberField.style.display = "none";
            fee = 0.03 * amount; // 3% fee for Dollar (USDT)
            feeInfo.textContent = "Note: A 3% fee will be deducted.";
        } else {
            addressField.style.display = "none";
            accountNumberField.style.display = "block";
            fee = 0.02 * amount; // 5% fee for other payment methods
            feeInfo.textContent = "Note: A 2% fee will be deducted.";
        }

        document.getElementById("fee_amount").textContent = fee.toFixed(2);
    }

    function validateForm() {
        var amount = document.getElementById("amount").value;
        var maxAmount = document.getElementById("max_amount").value;
        var accountNumber = document.getElementById("account_number").value;
        var randomString = document.getElementById("random_string").value;
        var amountError = document.getElementById("amount_error");
        var accountNumberError = document.getElementById("account_number_error");
        var randomStringError = document.getElementById("random_string_error");

        // Reset errors
        amountError.textContent = "";
        accountNumberError.textContent = "";
        randomStringError.textContent = "";

        if (parseFloat(amount) > parseFloat(maxAmount)) {
            amountError.textContent = "Amount exceeds wallet balance.";
            amountError.style.color = "red";
            return false;
        }

        if (parseFloat(amount) < 10) {
            amountError.textContent = "Minimum withdrawal amount should be 10.";
            amountError.style.color = "red";
            return false;
        }

        if (document.getElementById("account_number_field").style.display === "block") {
            if (accountNumber.length !== 11) {
                accountNumberError.textContent = "Account number must be exactly 11 characters long.";
                accountNumberError.style.color = "red";
                return false;
            }
        }

        if (randomString.length === 0) {
            randomStringError.textContent = "Private key is required.";
            randomStringError.style.color = "red";
            return false;
        }

        return true;
    }
</script>

<style>
    th,
    td {
        color: white;
    }
</style>

<div class="container">
    <h2 class="mt-5">Withdrawal Requests Form</h2>
    <form action="process_payment.php" method="post" onsubmit="return validateForm();">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method:</label>
            <select class="form-control" id="payment_method" name="payment_method" onchange="toggleFields()" required>
                <option value="" disabled selected>Select a payment method</option>
                <!-- <option value="Easy Paisa">Easy Paisa</option>
                <option value="Jazz Cash">Jazz Cash</option> -->
                <option value="Dollar">USDT</option>
                <option value="binance">Binance Pay ID</option>
            </select>
            <span id="fee_info" style="color: red;"></span>
        </div>

        <div id="address_field" class="form-group" style="display:none;">
            <label for="address">Address:</label>
            <input type="text" class="form-control" id="address" name="address">
        </div>

        <div class="form-group">
            <label for="amount">Amount:</label>
            <input type="number" class="form-control" id="amount" name="amount" step="0.01" oninput="toggleFields()" required>
            <span id="amount_error" style="color: red;"></span>
        </div>

        <div id="account_number_field" class="form-group" style="display:none;">
            <label for="account_number">Binance Pay ID:</label>
            <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Enter Binance Pay ID">
            <span id="account_number_error" style="color: red;"></span>
        </div>

        <div class="form-group">
            <label for="random_string">Private Key:</label>
            <input placeholder="Enter Private Key" type="text" class="form-control" id="random_string" name="random_string" required>
            <span id="random_string_error" style="color: red;"></span>
        </div>

        <input type="hidden" id="max_amount" value="<?php echo $wallet_balance; ?>">

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <div class="mt-3">
        <p>Estimated Fee: <span id="fee_amount">0.00</span></p>
    </div>
    <?php
    if (isset($_GET['submitted']) && $_GET['submitted'] == 'true') {
        echo '<div class="alert alert-success mt-3" role="alert">Your form has been submitted successfully.</div>';
    }
    ?>
    <div class="alert alert-info mt-3" role="alert">
        Your withdrawal request will be reviewed on working days.
    </div>
</div>

<div class="container mt-5">
    <h2>Withdrawal Requests</h2>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Reason Rejection</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['amount']; ?></td>
                        <td><?php echo $row['payment_method']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $row['rejection_reason']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require('footer.php') ?>