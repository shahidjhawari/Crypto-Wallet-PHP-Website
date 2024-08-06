<?php
ob_start();
require('header.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);

    $stmt = $conn->prepare("SELECT id, password, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    $emailError = "";
    $passwordError = "";

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $name);
        $stmt->fetch();

        if (!password_verify($password, $hashed_password)) {
            $passwordError = "Invalid password.";
        }

        if (empty($passwordError)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['login_time'] = time(); // Store the login time
            header("Location: dashboard.php");
            exit();
        }
    } else {
        $emailError = "Invalid email.";
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

    .password-container {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        top: 75%;
        right: 10px;
        transform: translateY(-50%);
        cursor: pointer;
        color: black;
    }
</style>


<div class="container">
    <div class="centered-form">
        <div class="form-container">
            <div class="text-center mb-4">
                <img src="img/logo.png" alt="Logo" class="img-fluid" width="300">
            </div>
            <?php
            if (!empty($emailError)) {
                echo '<p class="error-message">' . $emailError . '</p>';
            }
            if (!empty($passwordError)) {
                echo '<p class="error-message">' . $passwordError . '</p>';
            }
            ?>
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                </div>
                <div class="form-group password-container">
                    <label for="password">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                    <i class="fas fa-eye toggle-password" data-target="password"></i>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <div class="text-center mt-3">
                <p>Don't have an account? <a href="signup.php" class="text-decoration-none">Sign up</a></p>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-password').forEach(item => {
        item.addEventListener('click', function() {
            const target = document.getElementById(this.getAttribute('data-target'));
            if (target.getAttribute('type') === 'password') {
                target.setAttribute('type', 'text');
                this.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                target.setAttribute('type', 'password');
                this.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
</script>

<?php require('footer.php'); ?>