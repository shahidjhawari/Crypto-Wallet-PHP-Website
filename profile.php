<?php
require('header.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user-specific data
$stmt = $conn->prepare("SELECT name, username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_username'])) {
        $new_username = trim($_POST['new_username']);
        if (preg_match('/^[a-zA-Z0-9_]+$/', $new_username)) {
            // Check if the username already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->bind_param("si", $new_username, $user_id);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "The username is already taken. Please choose another one.";
            } else {
                $stmt->close();
                $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                $stmt->bind_param("si", $new_username, $user_id);
                if ($stmt->execute()) {
                    $success = "Username updated successfully.";
                } else {
                    $error = "Error updating username.";
                }
            }
            $stmt->close();
        } else {
            $error = "Username can only contain letters, numbers, and underscores, and must not contain spaces.";
        }
    } elseif (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (password_verify($current_password, $user_data['password'])) {
            if ($new_password === $confirm_password) {
                $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_password_hash, $user_id);
                if ($stmt->execute()) {
                    $success .= ($success ? " " : "") . "Password updated successfully.";
                } else {
                    $error .= ($error ? " " : "") . "Error updating password.";
                }
                $stmt->close();
            } else {
                $error .= ($error ? " " : "") . "New passwords do not match.";
            }
        } else {
            $error .= ($error ? " " : "") . "Current password is incorrect.";
        }
    }
}
?>

<div class="container mt-4">
    <h2>User Profile</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        </div>
    </div>

    <hr>

    <h3>Update Username</h3>
    <form method="post" action="" onsubmit="return validateUsername()">
        <div class="form-group">
            <label for="new_username">New Username:</label>
            <input type="text" minlength="8" maxlength="18" class="form-control" id="new_username" name="new_username" placeholder="Enter new username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <button type="submit" name="update_username" class="btn btn-primary">Update Username</button>
    </form>

    <hr>

    <h3>Change Password</h3>
    <form method="post" action="">
        <div class="form-group">
            <label for="current_password">Current Password:</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" class="form-control" id="new_password" name="new_password">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
        </div>
        <button type="submit" name="update_password" class="btn btn-primary">Change Password</button>
    </form>
</div>

<script>
    function validateUsername() {
        var username = document.getElementById("new_username").value;
        var usernamePattern = /^[a-zA-Z0-9_]+$/;
        if (!usernamePattern.test(username)) {
            alert("Username can only contain letters, numbers, and underscores, and must not contain spaces.");
            return false;
        }
        return true;
    }
</script>

<?php require('footer.php'); ?>