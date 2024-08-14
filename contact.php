<?php
ob_start();
session_start();
require('header.php'); // Include database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $name = htmlspecialchars($_POST['name']);
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);
    $reason = htmlspecialchars($_POST['reason']);

    // Validate required fields
    if (empty($name) || empty($username) || empty($email) || empty($message) || empty($reason)) {
        echo '<div class="alert alert-danger mt-3" role="alert">Error: All fields are required.</div>';
        exit();
    }

    // Check message length
    if (strlen($message) > 500) {
        echo '<div class="alert alert-danger mt-3" role="alert">Error: Message cannot exceed 500 characters.</div>';
        exit();
    }

    // Prepare SQL statement
    if ($stmt = $conn->prepare("INSERT INTO contact_messages (name, username, email, message, reason) VALUES (?, ?, ?, ?, ?)")) {
        $stmt->bind_param("sssss", $name, $username, $email, $message, $reason);

        if ($stmt->execute()) {
            $stmt->close();
            // Redirect to prevent form resubmission and pass email through query string
            header('Location: contact.php?success=1&email=' . urlencode($email));
            exit();
        } else {
            echo '<div class="alert alert-danger mt-3" role="alert">Error: Could not send your message. Please try again later.</div>';
            error_log("Execution Error: " . $stmt->error);
        }
    } else {
        echo '<div class="alert alert-danger mt-3" role="alert">Error: Could not prepare the SQL statement.</div>';
        error_log("Preparation Error: " . $conn->error);
    }
}
?>

<div class="container">
    <h2 class="mt-5">Contact Us</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success mt-3" role="alert">
            Thank you for contacting us. We will contact you soon at <?php echo htmlspecialchars($_GET['email']); ?>.
        </div>
    <?php endif; ?>

    <form action="contact.php" method="post">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Enter Your Full Name" required>
        </div>

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Enter Your Username" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter Your Email" required>
        </div>

        <div class="form-group">
            <label for="reason">Reason:</label>
            <select class="form-control" id="reason" name="reason" required>
                <option value="" disabled selected>Select a reason</option>
                <option value="login/registration issue">Login/Registration Issue</option>
                <option value="account activation issue">Account Activation Issue</option>
                <option value="transaction issue/deposit issue">Transaction Issue/Deposit Issue</option>
                <option value="staking/daily earning & withdraw issue">Staking/Daily Earning & Withdraw Issue</option>
                <option value="lost private key">Lost Private Key</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="message">Message:</label>
            <textarea class="form-control" id="message" name="message" rows="4" maxlength="500" placeholder="Enter Your Message" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<?php require('footer.php'); ?>