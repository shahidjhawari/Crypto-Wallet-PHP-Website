<?php
session_start();
require('header.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the user's withdrawal requests
$stmt = $conn->prepare("SELECT id, amount, payment_method, status, created_at FROM user_payments WHERE user_id = ? ORDER BY created_at DESC");
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

        if (paymentMethod === "Dollar") {
            addressField.style.display = "block";
            accountNumberField.style.display = "none";
        } else {
            addressField.style.display = "none";
            accountNumberField.style.display = "block";
        }
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
            amountError.textContent = "Minimum draw balance should be 10.";
            amountError.style.color = "red";
            return false;
        }

        if (accountNumberField.style.display === "block") {
            if (accountNumber.length !== 11) {
                accountNumberError.textContent = "Account number must be exactly 11 characters long.";
                accountNumberError.style.color = "red";
                return false;
            }
        }

        if (randomString.length === 0) {
            randomStringError.textContent = "Random string is required.";
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
    <h2 class="mt-5">Payment Form</h2>
    <form action="process_payment.php" method="post" onsubmit="return validateForm();">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method:</label>
            <select class="form-control" id="payment_method" name="payment_method" onchange="toggleFields()" required>
                <option value="" disabled selected>Select a payment method</option>
                <option value="Easy Paisa">Easy Paisa</option>
                <option value="Jazz Cash">Jazz Cash</option>
                <option value="Simple Pay">Simple Pay</option>
                <option value="Dollar">Dollar</option>
            </select>
        </div>

        <div id="address_field" class="form-group" style="display:none;">
            <label for="address">Address:</label>
            <input type="text" class="form-control" id="address" name="address">
        </div>

        <div class="form-group">
            <label for="amount">Amount:</label>
            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
            <span id="amount_error" style="color: red;"></span>
        </div>

        <div id="account_number_field" class="form-group" style="display:none;">
            <label for="account_number">Account Number:</label>
            <input type="text" class="form-control" id="account_number" name="account_number">
            <span id="account_number_error" style="color: red;"></span>
        </div>

        <div class="form-group">
            <label for="random_string">Private Key:</label>
            <input type="text" placeholder="Enter Your Private Key:" class="form-control" id="random_string" name="random_string" required>
            <span id="random_string_error" style="color: red;"></span>
        </div>

        <input type="hidden" id="max_amount" value="<?php echo $wallet_balance; ?>">

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
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
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require('footer.php') ?>
