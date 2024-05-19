<?php
require('top.php');

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$email_error = "";
$password_error = "";
$random_string_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);
    $random_string = test_input($_POST["random_string"]);

    $stmt = $conn->prepare("SELECT id, password, random_string FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $stored_random_string);
        $stmt->fetch();

        if (!password_verify($password, $hashed_password)) {
            $password_error = "Invalid password.";
        }

        if ($random_string !== $stored_random_string) {
            $random_string_error = "Invalid random key.";
        }

        if (empty($password_error) && empty($random_string_error)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['random_string'] = $stored_random_string;
            header("Location: show_key.php");
            exit();
        }
    } else {
        $email_error = "Invalid email.";
    }
    $stmt->close();
}
?>

<style>
    body {
        background: #070F2B;
        color: white;
    }

    .centered-form {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .form-container {
        width: 100%;
        max-width: 400px;
        padding: 20px;
        border: 1px solid #e3e3e3;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        background: #141E46;
    }

    .error-message {
        color: red;
        margin-top: 10px;
    }
</style>

<div class="container">
    <div class="centered-form">
        <div class="form-container">
            <div class="text-center mb-4">
                <img src="img/logo.png" alt="Logo" class="img-fluid" width="300">
            </div>
            <?php
            if (!empty($email_error)) {
                echo '<p class="error-message">' . $email_error . '</p>';
            }
            if (!empty($password_error)) {
                echo '<p class="error-message">' . $password_error . '</p>';
            }
            if (!empty($random_string_error)) {
                echo '<p class="error-message">' . $random_string_error . '</p>';
            }
            ?>
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" autocomplete="new-email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" autocomplete="new-password" required>
                </div>
                <div class="form-group">
                    <label for="random_string">Private Key *</label>
                    <input type="text" class="form-control" id="random_string" name="random_string" placeholder="Enter private key" autocomplete="new-password" required>
                </div>
                <div class="form-group text-right">
                    <a href="#" class="text-decoration-none">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <div class="text-center mt-3">
                <p>Don't have an account? <a href="signup.php" class="text-decoration-none">Sign up</a></p>
            </div>
        </div>
    </div>
</div>