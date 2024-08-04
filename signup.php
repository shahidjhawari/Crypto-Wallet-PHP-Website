<?php
ob_start();
require('header.php');

function test_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

function generateReferralCode($length = 8)
{
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

$emailError = "";
$passwordError = "";
$referralError = "";
$usernameError = "";

// Check for referral code in URL
$referral_code = isset($_GET['referral']) ? test_input($_GET['referral']) : "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = test_input($_POST["name"]);
    $username = test_input($_POST["username"]);
    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);
    $confirmPassword = test_input($_POST["confirmPassword"]);
    $referral = test_input($_POST["referral"]);

    // Validate username
    if (!preg_match("/^[a-zA-Z0-9]+$/", $username)) {
        $usernameError = "Username can only contain lowercase letters and numbers.";
    } else {
        // Check if the username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $usernameError = "Username already exists.";
            $stmt->close();
        } else {
            $stmt->close();
        }
    }

    if ($password != $confirmPassword) {
        $passwordError = "Passwords do not match.";
    } else if (empty($usernameError)) {
        // Check if the email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $emailError = "Email already exists.";
            $stmt->close();
        } else {
            $stmt->close();
            $referrer_id = null;

            if (!empty($referral)) {
                $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
                $stmt->bind_param("s", $referral);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($referrer_id);
                    $stmt->fetch();
                    $stmt->close();
                } else {
                    $referralError = "Invalid referral code.";
                    $stmt->close();
                }
            }

            if (empty($referralError)) {
                $randomString = bin2hex(random_bytes(50));
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $referral_code = generateReferralCode();

                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, random_string, referrer_id, referral_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssis", $name, $username, $email, $hashed_password, $randomString, $referrer_id, $referral_code);
                if ($stmt->execute()) {
                    $user_id = $stmt->insert_id;
                    $stmt->close();

                    // Initialize rewards for the new user
                    $stmt = $conn->prepare("INSERT INTO rewards (user_id, reward_points, referral_count, level_one_count, level_two_count, level_three_count) VALUES (?, 0, 0, 0, 0, 0)");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->close();

                    header("Location: show_key.php?random_string=" . urlencode($randomString));
                    exit();
                } else {
                    echo "Error: " . $stmt->error;
                }
            }
        }
    }
}

function rewardReferrer($referrer_id, $points, $level)
{
    global $conn;
    if ($level > 3) {
        return;
    }

    // Check deposit amounts for levels
    if ($level == 2 || $level == 3) {
        $required_amount = ($level == 2) ? 30 : 50;
        $stmt = $conn->prepare("SELECT SUM(amount) AS total_deposited FROM deposits WHERE user_id = ?");
        $stmt->bind_param("i", $referrer_id);
        $stmt->execute();
        $total_deposited = $stmt->get_result()->fetch_assoc()['total_deposited'] ?? 0;
        $stmt->close();

        if ($total_deposited < $required_amount) {
            return; // Do not add rewards if the required deposit amount is not met
        }
    }

    // Update rewards and level count for the current referrer
    if ($level == 1) {
        $stmt = $conn->prepare("UPDATE rewards SET reward_points = reward_points + ?, referral_count = referral_count + 1, level_one_count = level_one_count + 1 WHERE user_id = ?");
    } elseif ($level == 2) {
        $stmt = $conn->prepare("UPDATE rewards SET reward_points = reward_points + ?, level_two_count = level_two_count + 1 WHERE user_id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE rewards SET reward_points = reward_points + ?, level_three_count = level_three_count + 1 WHERE user_id = ?");
    }

    // Adjust the points here
    $adjusted_points = ($points == 10) ? 5 : $points;

    $stmt->bind_param("ii", $adjusted_points, $referrer_id);
    $stmt->execute();
    $stmt->close();

    if ($level < 3) {
        // Get the next level referrer
        $stmt = $conn->prepare("SELECT referrer_id FROM users WHERE id = ?");
        $stmt->bind_param("i", $referrer_id);
        $stmt->execute();
        $stmt->bind_result($next_referrer_id);
        $stmt->fetch();
        $stmt->close();

        if ($next_referrer_id !== null) {
            // Determine points for the next level
            $next_points = ($level == 1) ? 5 : 2;
            rewardReferrer($next_referrer_id, $next_points, $level + 1);
        }
    }
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

    .error {
        color: red;
        margin-top: 5px;
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
            <form action="signup.php" method="post" autocomplete="off">
                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required autocomplete="new-name">
                </div>
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required autocomplete="new-username" minlength="8" maxlength="18">
                    <span class="error"><?php echo $usernameError; ?></span>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required autocomplete="new-email">
                    <span class="error"><?php echo $emailError; ?></span>
                </div>
                <div class="form-group password-container">
                    <label for="password">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required autocomplete="new-password">
                    <i class="fas fa-eye toggle-password" data-target="password"></i>
                    <span class="error"><?php echo $passwordError; ?></span>
                </div>
                <div class="form-group password-container">
                    <label for="confirmPassword">Confirm Password *</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required autocomplete="new-password">
                    <i class="fas fa-eye toggle-password" data-target="confirmPassword"></i>
                </div>
                <div class="form-group">
                    <label for="referral">Referral Code (optional)</label>
                    <input type="text" class="form-control" id="referral" name="referral" placeholder="Enter referral code" value="<?php echo $referral_code; ?>">
                    <span class="error"><?php echo $referralError; ?></span>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
            </form>
            <div class="text-center mt-3">
                <p>Already have an account? <a href="index.php" class="text-decoration-none">Login</a></p>
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