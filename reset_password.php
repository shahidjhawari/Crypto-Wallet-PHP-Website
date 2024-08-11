<?php
ob_start();
require('header.php');

function test_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

$token = isset($_GET['token']) ? test_input($_GET['token']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = test_input($_POST["new_password"]);
    $confirm_password = test_input($_POST["confirm_password"]);

    if ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Validate the reset token
        $stmt = $conn->prepare("SELECT username FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($username);
            $stmt->fetch();
            $stmt->close();

            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the user's password and clear the reset token
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE username = ?");
            $stmt->bind_param("ss", $hashed_password, $username);
            $stmt->execute();
            $stmt->close();

            $success_message = "Your password has been reset successfully.";
        } else {
            $error_message = "Invalid or expired reset token.";
        }
    }
}
?>

<div class="container mt-5">
    <h2 class="text-center">Reset Password</h2>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <form action="reset_password.php?token=<?php echo urlencode($token); ?>" method="POST">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
        </div>
    </div>
</div>