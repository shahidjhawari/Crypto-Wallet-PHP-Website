<?php
ob_start();
require('header.php');

function test_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = test_input($_POST["username"]);
    $random_string = test_input($_POST["random_string"]);

    // Fetch the user's details from the database
    $stmt = $conn->prepare("SELECT email, random_string FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($email, $stored_random_string);
        $stmt->fetch();
        $stmt->close();

        // Check if the random string matches
        if ($random_string === $stored_random_string) {
            // Generate a reset token and store it
            $reset_token = bin2hex(random_bytes(32));
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = NOW() + INTERVAL 1 HOUR WHERE username = ?");
            $stmt->bind_param("ss", $reset_token, $username);
            $stmt->execute();
            $stmt->close();

            // Redirect to the reset password page
            header("Location: reset_password.php?token=" . urlencode($reset_token));
            exit();
        } else {
            $error_message = "Invalid random string.";
        }
    } else {
        $error_message = "Username not found.";
    }
}
?>

<div class="container mt-5">
    <h2 class="text-center">Forgot Password</h2>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form action="forgot_password.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="random_string">Private Key:</label>
                    <input type="text" class="form-control" id="random_string" name="random_string" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Verify</button>
            </form>
        </div>
    </div>
</div>