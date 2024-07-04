<?php require('header.php') ?>
    <script>
        function toggleFields() {
            var paymentMethod = document.getElementById("payment_method").value;
            var addressField = document.getElementById("address_field");
            var accountNumberField = document.getElementById("account_number_field");

            if (paymentMethod === 'Dollar') {
                addressField.style.display = "block";
                addressField.querySelector("input").required = true;
                accountNumberField.style.display = "none";
                accountNumberField.querySelector("input").required = false;
            } else {
                addressField.style.display = "none";
                addressField.querySelector("input").required = false;
                accountNumberField.style.display = "block";
                accountNumberField.querySelector("input").required = true;
            }
        }

        function validateForm() {
            var amount = parseFloat(document.getElementById("amount").value);
            var maxAmount = parseFloat(document.getElementById("max_amount").value);

            if (amount > maxAmount) {
                alert("Amount cannot be more than the available balance.");
                return false;
            }

            return true;
        }
    </script>

<div class="container">
    <h2 class="mt-5">Withdraw Form</h2>
    <form action="process_payment.php" method="post" onsubmit="return validateForm();">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" placeholder="Your Name" class="form-control" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method:</label>
            <select class="form-control" id="payment_method" name="payment_method" onchange="toggleFields()" required>
                <option value="" disabled selected>Select a payment method</option>
                <option value="Easy Paisa">Easy Paisa</option>
                <option value="Jazz Cash">Jazz Cash</option>
                <option value="Simple Pay">Simple Pay</option>
                <option value="Dollar">USDT</option>
            </select>
        </div>

        <div id="address_field" class="form-group" style="display:none;">
            <label for="address">Address:</label>
            <input type="text" class="form-control" id="address" name="address">
        </div>

        <div id="account_number_field" class="form-group" style="display:none;">
            <label for="account_number">Account Number:</label>
            <input type="text" class="form-control" id="account_number" name="account_number">
        </div>

        <div class="form-group">
            <label for="amount">Amount:</label>
            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
        </div>

        <input type="hidden" id="max_amount" value="<?php echo $wallet_balance; ?>">

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<?php require('footer.php') ?>