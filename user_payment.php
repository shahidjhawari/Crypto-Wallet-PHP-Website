<?php require('header.php') ?>
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
        var amountError = document.getElementById("amount_error");
        var accountNumberError = document.getElementById("account_number_error");

        // Reset errors
        amountError.textContent = "";
        accountNumberError.textContent = "";

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

        return true;
    }
</script>

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

        <input type="hidden" id="max_amount" value="<?php echo $wallet_balance; ?>">

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<?php require('footer.php') ?>
