<?php require('header.php') ?>
    <title>User Payment Form</title>
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
</head>
<body>

<form action="process_payment.php" method="post" onsubmit="return validateForm();">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required><br><br>

    <label for="payment_method">Payment Method:</label>
    <select id="payment_method" name="payment_method" onchange="toggleFields()" required>
        <option value="" disabled selected>Select a payment method</option>
        <option value="Easy Paisa">Easy Paisa</option>
        <option value="Jazz Cash">Jazz Cash</option>
        <option value="Simple Pay">Simple Pay</option>
        <option value="Dollar">Dollar</option>
    </select><br><br>

    <div id="address_field" style="display:none;">
        <label for="address">Address:</label>
        <input type="text" id="address" name="address"><br><br>
    </div>

    <div id="account_number_field" style="display:none;">
        <label for="account_number">Account Number:</label>
        <input type="text" id="account_number" name="account_number"><br><br>
    </div>

    <label for="amount">Amount:</label>
    <input type="number" id="amount" name="amount" step="0.01" required><br><br>

    <input type="hidden" id="max_amount" value="<?php echo $wallet_balance; ?>"><!-- The max amount from user's wallet balance -->

    <input type="submit" value="Submit">
</form>

<?php require('footer.php') ?>