<?php
require('top.php');

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);
    $random_string = test_input($_POST["random_string"]);

    // Prepare and bind
    $stmt = $conn->prepare("SELECT id, password, random_string FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Bind result variables
        $stmt->bind_result($id, $hashed_password, $stored_random_string);
        $stmt->fetch();

        if (password_verify($password, $hashed_password) && $random_string === $stored_random_string) {
            // Password and random string are correct, start a session and redirect
            $_SESSION['user_id'] = $id;
            $_SESSION['random_string'] = $stored_random_string;
            header("Location: show_key.php");
            exit();
        } else {
            // Password or random string is incorrect
            $error_message = "Invalid email, password, or random key.";
        }
    } else {
        // Email not found
        $error_message = "Invalid email, password, or random key.";
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
</head>

<body>
    <div class="container">
        <div class="centered-form">
            <div class="form-container">
                <div class="text-center mb-4">
                    <img src="img/logo.png" alt="Logo" class="img-fluid" width="300">
                </div>
                <?php
                if (!empty($error_message)) {
                    echo '<p class="error-message">' . $error_message . '</p>';
                }
                ?>
                <form action="login_submit.php" method="post">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                    </div>
                    <div class="form-group">
                        <label for="random_string">Random Key *</label>
                        <input type="text" class="form-control" id="random_string" name="random_string" placeholder="Enter random key" required>
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
</body>

</html>